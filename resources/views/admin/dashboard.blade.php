@extends('layouts.app')

@section('title', 'Dashboard Admin - Rekap Jurnal Siswa')

@section('content')
@php($isRegisteredView = request('view') === 'registered')
<div class="bg-gradient-to-r from-indigo-700 via-violet-700 to-purple-700 rounded-3xl p-6 sm:p-8 text-white shadow-xl">
    <div class="flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
        <div>
            <div class="text-xs font-bold uppercase tracking-widest text-violet-200 mb-2"><i class="fa-solid fa-shield-halved mr-1"></i> Dashboard Admin</div>
            <h1 class="text-2xl sm:text-3xl font-extrabold">{{ $isRegisteredView ? 'Siswa Terdaftar' : 'Rekap PAN Siswa' }}</h1>
            <p class="text-sm text-violet-100 mt-1">{{ $isRegisteredView ? 'Data seluruh siswa yang telah mendaftar ke sistem.' : 'Ringkasan keterisian 7 Kebiasaan Baik seluruh siswa.' }}</p>
        </div>
        @if(!$isRegisteredView)
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
            <form method="GET" class="flex items-center gap-2 bg-white/10 p-2 rounded-xl border border-white/20">
                <label for="kelas" class="text-xs font-bold">Kelas</label>
                <select id="kelas" name="kelas" onchange="this.form.submit()" class="rounded-lg px-3 py-2 text-xs font-bold text-slate-700 bg-white">
                    <option value="">Semua kelas</option>
                    @foreach($classList as $kelas)
                        <option value="{{ $kelas }}" @selected(request('kelas') === $kelas)>{{ $kelas }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        @endif
    </div>
</div>

@if($isRegisteredView)
<section id="daftar-siswa-terdaftar" class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
        <div>
            <h2 class="font-extrabold text-slate-800"><i class="fa-solid fa-user-check text-violet-500 mr-1"></i>Siswa Terdaftar</h2>
            <p class="text-xs text-slate-500 mt-1">
                @if(request('kelas'))
                    Siswa yang terdaftar di kelas {{ request('kelas') }}.
                @else
                    Semua siswa yang telah mendaftar ke sistem.
                @endif
            </p>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="w-full sm:w-auto">
                <input type="hidden" name="view" value="registered">
                <select name="kelas" onchange="this.form.submit()" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-violet-500">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($classList as $kelas)
                        <option value="{{ $kelas }}" @selected(request('kelas') === $kelas)>Kelas {{ $kelas }}</option>
                    @endforeach
                </select>
            </form>
            <span class="w-fit px-3 py-1.5 rounded-full bg-violet-100 text-violet-700 text-xs font-extrabold whitespace-nowrap">{{ $students->count() }} siswa</span>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[680px] text-left text-xs text-slate-700">
            <thead class="bg-slate-50 text-slate-400 font-extrabold uppercase tracking-wider text-[10px]">
                <tr>
                    <th class="py-3.5 px-4">No.</th>
                    <th class="py-3.5 px-4">NIS</th>
                    <th class="py-3.5 px-4">Nama Siswa</th>
                    <th class="py-3.5 px-4">Kelas</th>
                    <th class="py-3.5 px-4">Email</th>
                    <th class="py-3.5 px-4">Terdaftar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($students as $index => $student)
                    <tr class="hover:bg-violet-50/40 transition-colors">
                        <td class="py-3.5 px-4 font-bold text-slate-400">{{ $index + 1 }}</td>
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-800">{{ $student->nis }}</td>
                        <td class="py-3.5 px-4 font-bold text-slate-900">{{ $student->name }}</td>
                        <td class="py-3.5 px-4"><span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 font-bold">{{ $student->kelas }}</span></td>
                        <td class="py-3.5 px-4 text-slate-500">{{ $student->email ?: '—' }}</td>
                        <td class="py-3.5 px-4 text-slate-500">{{ optional($student->created_at)->translatedFormat('d M Y, H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400">Belum ada siswa yang terdaftar pada kelas ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@else
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    @foreach ([
        ['title' => 'Rekap Minggu Ini', 'period' => $weekStart->translatedFormat('d M') . ' – ' . $weekEnd->translatedFormat('d M Y'), 'data' => $weeklyRecap, 'tone' => 'emerald'],
        ['title' => 'Rekap Bulan Ini', 'period' => $monthStart->translatedFormat('F Y'), 'data' => $monthlyRecap, 'tone' => 'indigo'],
    ] as $recap)
    <section class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-start justify-between gap-3">
            <div><h2 class="font-extrabold text-slate-800">{{ $recap['title'] }}</h2><p class="text-xs text-slate-500 mt-1">{{ $recap['period'] }}</p></div>
            <span class="px-3 py-1 rounded-full {{ $recap['tone'] === 'emerald' ? 'bg-emerald-100 text-emerald-700' : 'bg-indigo-100 text-indigo-700' }} text-xs font-extrabold">{{ $recap['data']['percentage'] }}% capaian</span>
        </div>
        <div class="grid grid-cols-3 divide-x divide-slate-100 p-4 text-center">
            <div><div class="text-lg font-black text-slate-800">{{ $recap['data']['student_count'] }}</div><div class="text-[10px] text-slate-500 font-bold uppercase">Siswa</div></div>
            <div><div class="text-lg font-black text-emerald-600">{{ $recap['data']['full_days'] }}</div><div class="text-[10px] text-slate-500 font-bold uppercase">Hari 7/7</div></div>
            <div><div class="text-lg font-black text-violet-600">{{ $recap['data']['completed_habits'] }}</div><div class="text-[10px] text-slate-500 font-bold uppercase">Kebiasaan</div></div>
        </div>
        <div class="overflow-x-auto border-t border-slate-100">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-400 uppercase tracking-wider text-[10px]"><tr><th class="p-3">Siswa</th><th class="p-3 text-center">Isi</th><th class="p-3 text-center">7/7</th><th class="p-3 text-right">Capaian</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recap['data']['rows'] as $row)
                    <tr><td class="p-3"><div class="font-bold text-slate-800">{{ $row['student']->name }}</div><div class="text-[10px] text-slate-500">{{ $row['student']->nis }} · {{ $row['student']->kelas }}</div></td><td class="p-3 text-center font-semibold">{{ $row['entries'] }} hari</td><td class="p-3 text-center font-bold text-emerald-600">{{ $row['full_days'] }}</td><td class="p-3 text-right"><span class="font-extrabold {{ $recap['tone'] === 'emerald' ? 'text-emerald-700' : 'text-indigo-700' }}">{{ $row['percentage'] }}%</span><div class="w-16 h-1.5 ml-auto bg-slate-100 rounded-full mt-1 overflow-hidden"><div class="h-full {{ $recap['tone'] === 'emerald' ? 'bg-emerald-500' : 'bg-indigo-500' }}" style="width: {{ $row['percentage'] }}%"></div></div></td></tr>
                    @empty
                    <tr><td colspan="4" class="p-6 text-center text-slate-400">Tidak ada data siswa untuk kelas ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    @endforeach
</div>
@endif
@endsection

@push('scripts')
<script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
<script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
<script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
<script type="text/babel">
    const explorerRoot = document.getElementById('admin-student-explorer');
    const studentDataElement = document.getElementById('admin-student-data');

    if (explorerRoot && studentDataElement) {
        const students = JSON.parse(studentDataElement.textContent);

        function StudentExplorer() {
            const [query, setQuery] = React.useState('');
            const [kelas, setKelas] = React.useState('');
            const [period, setPeriod] = React.useState('week');
            const classes = [...new Set(students.map(student => student.kelas))].sort();
            const filteredStudents = students.filter(student => {
                const keyword = query.toLowerCase();
                return (!kelas || student.kelas === kelas) &&
                    (!keyword || student.name.toLowerCase().includes(keyword) || student.nis.toLowerCase().includes(keyword));
            });

            return (
                <div className="p-5">
                    <div className="flex flex-col lg:flex-row gap-3 lg:items-center lg:justify-between mb-4">
                        <div className="flex bg-slate-100 rounded-xl p-1 w-fit">
                            <button onClick={() => setPeriod('week')} className={`px-3 py-2 rounded-lg text-xs font-bold transition ${period === 'week' ? 'bg-white text-violet-700 shadow-sm' : 'text-slate-500'}`}>Minggu ini</button>
                            <button onClick={() => setPeriod('month')} className={`px-3 py-2 rounded-lg text-xs font-bold transition ${period === 'month' ? 'bg-white text-violet-700 shadow-sm' : 'text-slate-500'}`}>Bulan ini</button>
                        </div>
                        <div className="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
                            <input value={query} onChange={event => setQuery(event.target.value)} placeholder="Cari nama atau NIS..." className="px-3 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-violet-400" />
                            <select value={kelas} onChange={event => setKelas(event.target.value)} className="px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 focus:outline-none focus:ring-2 focus:ring-violet-400">
                                <option value="">Semua kelas</option>
                                {classes.map(item => <option key={item} value={item}>{item}</option>)}
                            </select>
                        </div>
                    </div>
                    <p className="text-[11px] text-slate-500 mb-3">Menampilkan <strong className="text-slate-700">{filteredStudents.length}</strong> dari {students.length} siswa</p>
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[620px] text-left text-xs">
                            <thead className="bg-slate-50 text-[10px] uppercase tracking-wider text-slate-400">
                                <tr><th className="p-3">Siswa</th><th className="p-3 text-center">Hari Diisi</th><th className="p-3 text-center">Lengkap 7/7</th><th className="p-3 text-center">Kebiasaan</th><th className="p-3 text-right">Capaian</th></tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {filteredStudents.map(student => {
                                    const recap = student[period];
                                    return <tr key={student.id} className="hover:bg-violet-50/40 transition-colors">
                                        <td className="p-3"><div className="font-bold text-slate-800">{student.name}</div><div className="text-[10px] text-slate-500">{student.nis} · {student.kelas}</div></td>
                                        <td className="p-3 text-center font-semibold text-slate-700">{recap.entries}</td>
                                        <td className="p-3 text-center font-bold text-emerald-600">{recap.full_days}</td>
                                        <td className="p-3 text-center font-semibold text-slate-700">{recap.completed_habits}</td>
                                        <td className="p-3 text-right"><span className="font-extrabold text-violet-700">{recap.percentage}%</span><div className="w-20 h-1.5 ml-auto mt-1 rounded-full bg-slate-100 overflow-hidden"><div className="h-full bg-violet-500" style={ { width: recap.percentage + '%' } }></div></div></td>
                                    </tr>;
                                })}
                                {!filteredStudents.length && <tr><td colSpan="5" className="p-8 text-center text-slate-400">Data siswa tidak ditemukan.</td></tr>}
                            </tbody>
                        </table>
                    </div>
                </div>
            );
        }

        ReactDOM.createRoot(explorerRoot).render(<StudentExplorer />);
    }
</script>
@endpush
