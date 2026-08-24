<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AdminStudentController extends Controller
{
    public const KELAS_LIST = [
        'X PPLG', 'X TJKT', 'X AKL', 'X ACP',
        'XI PPLG', 'XI TJKT', 'XI AKL', 'XI ACP',
        'XII PPLG', 'XII TJKT', 'XII AKL', 'XII ACP',
    ];

    public function bulkStore(Request $request)
    {
        $kelas = $request->input('kelas');
        $rows = is_array($request->input('rows')) ? $request->input('rows') : [];

        return response()->json($this->storeStudents($kelas, $rows));
    }

    public function importPreview(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file'],
        ]);

        try {
            $rows = $this->parseFile($request->file('file'));
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'File tidak dapat dibaca. Pastikan formatnya .xlsx atau .csv sesuai template.',
            ], 422);
        }

        if (empty($rows)) {
            return response()->json([
                'error' => 'File kosong atau tidak memiliki baris data.',
            ], 422);
        }

        $seen = [];
        $preview = array_map(function ($row, $index) use (&$seen) {
            $row = $this->normalizeRow($row);
            $errors = $this->validateRow($row, $seen);
            $row['index'] = $index;
            $row['errors'] = $errors;
            $row['valid'] = empty($errors);

            return $row;
        }, $rows, array_keys($rows));

        return response()->json(['rows' => $preview]);
    }

    public function importStore(Request $request)
    {
        $kelas = $request->input('kelas');
        $rows = is_array($request->input('rows')) ? $request->input('rows') : [];

        return response()->json($this->storeStudents($kelas, $rows));
    }

    public function update(Request $request, User $student)
    {
        abort_unless($student->role === 'siswa', 404);

        $request->merge([
            'phone' => User::normalizePhone($request->input('phone')),
        ]);

        $validated = $request->validate([
            'phone' => ['nullable', 'regex:/^\+628[0-9]{8,12}$/'],
            'otp_channel' => ['required', Rule::in(['whatsapp', 'sms'])],
        ], [
            'phone.regex' => 'Nomor HP harus menggunakan nomor seluler Indonesia yang valid.',
            'otp_channel.in' => 'Kanal OTP harus WhatsApp atau SMS.',
        ]);

        $securityChanged = $student->phone !== ($validated['phone'] ?? null)
            || $student->otp_channel !== $validated['otp_channel'];

        $student->forceFill($validated)->save();

        if ($securityChanged) {
            app(OtpService::class)->revokeTrustedDevices($student);
        }

        return back()->with('success', 'Nomor HP dan kanal OTP siswa berhasil diperbarui.');
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template-siswa.csv"',
        ];

        $content = "\xEF\xBB\xBF"."Nama Siswa,NIS,Password,No HP,Kanal OTP\r\n";

        return response($content, 200, $headers);
    }

    private function storeStudents(?string $kelas, array $rows): array
    {
        $failed = [];
        $success = 0;
        $seen = [];

        foreach ($rows as $row) {
            $row = $this->normalizeRow($row);
            $errors = $this->validateRow($row, $seen, $kelas);

            if (! empty($errors)) {
                $failed[] = [
                    'nis' => $row['nis'],
                    'name' => $row['name'],
                    'reason' => implode('; ', $errors),
                ];

                continue;
            }

            User::create([
                'nis' => $row['nis'],
                'name' => $row['name'],
                'kelas' => $kelas,
                'role' => 'siswa',
                'worship_type' => 'muslim',
                'password' => $row['password'],
                'phone' => $row['phone'],
                'otp_channel' => $row['otp_channel'],
            ]);

            $seen[$row['nis']] = true;
            $success++;
        }

        return [
            'success' => $success,
            'failed' => $failed,
        ];
    }

    private function parseFile($file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'csv') {
            $reader = IOFactory::createReader('Csv');
            $reader->setInputEncoding('UTF-8');
        } elseif ($extension === 'xls') {
            $reader = IOFactory::createReader('Xls');
        } else {
            $reader = IOFactory::createReader('Xlsx');
        }

        $spreadsheet = $reader->load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = [];
        $seenHeader = false;

        foreach ($sheet->toArray() as $cells) {
            $name = trim((string) ($cells[0] ?? ''));
            $nis = trim((string) ($cells[1] ?? ''));
            $password = trim((string) ($cells[2] ?? ''));
            $phone = trim((string) ($cells[3] ?? ''));
            $otpChannel = trim((string) ($cells[4] ?? ''));

            if (! $seenHeader && strtolower($name) === 'nama siswa') {
                $seenHeader = true;

                continue;
            }
            $seenHeader = true;

            if ($name === '' && $nis === '' && $password === '') {
                continue;
            }

            $rows[] = [
                'name' => $name,
                'nis' => $nis,
                'password' => $password,
                'phone' => $phone,
                'otp_channel' => $otpChannel,
            ];
        }

        return $rows;
    }

    private function normalizeRow(mixed $row): array
    {
        return [
            'name' => trim((string) ($row['name'] ?? '')),
            'nis' => trim((string) ($row['nis'] ?? '')),
            'password' => (string) ($row['password'] ?? ''),
            'phone' => User::normalizePhone($row['phone'] ?? null),
            'otp_channel' => strtolower(trim((string) ($row['otp_channel'] ?? ''))) ?: 'whatsapp',
        ];
    }

    private function validateRow(array $row, array &$seen, ?string $kelas = null): array
    {
        $errors = [];

        if ($row['name'] === '') {
            $errors[] = 'Nama siswa kosong';
        } elseif (mb_strlen($row['name']) > 255) {
            $errors[] = 'Nama terlalu panjang (maks 255 karakter)';
        }

        if ($row['nis'] === '') {
            $errors[] = 'NIS kosong';
        } elseif (mb_strlen($row['nis']) > 50) {
            $errors[] = 'NIS terlalu panjang (maks 50 karakter)';
        } elseif (isset($seen[$row['nis']])) {
            $errors[] = 'NIS duplikat dalam batch ini';
        } elseif (User::where('nis', $row['nis'])->exists()) {
            $errors[] = 'NIS sudah terdaftar di sistem';
        }

        if ($row['password'] === '') {
            $errors[] = 'Password kosong';
        } elseif (strlen($row['password']) < 6) {
            $errors[] = 'Password minimal 6 karakter';
        }

        if (! empty($row['phone']) && ! preg_match('/^\+628[0-9]{8,12}$/', $row['phone'])) {
            $errors[] = 'Nomor HP tidak valid (gunakan contoh 081234567890)';
        }

        if (! in_array($row['otp_channel'], ['whatsapp', 'sms'], true)) {
            $errors[] = 'Kanal OTP harus whatsapp atau sms';
        }

        if ($kelas !== null && ! in_array($kelas, self::KELAS_LIST, true)) {
            $errors[] = 'Kelas tidak valid';
        }

        return $errors;
    }
}
