<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class JournalController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();
        $todayStr = $today->toDateString();

        $journal = Journal::where('user_id', $user->id)
            ->whereDate('date', $todayStr)
            ->first();

        if (! $journal) {
            $journal = Journal::create([
                'user_id' => $user->id,
                'date' => $todayStr,
                'bangun_pagi' => false,
                'beribadah' => false,
                'ibadah_details' => $user->worship_type === 'muslim'
                    ? ['subuh' => false, 'dzuhur' => false, 'ashar' => false, 'maghrib' => false, 'isya' => false]
                    : ['prayer' => false, 'scripture' => false, 'worship' => false, 'spiritual_activity' => false, 'other' => false],
                'berolahraga' => false,
                'makan_sehat' => false,
                'gemar_belajar' => false,
                'bermasyarakat' => false,
                'tidur_cepat' => false,
                'completed_count' => 0,
                'is_fully_completed' => false,
            ]);
        }

        $streak = $user->current_streak;
        $badges = $user->badges;
        $recentJournals = Journal::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->take(7)
            ->get();

        $leaderboard = User::where('role', 'siswa')
            ->where('kelas', $user->kelas)
            ->withSum('journals as total_habits', 'completed_count')
            ->orderByDesc('total_habits')
            ->orderBy('name')
            ->get();

        return view('dashboard', compact('user', 'journal', 'today', 'streak', 'badges', 'recentJournals', 'leaderboard'));
    }

    public function save(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'bangun_pagi_time' => ['nullable', 'date_format:H:i'],
        ]);
        $targetDateStr = Carbon::parse($request->input('date', Carbon::today()->toDateString()))->toDateString();

        $journal = Journal::where('user_id', $user->id)
            ->whereDate('date', $targetDateStr)
            ->first();

        // Jurnal yang sudah di-submit (klik tombol Simpan) tidak dapat diubah kembali selamanya
        if ($journal && $journal->is_submitted) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'is_locked' => true,
                    'message' => 'Jurnal telah disimpan permanen dan tidak dapat diubah kembali selamanya.',
                ], 422);
            }

            return redirect()->back()->withErrors(['message' => 'Jurnal telah disimpan permanen dan tidak dapat diubah kembali selamanya.']);
        }

        if (! $journal) {
            $journal = new Journal;
            $journal->user_id = $user->id;
            $journal->date = $targetDateStr;
        }

        $journal->bangun_pagi = $request->boolean('bangun_pagi');
        $journal->bangun_pagi_time = $journal->bangun_pagi ? $request->input('bangun_pagi_time') : null;
        $journal->berolahraga = $request->boolean('berolahraga');
        $journal->olahraga_note = $request->input('olahraga_note');
        $journal->makan_sehat = $request->boolean('makan_sehat');
        $journal->makan_note = $request->input('makan_note');
        $journal->gemar_belajar = $request->boolean('gemar_belajar');
        $journal->belajar_note = $request->input('belajar_note');
        $journal->bermasyarakat = $request->boolean('bermasyarakat');
        $journal->masyarakat_note = $request->input('masyarakat_note');
        $journal->tidur_cepat = $request->boolean('tidur_cepat');
        $journal->tidur_note = $request->input('tidur_note');

        // Processing Ibadah
        if ($user->worship_type === 'muslim') {
            $subuh = $request->boolean('ibadah_subuh');
            $dzuhur = $request->boolean('ibadah_dzuhur');
            $ashar = $request->boolean('ibadah_ashar');
            $maghrib = $request->boolean('ibadah_maghrib');
            $isya = $request->boolean('ibadah_isya');

            $journal->ibadah_details = [
                'subuh' => $subuh,
                'dzuhur' => $dzuhur,
                'ashar' => $ashar,
                'maghrib' => $maghrib,
                'isya' => $isya,
            ];

            // Master beribadah is true ONLY if all 5 prayers are checked
            $journal->beribadah = ($subuh && $dzuhur && $ashar && $maghrib && $isya);
        } else {
            $details = [
                'prayer' => $request->boolean('ibadah_prayer'),
                'scripture' => $request->boolean('ibadah_scripture'),
                'worship' => $request->boolean('ibadah_worship'),
                'spiritual_activity' => $request->boolean('ibadah_spiritual_activity'),
                'other' => $request->boolean('ibadah_other'),
            ];

            $journal->ibadah_details = $details;
            $journal->beribadah = collect($details)->filter()->count() > 0;
            $journal->ibadah_note = $request->input('ibadah_note');
        }

        $journal->recalculateProgress();

        // Jika ini submit manual (bukan AJAX auto-save), kunci permanen
        $isManualSubmit = ! ($request->wantsJson() || $request->ajax());
        if ($isManualSubmit) {
            $journal->is_submitted = true;
        }

        $journal->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Jurnal berhasil diperbarui!',
                'completed_count' => $journal->completed_count,
                'is_fully_completed' => $journal->is_fully_completed,
                'is_submitted' => $journal->is_submitted,
                'beribadah' => $journal->beribadah,
                'streak' => $user->current_streak,
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Jurnal harian berhasil disimpan dan dikunci permanen!');
    }

    public function history(Request $request)
    {
        $user = Auth::user();
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $journals = Journal::where('user_id', $user->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

        return view('history', compact('user', 'startDate', 'endDate', 'journals', 'month', 'year'));
    }

    public function getByDate($date)
    {
        $user = Auth::user();
        $journal = Journal::where('user_id', $user->id)
            ->whereDate('date', $date)
            ->first();

        if (! $journal) {
            return response()->json([
                'found' => false,
                'date' => $date,
            ]);
        }

        $qualified = $journal->qualifiedHabits();
        $qualifiedCount = count(array_filter($qualified));

        return response()->json([
            'found' => true,
            'journal' => $journal,
            'qualified_habits' => $qualified,
            'qualified_count' => $qualifiedCount,
            'is_fully_qualified' => $qualifiedCount === 7,
            'formatted_date' => Carbon::parse($date)->translatedFormat('l, d F Y'),
        ]);
    }

    public function statistics(Request $request)
    {
        $user = Auth::user();
        $days = $request->input('period', 30);

        $startDate = Carbon::today()->subDays($days - 1);
        $journals = Journal::where('user_id', $user->id)
            ->whereBetween('date', [$startDate->toDateString(), Carbon::today()->toDateString()])
            ->get();

        $totalDaysRecorded = $journals->count();
        if ($totalDaysRecorded === 0) {
            $totalDaysRecorded = 1; // avoid division by zero
        }

        $stats = [
            'bangun_pagi' => ['name' => 'Bangun Pagi', 'icon' => 'sunrise', 'count' => $journals->where('bangun_pagi', true)->count()],
            'beribadah' => ['name' => 'Beribadah', 'icon' => 'hand-heart', 'count' => $journals->where('beribadah', true)->count()],
            'berolahraga' => ['name' => 'Berolahraga', 'icon' => 'footprints', 'count' => $journals->where('berolahraga', true)->count()],
            'makan_sehat' => ['name' => 'Makan Sehat', 'icon' => 'salad', 'count' => $journals->where('makan_sehat', true)->count()],
            'gemar_belajar' => ['name' => 'Gemar Belajar', 'icon' => 'book-open', 'count' => $journals->where('gemar_belajar', true)->count()],
            'bermasyarakat' => ['name' => 'Bermasyarakat', 'icon' => 'handshake', 'count' => $journals->where('bermasyarakat', true)->count()],
            'tidur_cepat' => ['name' => 'Tidur Cepat', 'icon' => 'moon-star', 'count' => $journals->where('tidur_cepat', true)->count()],
        ];

        foreach ($stats as $key => &$item) {
            $item['percentage'] = round(($item['count'] / $totalDaysRecorded) * 100);
        }
        unset($item);

        $shortNames = [
            'bangun_pagi' => 'Pagi',
            'beribadah' => 'Ibadah',
            'berolahraga' => 'Olahraga',
            'makan_sehat' => 'Sehat',
            'gemar_belajar' => 'Belajar',
            'bermasyarakat' => 'Sosial',
            'tidur_cepat' => 'Tidur',
        ];

        foreach ($stats as $key => &$item) {
            $item['shortName'] = $shortNames[$key] ?? $item['name'];
        }
        unset($item);

        $journalsByDate = $journals->keyBy(fn ($journal) => Carbon::parse($journal->date)->toDateString());
        $trendData = collect(range(0, $days - 1))->map(function ($offset) use ($startDate, $journalsByDate) {
            $date = $startDate->copy()->addDays($offset);
            $journal = $journalsByDate->get($date->toDateString());

            return [
                'date' => $date->translatedFormat('d M'),
                'completed' => $journal ? (int) $journal->completed_count : 0,
            ];
        })->values();

        // Find most skipped habit
        $sortedStats = collect($stats)->sortBy('count');
        $mostSkipped = $sortedStats->first();

        return view('statistics', compact('user', 'stats', 'days', 'totalDaysRecorded', 'mostSkipped', 'trendData'));
    }

    public function profile()
    {
        $user = Auth::user();

        return view('profile', compact('user'));
    }

    public function mascot()
    {
        $user = Auth::user();

        return view('mascot', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        abort(403, 'Agama dan jenis ibadah hanya dapat diubah oleh admin.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min' => 'Password baru minimal 8 karakter.',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Password saat ini salah.']);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);
        $user->tokens()->delete();

        return redirect()->back()->with('success', 'Password berhasil diubah!');
    }
}
