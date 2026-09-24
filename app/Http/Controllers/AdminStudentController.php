<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

    public function storeTeacher(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'string', 'max:50', 'unique:users,nis'],
            'password' => ['required', 'string', 'min:6'],
            'kelas' => ['required', 'string', 'in:'.implode(',', self::KELAS_LIST)],
        ]);

        User::create([
            'nis' => $validated['nis'],
            'name' => $validated['name'],
            'kelas' => $validated['kelas'],
            'role' => 'guru',
            'worship_type' => 'muslim',
            'password' => $validated['password'],
        ]);

        return back()->with('success', "Akun guru {$validated['name']} ({$validated['kelas']}) berhasil dibuat.");
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

    public function updateReligion(Request $request, User $user)
    {
        if ($user->role !== 'siswa') {
            abort(404);
        }

        $validated = $request->validate([
            'religion' => ['required', Rule::in(array_keys(User::RELIGIONS))],
        ]);

        $religion = $validated['religion'];
        $user->update([
            'religion' => $religion,
            'worship_type' => User::worshipTypeForReligion($religion),
        ]);

        return back()->with('success', "Agama {$user->name} berhasil diubah menjadi {$user->religion_label}.");
    }

    public function downloadTemplate(Request $request)
    {
        if ($request->query('format') === 'xlsx') {
            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Template Siswa');
            $sheet->fromArray([
                ['Nama Siswa', 'NIS', 'Password', 'Agama'],
            ]);

            $writer = new Xlsx($spreadsheet);

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, 'template-siswa.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template-siswa.csv"',
        ];

        $content = "\xEF\xBB\xBF"."Nama Siswa,NIS,Password,Agama\r\n";

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

            $religion = $row['religion'];

            User::create([
                'nis' => $row['nis'],
                'name' => $row['name'],
                'kelas' => $kelas,
                'role' => 'siswa',
                'religion' => $religion,
                'worship_type' => User::worshipTypeForReligion($religion),
                'password' => $row['password'],
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
            $religion = trim((string) ($cells[3] ?? ''));

            if (! $seenHeader && strtolower($name) === 'nama siswa') {
                $seenHeader = true;

                continue;
            }
            $seenHeader = true;

            if ($name === '' && $nis === '' && $password === '' && $religion === '') {
                continue;
            }

            $rows[] = [
                'name' => $name,
                'nis' => $nis,
                'password' => $password,
                'religion' => $religion,
            ];
        }

        return $rows;
    }

    private function normalizeRow(mixed $row): array
    {
        $rawReligion = strtolower(trim((string) ($row['religion'] ?? '')));
        $legacyWorshipType = strtolower(trim((string) ($row['worship_type'] ?? '')));

        if ($rawReligion === '' && in_array($legacyWorshipType, ['non_muslim', 'non-muslim', 'non muslim'], true)) {
            $rawReligion = 'belum ditentukan';
        }

        $religion = match ($rawReligion) {
            '', 'islam', 'muslim' => 'islam',
            'kristen', 'kristen protestan', 'protestan' => 'kristen',
            'katolik' => 'katolik',
            'hindu' => 'hindu',
            'buddha', 'budha' => 'buddha',
            'konghucu', 'khonghucu' => 'konghucu',
            default => $rawReligion,
        };

        return [
            'name' => trim((string) ($row['name'] ?? '')),
            'nis' => trim((string) ($row['nis'] ?? '')),
            'password' => (string) ($row['password'] ?? ''),
            'religion' => $religion,
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

        if (! array_key_exists($row['religion'], User::RELIGIONS)) {
            $errors[] = 'Agama tidak valid';
        }

        if ($kelas !== null && ! in_array($kelas, self::KELAS_LIST, true)) {
            $errors[] = 'Kelas tidak valid';
        }

        return $errors;
    }
}
