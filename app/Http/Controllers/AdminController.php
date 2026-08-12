<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $classList = User::where('role', 'siswa')->orderBy('kelas')->distinct()->pluck('kelas');
        $studentsQuery = User::where('role', 'siswa')->orderBy('kelas')->orderBy('name');

        if ($request->filled('kelas')) {
            $studentsQuery->where('kelas', $request->input('kelas'));
        }

        $students = $studentsQuery->get();
        $studentIds = $students->pluck('id');
        $weekStart = Carbon::today()->startOfWeek(Carbon::MONDAY);
        $weekEnd = Carbon::today()->endOfWeek(Carbon::SUNDAY);
        $monthStart = Carbon::today()->startOfMonth();
        $monthEnd = Carbon::today()->endOfMonth();

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
        $monthlyRows = $monthlyRecap['rows']->keyBy(fn ($row) => $row['student']->id);
        $studentData = $weeklyRecap['rows']->map(function ($weekRow) use ($monthlyRows) {
            $student = $weekRow['student'];
            $monthRow = $monthlyRows->get($student->id);

            return [
                'id' => $student->id,
                'nis' => $student->nis,
                'name' => $student->name,
                'kelas' => $student->kelas,
                'week' => collect($weekRow)->except('student')->all(),
                'month' => collect($monthRow)->except('student')->all(),
            ];
        })->values();

        return view('admin.dashboard', compact(
            'classList', 'students', 'weekStart', 'weekEnd', 'monthStart', 'monthEnd', 'weeklyRecap', 'monthlyRecap', 'studentData'
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
}
