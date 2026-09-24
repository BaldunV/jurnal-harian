<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $filters = $request->validate([
            'view' => ['nullable', Rule::in(['registered'])],
            'kelas' => ['nullable', Rule::in(AdminStudentController::KELAS_LIST)],
            'worship_type' => ['nullable', Rule::in(['muslim', 'non_muslim'])],
            'religion' => ['nullable', Rule::in(array_keys(User::RELIGIONS))],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $isRegisteredView = ($filters['view'] ?? null) === 'registered';
        $classList = User::where('role', 'siswa')->orderBy('kelas')->distinct()->pluck('kelas');
        $studentsQuery = User::where('role', 'siswa')->orderBy('kelas')->orderBy('name');

        if (! empty($filters['kelas'])) {
            $studentsQuery->where('kelas', $filters['kelas']);
        }

        if (! empty($filters['worship_type'])) {
            $studentsQuery->where('worship_type', $filters['worship_type']);
        }

        if (! empty($filters['religion'])) {
            $studentsQuery->where('religion', $filters['religion']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $studentsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $students = $isRegisteredView
            ? $studentsQuery->paginate(20)->withQueryString()
            : $studentsQuery->get();
        $teachers = User::where('role', 'guru')->orderBy('kelas')->orderBy('name')->get();
        $weekStart = Carbon::today()->startOfWeek(Carbon::MONDAY);
        $weekEnd = Carbon::today()->endOfWeek(Carbon::SUNDAY);
        $monthStart = Carbon::today()->startOfMonth();
        $monthEnd = Carbon::today()->endOfMonth();
        $weeklyRecap = null;
        $monthlyRecap = null;

        if (! $isRegisteredView) {
            $studentIds = $students->pluck('id');
            $weekJournals = Journal::whereIn('user_id', $studentIds)
                ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->get()
                ->groupBy('user_id');
            $monthJournals = Journal::whereIn('user_id', $studentIds)
                ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->get()
                ->groupBy('user_id');

            $weekDays = $weekStart->diffInDays($weekEnd) + 1;
            $monthDays = $monthStart->daysInMonth;
            $weeklyRecap = $this->buildRecap($students, $weekJournals, $weekDays);
            $monthlyRecap = $this->buildRecap($students, $monthJournals, $monthDays);
        }

        return view('admin.dashboard', compact(
            'classList', 'students', 'teachers', 'weekStart', 'weekEnd', 'monthStart', 'monthEnd', 'weeklyRecap', 'monthlyRecap'
        ));
    }

    public function documentation(Request $request)
    {
        $validated = $request->validate([
            'kelas' => ['nullable', 'string', 'max:100'],
            'habit' => ['nullable', 'in:olahraga,makan,belajar,masyarakat'],
            'date' => ['nullable', 'date'],
        ]);

        $classList = User::where('role', 'siswa')
            ->whereNotNull('kelas')
            ->where('kelas', '!=', '')
            ->orderBy('kelas')
            ->distinct()
            ->pluck('kelas');

        $selectedClass = $validated['kelas'] ?? '';
        $selectedHabit = $validated['habit'] ?? '';
        $selectedDate = $validated['date'] ?? '';

        $photoColumns = [
            'olahraga' => 'olahraga_photo',
            'makan' => 'makan_photo',
            'belajar' => 'belajar_photo',
            'masyarakat' => 'masyarakat_photo',
        ];

        $journals = Journal::query()
            ->with('user')
            ->whereHas('user', function ($query) use ($selectedClass) {
                $query->where('role', 'siswa');

                if ($selectedClass !== '') {
                    $query->where('kelas', $selectedClass);
                }
            })
            ->when($selectedDate !== '', function ($query) use ($selectedDate) {
                $query->whereDate('date', $selectedDate);
            })
            ->when(
                $selectedHabit !== '',
                function ($query) use ($selectedHabit, $photoColumns) {
                    $query->whereNotNull($photoColumns[$selectedHabit]);
                },
                function ($query) {
                    $query->where(function ($photoQuery) {
                        $photoQuery
                            ->whereNotNull('olahraga_photo')
                            ->orWhereNotNull('makan_photo')
                            ->orWhereNotNull('belajar_photo')
                            ->orWhereNotNull('masyarakat_photo');
                    });
                }
            )
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(18)
            ->withQueryString();

        return view('admin.documentation', compact(
            'classList',
            'journals',
            'selectedClass',
            'selectedHabit',
            'selectedDate',
        ));
    }

    private function buildRecap($students, $journalsByStudent, int $periodDays): array
    {
        $rows = $students->map(function (User $student) use ($journalsByStudent, $periodDays) {
            $journals = $journalsByStudent->get($student->id, collect());
            $completedHabits = $journals->sum('completed_count');
            $maximumHabits = $periodDays * 7;

            return [
                'student' => $student,
                'entries' => $journals->count(),
                'full_days' => $journals->where('is_fully_completed', true)->count(),
                'completed_habits' => $completedHabits,
                'percentage' => $maximumHabits ? round(($completedHabits / $maximumHabits) * 100) : 0,
            ];
        });

        $completedHabits = $rows->sum('completed_habits');
        $maximumHabits = $students->count() * $periodDays * 7;

        return [
            'rows' => $rows,
            'student_count' => $students->count(),
            'entry_count' => $rows->sum('entries'),
            'full_days' => $rows->sum('full_days'),
            'completed_habits' => $completedHabits,
            'percentage' => $maximumHabits ? round(($completedHabits / $maximumHabits) * 100) : 0,
        ];
    }

    public function documentationPhoto(Journal $journal, string $type)
    {
        $columns = [
            'olahraga' => 'olahraga_photo',
            'makan' => 'makan_photo',
            'belajar' => 'belajar_photo',
            'masyarakat' => 'masyarakat_photo',
        ];

        abort_unless(isset($columns[$type]), 404);

        $column = $columns[$type];
        $path = $journal->{$column};

        abort_if(empty($path), 404);

        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->response(
                $path,
                null,
                [
                    'Cache-Control' => 'private, max-age=3600',
                ]
            );
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->response(
                $path,
                null,
                [
                    'Cache-Control' => 'private, max-age=3600',
                ]
            );
        }

        abort(404);
    }
}
