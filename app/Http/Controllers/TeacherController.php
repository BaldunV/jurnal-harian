<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Journal;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TeacherController extends Controller
{
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
            if (!$todayJournal) {
                $emptyToday++;
            } elseif ($todayJournal->is_fully_completed) {
                $completedToday++;
            } elseif ($todayJournal->completed_count > 0) {
                $partialToday++;
            } else {
                $emptyToday++;
            }
        }

        return view('teacher.index', compact(
            'students',
            'classList',
            'today',
            'totalStudents',
            'completedToday',
            'partialToday',
            'emptyToday'
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
