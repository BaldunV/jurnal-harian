<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    private const PHOTO_MAP = [
        'olahraga' => ['label' => 'Berolahraga', 'icon' => 'footprints', 'photo' => 'olahraga_photo', 'note' => 'olahraga_note'],
        'makan' => ['label' => 'Makan Sehat', 'icon' => 'salad', 'photo' => 'makan_photo', 'note' => 'makan_note'],
        'belajar' => ['label' => 'Gemar Belajar', 'icon' => 'book-open', 'photo' => 'belajar_photo', 'note' => 'belajar_note'],
        'masyarakat' => ['label' => 'Bermasyarakat', 'icon' => 'handshake', 'photo' => 'masyarakat_photo', 'note' => 'masyarakat_note'],
    ];

    public function index(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $query = User::where('role', 'siswa');

        if ($request->has('kelas') && $request->kelas != '') {
            $query->where('kelas', $request->kelas);
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $students = $query->with(['journals' => function ($q) use ($today) {
            $q->whereDate('date', $today);
        }])->get();

        $classList = User::where('role', 'siswa')->distinct()->pluck('kelas');

        // Calculate statistics
        $totalStudents = $students->count();
        $completedToday = 0;
        $partialToday = 0;
        $emptyToday = 0;

        foreach ($students as $student) {
            $todayJournal = $student->journals->first();
            if (! $todayJournal) {
                $emptyToday++;
            } elseif ($todayJournal->is_fully_completed) {
                $completedToday++;
            } elseif ($todayJournal->completed_count > 0) {
                $partialToday++;
            } else {
                $emptyToday++;
            }
        }

        // Dokumentasi foto: default kelas wali guru, ikut filter kelas bila dipilih.
        $docsKelas = $request->input('kelas', $request->user()->kelas);

        $documentations = Journal::with('user')
            ->whereHas('user', function ($q) use ($docsKelas) {
                $q->where('role', 'siswa');

                if ($docsKelas && $docsKelas !== '-') {
                    $q->where('kelas', $docsKelas);
                }
            })
            ->where(function ($q) {
                $q->whereNotNull('olahraga_photo')
                    ->orWhereNotNull('makan_photo')
                    ->orWhereNotNull('belajar_photo')
                    ->orWhereNotNull('masyarakat_photo');
            })
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->take(12)
            ->get()
            ->map(function ($journal) {
                $items = [];

                foreach (self::PHOTO_MAP as $type => $meta) {
                    if (! empty($journal->{$meta['photo']})) {
                        $items[] = [
                            'label' => $meta['label'],
                            'icon' => $meta['icon'],
                            'photo' => route('admin.documentation.photo', [$journal->id, $type]),
                            'note' => $journal->{$meta['note']},
                        ];
                    }
                }

                $journal->doc_items = $items;

                return $journal;
            })
            ->filter(fn ($journal) => ! empty($journal->doc_items))
            ->values();

        return view('teacher.index', compact(
            'students',
            'classList',
            'today',
            'totalStudents',
            'completedToday',
            'partialToday',
            'emptyToday',
            'documentations',
            'docsKelas'
        ));
    }

    public function studentDetail($id)
    {
        $student = User::where('role', 'siswa')->with(['journals' => function ($q) {
            $q->orderBy('date', 'desc')->take(30);
        }])->findOrFail($id);

        return response()->json([
            'student' => $student,
            'streak' => $student->current_streak,
            'badges' => $student->badges,
            'journals' => $student->journals,
        ]);
    }
}
