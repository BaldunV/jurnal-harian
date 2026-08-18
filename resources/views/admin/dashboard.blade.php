@extends('layouts.app', ['hidePageLoader' => true])

@section('title', 'Dashboard Admin - Rekap Jurnal Siswa')

@section('content')
@php($isRegisteredView = request('view') === 'registered')
<div class="bg-gradient-to-r from-primary-700 via-primary-600 to-primary-500 rounded-3xl p-6 sm:p-8 text-white shadow-card">
    <div class="flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
        <div>
            <div class="text-xs font-bold uppercase tracking-widest text-emerald-100 mb-2">@include('partials.icon', ['name' => 'shield-check', 'class' => 'w-3.5 h-3.5 inline-block mr-1']) Dashboard Admin</div>
            <h1 class="text-2xl sm:text-3xl font-extrabold">{{ $isRegisteredView ? 'Siswa Terdaftar' : 'Rekap PAN Siswa' }}</h1>
            <p class="text-sm text-emerald-100 mt-1">{{ $isRegisteredView ? 'Data seluruh siswa yang telah mendaftar ke sistem.' : 'Ringkasan keterisian jurnal seluruh siswa.' }}</p>
        </div>
        @if(!$isRegisteredView)
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
            <form method="GET" class="flex items-center gap-2 bg-white/10 p-2 rounded-xl border border-white/20">
                <label for="kelas" class="text-xs font-bold">Kelas</label>
                <select id="kelas" name="kelas" onchange="this.form.submit()" class="rounded-lg px-3 py-2 text-xs font-bold text-slate-700 bg-white dark:bg-slate-800 dark:text-slate-100">
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
<section id="manajemen-siswa" class="bg-white dark:bg-slate-800/80 dark:border-slate-700 rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden mb-6">
    <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="font-extrabold text-slate-800 dark:text-slate-100">@include('partials.icon', ['name' => 'user-plus', 'class' => 'w-4 h-4 inline-block mr-1 text-primary-600'])Manajemen Siswa</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Tambah banyak siswa sekaligus lewat tabel input atau file Excel/CSV.</p>
        </div>
        <div class="flex flex-col sm:flex-row flex-wrap gap-2">
            <select id="bulk-kelas" class="px-4 py-2.5 bg-slate-50 dark:bg-slate-700/60 dark:border-slate-600 dark:text-slate-100 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">-- Pilih Kelas --</option>
                @foreach(\App\Http\Controllers\AdminStudentController::KELAS_LIST as $kelasOption)
                    <option value="{{ $kelasOption }}">Kelas {{ $kelasOption }}</option>
                @endforeach
            </select>
            <a href="{{ route('admin.students.template') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-200 text-xs font-extrabold hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                @include('partials.icon', ['name' => 'printer', 'class' => 'w-4 h-4']) Download Template
            </a>
            <button id="btn-toggle-bulk" type="button" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-primary-600 text-white text-xs font-extrabold shadow-md shadow-primary-600/25 hover:bg-primary-700 transition-colors">
                @include('partials.icon', ['name' => 'list-checks', 'class' => 'w-4 h-4']) Tambah Siswa Massal
            </button>
            <button id="btn-trigger-import" type="button" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-extrabold shadow-md shadow-emerald-600/25 hover:bg-emerald-700 transition-colors">
                @include('partials.icon', ['name' => 'cloud-fog', 'class' => 'w-4 h-4']) Import Siswa
            </button>
            <input id="import-file" type="file" accept=".xlsx,.csv" class="hidden">
        </div>
    </div>

    {{-- Panel: Tambah Siswa Massal (tabel dinamis) --}}
    <div id="panel-bulk" class="hidden border-b border-slate-100 dark:border-slate-700">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-700/40 text-slate-400 font-extrabold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-4 w-12">No.</th>
                        <th class="py-3 px-4">Nama Siswa</th>
                        <th class="py-3 px-4">NIS</th>
                        <th class="py-3 px-4">Password</th>
                        <th class="py-3 px-4 w-16 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="bulk-rows" class="divide-y divide-slate-100 dark:divide-slate-700"></tbody>
            </table>
        </div>
        <div class="p-4 flex flex-col sm:flex-row gap-2 sm:items-center sm:justify-between bg-slate-50/60 dark:bg-slate-700/20">
            <button id="btn-add-row" type="button" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 text-xs font-extrabold border border-emerald-200 dark:border-emerald-500/30 hover:bg-emerald-100 dark:hover:bg-emerald-500/25 transition-colors">
                @include('partials.icon', ['name' => 'user-plus', 'class' => 'w-3.5 h-3.5']) Tambah Baris
            </button>
            <button id="btn-save-bulk" type="button" class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-xl bg-primary-600 text-white text-xs font-extrabold shadow-md shadow-primary-600/25 hover:bg-primary-700 transition-colors">
                @include('partials.icon', ['name' => 'save', 'class' => 'w-3.5 h-3.5']) Simpan Semua Siswa
            </button>
        </div>
    </div>

    {{-- Panel: Import Siswa --}}
    <div id="panel-import" class="hidden border-b border-slate-100 dark:border-slate-700 p-5">
        <div id="import-dropzone" class="border-2 border-dashed border-slate-200 dark:border-slate-600 rounded-2xl p-8 text-center cursor-pointer hover:border-emerald-400 hover:bg-emerald-50/40 dark:hover:bg-emerald-500/5 transition-colors">
            <div class="text-slate-400 dark:text-slate-500 mx-auto mb-2">@include('partials.icon', ['name' => 'cloud-fog', 'class' => 'w-8 h-8'])</div>
            <p class="text-xs font-bold text-slate-600 dark:text-slate-300">Klik untuk pilih file Excel (.xlsx) atau CSV</p>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Format kolom: <strong>Nama Siswa | NIS | Password</strong> &mdash; unduh template terlebih dahulu jika perlu.</p>
        </div>
        <div id="import-loading" class="hidden p-8 text-center text-xs font-bold text-slate-500 dark:text-slate-400">Memproses file&hellip;</div>
        <div id="import-preview" class="hidden mt-4">
            <div class="flex flex-col sm:flex-row gap-2 sm:items-center sm:justify-between mb-3">
                <p class="text-xs font-bold text-slate-700 dark:text-slate-200">Preview Data (<span id="preview-total">0</span> baris &mdash; <span id="preview-valid" class="text-emerald-600 dark:text-emerald-400">0 valid</span>, <span id="preview-error" class="text-rose-600 dark:text-rose-400">0 error</span>)</p>
                <button id="btn-save-import" type="button" class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-xl bg-primary-600 text-white text-xs font-extrabold shadow-md shadow-primary-600/25 hover:bg-primary-700 transition-colors">
                    @include('partials.icon', ['name' => 'save', 'class' => 'w-3.5 h-3.5']) Simpan Data Valid
                </button>
            </div>
            <div class="overflow-x-auto max-h-80 overflow-y-auto rounded-2xl border border-slate-200 dark:border-slate-700">
                <table class="w-full min-w-[640px] text-left text-xs text-slate-700 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-700/40 text-slate-400 font-extrabold uppercase tracking-wider text-[10px] sticky top-0">
                        <tr>
                            <th class="py-3 px-4 w-12">No.</th>
                            <th class="py-3 px-4">Nama Siswa</th>
                            <th class="py-3 px-4">NIS</th>
                            <th class="py-3 px-4">Password</th>
                            <th class="py-3 px-4 w-40">Status</th>
                        </tr>
                    </thead>
                    <tbody id="preview-rows" class="divide-y divide-slate-100 dark:divide-slate-700"></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Panel: Hasil --}}
    <div id="result-panel" class="hidden p-5 border-b border-slate-100 dark:border-slate-700">
        <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span id="result-icon" class="w-10 h-10 rounded-2xl flex items-center justify-center"></span>
                <div>
                    <p id="result-title" class="font-extrabold text-slate-800 dark:text-slate-100 text-sm"></p>
                    <p id="result-subtitle" class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5"></p>
                </div>
            </div>
            <button id="btn-reload" type="button" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-200 text-xs font-extrabold hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                @include('partials.icon', ['name' => 'refresh-cw', 'class' => 'w-3.5 h-3.5']) Segarkan Daftar Siswa
            </button>
        </div>
        <div id="result-failed" class="hidden mt-4">
            <p class="text-xs font-extrabold text-rose-600 dark:text-rose-400 mb-2 uppercase tracking-wider">Daftar Siswa Gagal</p>
            <div class="overflow-x-auto rounded-2xl border border-rose-200 dark:border-rose-500/30">
                <table class="w-full min-w-[480px] text-left text-xs text-slate-700 dark:text-slate-300">
                    <thead class="bg-rose-50 dark:bg-rose-500/10 text-rose-500 font-extrabold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4">NIS</th>
                            <th class="py-3 px-4">Nama</th>
                            <th class="py-3 px-4">Alasan</th>
                        </tr>
                    </thead>
                    <tbody id="result-failed-rows" class="divide-y divide-rose-100 dark:divide-rose-500/15"></tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<section id="daftar-siswa-terdaftar" class="bg-white dark:bg-slate-800/80 dark:border-slate-700 rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
    <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
        <div>
            <h2 class="font-extrabold text-slate-800 dark:text-slate-100">@include('partials.icon', ['name' => 'user-check', 'class' => 'w-4 h-4 inline-block mr-1 text-primary-600'])Siswa Terdaftar</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
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
                <select name="kelas" onchange="this.form.submit()" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700/60 dark:border-slate-600 dark:text-slate-100 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($classList as $kelas)
                        <option value="{{ $kelas }}" @selected(request('kelas') === $kelas)>Kelas {{ $kelas }}</option>
                    @endforeach
                </select>
            </form>
            <span class="w-fit px-3 py-1.5 rounded-full bg-primary-50 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 text-xs font-extrabold whitespace-nowrap">{{ $students->count() }} siswa</span>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[680px] text-left text-xs text-slate-700 dark:text-slate-300">
            <thead class="bg-slate-50 dark:bg-slate-700/40 text-slate-400 font-extrabold uppercase tracking-wider text-[10px]">
                <tr>
                    <th class="py-3.5 px-4">No.</th>
                    <th class="py-3.5 px-4">NIS</th>
                    <th class="py-3.5 px-4">Nama Siswa</th>
                    <th class="py-3.5 px-4">Kelas</th>
                    <th class="py-3.5 px-4">Email</th>
                    <th class="py-3.5 px-4">Terdaftar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($students as $index => $student)
                    <tr class="hover:bg-emerald-50/40 dark:hover:bg-emerald-500/10 transition-colors">
                        <td class="py-3.5 px-4 font-bold text-slate-400 dark:text-slate-500">{{ $index + 1 }}</td>
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-800 dark:text-slate-200">{{ $student->nis }}</td>
                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">{{ $student->name }}</td>
                        <td class="py-3.5 px-4"><span class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 font-bold">{{ $student->kelas }}</span></td>
                        <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">{{ $student->email ?: '—' }}</td>
                        <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">{{ optional($student->created_at)->translatedFormat('d M Y, H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400 dark:text-slate-500">Belum ada siswa yang terdaftar pada kelas ini.</td>
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
        ['title' => 'Rekap Bulan Ini', 'period' => $monthStart->translatedFormat('F Y'), 'data' => $monthlyRecap, 'tone' => 'teal'],
    ] as $recap)
    <section class="bg-white dark:bg-slate-800/80 dark:border-slate-700 rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex items-start justify-between gap-3">
            <div><h2 class="font-extrabold text-slate-800 dark:text-slate-100">{{ $recap['title'] }}</h2><p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $recap['period'] }}</p></div>
            <span class="px-3 py-1 rounded-full {{ $recap['tone'] === 'emerald' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-teal-100 text-teal-700 dark:bg-teal-500/15 dark:text-teal-300' }} text-xs font-extrabold">{{ $recap['data']['percentage'] }}% capaian</span>
        </div>
        <div class="grid grid-cols-3 divide-x divide-slate-100 dark:divide-slate-700 p-4 text-center">
            <div><div class="text-lg font-black text-slate-800 dark:text-slate-100">{{ $recap['data']['student_count'] }}</div><div class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase">Siswa</div></div>
            <div><div class="text-lg font-black text-emerald-600 dark:text-emerald-400">{{ $recap['data']['full_days'] }}</div><div class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase">Hari 7/7</div></div>
            <div><div class="text-lg font-black text-primary-600 dark:text-primary-400">{{ $recap['data']['completed_habits'] }}</div><div class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase">Kebiasaan</div></div>
        </div>
        <div class="overflow-x-auto border-t border-slate-100 dark:border-slate-700">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-700/40 text-slate-400 uppercase tracking-wider text-[10px]"><tr><th class="p-3">Siswa</th><th class="p-3 text-center">Isi</th><th class="p-3 text-center">7/7</th><th class="p-3 text-right">Capaian</th></tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($recap['data']['rows'] as $row)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors"><td class="p-3"><div class="font-bold text-slate-800 dark:text-slate-100">{{ $row['student']->name }}</div><div class="text-[10px] text-slate-500 dark:text-slate-400">{{ $row['student']->nis }} · {{ $row['student']->kelas }}</div></td><td class="p-3 text-center font-semibold">{{ $row['entries'] }} hari</td><td class="p-3 text-center font-bold text-emerald-600 dark:text-emerald-400">{{ $row['full_days'] }}</td><td class="p-3 text-right"><span class="font-extrabold {{ $recap['tone'] === 'emerald' ? 'text-emerald-700 dark:text-emerald-300' : 'text-teal-700 dark:text-teal-300' }}">{{ $row['percentage'] }}%</span><div class="w-16 h-1.5 ml-auto bg-slate-100 dark:bg-slate-700/60 rounded-full mt-1 overflow-hidden"><div class="h-full {{ $recap['tone'] === 'emerald' ? 'bg-emerald-500' : 'bg-teal-500' }}" style="width: {{ $row['percentage'] }}%"></div></div></td></tr>
                    @empty
                    <tr><td colspan="4" class="p-6 text-center text-slate-400 dark:text-slate-500">Tidak ada data siswa untuk kelas ini.</td></tr>
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
                        <div className="flex bg-slate-100 dark:bg-slate-700/60 rounded-xl p-1 w-fit">
                            <button onClick={() => setPeriod('week')} className={`px-3 py-2 rounded-lg text-xs font-bold transition ${period === 'week' ? 'bg-white dark:bg-slate-800 text-primary-700 dark:text-primary-300 shadow-sm' : 'text-slate-500 dark:text-slate-400'}`}>Minggu ini</button>
                            <button onClick={() => setPeriod('month')} className={`px-3 py-2 rounded-lg text-xs font-bold transition ${period === 'month' ? 'bg-white dark:bg-slate-800 text-primary-700 dark:text-primary-300 shadow-sm' : 'text-slate-500 dark:text-slate-400'}`}>Bulan ini</button>
                        </div>
                        <div className="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
                            <input value={query} onChange={event => setQuery(event.target.value)} placeholder="Cari nama atau NIS..." className="px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 text-xs focus:outline-none focus:ring-2 focus:ring-primary-500" />
                            <select value={kelas} onChange={event => setKelas(event.target.value)} className="px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 text-xs font-semibold text-slate-600 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">Semua kelas</option>
                                {classes.map(item => <option key={item} value={item}>{item}</option>)}
                            </select>
                        </div>
                    </div>
                    <p className="text-[11px] text-slate-500 dark:text-slate-400 mb-3">Menampilkan <strong className="text-slate-700 dark:text-slate-200">{filteredStudents.length}</strong> dari {students.length} siswa</p>
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[620px] text-left text-xs text-slate-700 dark:text-slate-300">
                            <thead className="bg-slate-50 dark:bg-slate-700/40 text-[10px] uppercase tracking-wider text-slate-400">
                                <tr><th className="p-3">Siswa</th><th className="p-3 text-center">Hari Diisi</th><th className="p-3 text-center">Lengkap 7/7</th><th className="p-3 text-center">Kebiasaan</th><th className="p-3 text-right">Capaian</th></tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 dark:divide-slate-700">
                                {filteredStudents.map(student => {
                                    const recap = student[period];
                                    return <tr key={student.id} className="hover:bg-emerald-50/40 dark:hover:bg-emerald-500/10 transition-colors">
                                        <td className="p-3"><div className="font-bold text-slate-800 dark:text-slate-100">{student.name}</div><div className="text-[10px] text-slate-500 dark:text-slate-400">{student.nis} · {student.kelas}</div></td>
                                        <td className="p-3 text-center font-semibold text-slate-700 dark:text-slate-200">{recap.entries}</td>
                                        <td className="p-3 text-center font-bold text-emerald-600 dark:text-emerald-400">{recap.full_days}</td>
                                        <td className="p-3 text-center font-semibold text-slate-700 dark:text-slate-200">{recap.completed_habits}</td>
                                        <td className="p-3 text-right"><span className="font-extrabold text-primary-700 dark:text-primary-300">{recap.percentage}%</span><div className="w-20 h-1.5 ml-auto mt-1 rounded-full bg-slate-100 dark:bg-slate-700/60 overflow-hidden"><div className="h-full bg-primary-500" style={ { width: recap.percentage + '%' } }></div></div></td>
                                    </tr>;
                                })}
                                {!filteredStudents.length && <tr><td colSpan="5" className="p-8 text-center text-slate-400 dark:text-slate-500">Data siswa tidak ditemukan.</td></tr>}
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

@push('scripts')
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const bulkKelas = document.getElementById('bulk-kelas');
    const btnToggleBulk = document.getElementById('btn-toggle-bulk');
    const panelBulk = document.getElementById('panel-bulk');
    const bulkRows = document.getElementById('bulk-rows');
    const btnAddRow = document.getElementById('btn-add-row');
    const btnSaveBulk = document.getElementById('btn-save-bulk');

    const btnTriggerImport = document.getElementById('btn-trigger-import');
    const importFile = document.getElementById('import-file');
    const panelImport = document.getElementById('panel-import');
    const dropzone = document.getElementById('import-dropzone');
    const importLoading = document.getElementById('import-loading');
    const importPreview = document.getElementById('import-preview');
    const previewRows = document.getElementById('preview-rows');
    const btnSaveImport = document.getElementById('btn-save-import');
    const previewTotal = document.getElementById('preview-total');
    const previewValid = document.getElementById('preview-valid');
    const previewError = document.getElementById('preview-error');

    const resultPanel = document.getElementById('result-panel');
    const resultIcon = document.getElementById('result-icon');
    const resultTitle = document.getElementById('result-title');
    const resultSubtitle = document.getElementById('result-subtitle');
    const resultFailed = document.getElementById('result-failed');
    const resultFailedRows = document.getElementById('result-failed-rows');
    const btnReload = document.getElementById('btn-reload');

    if (!bulkKelas || !btnToggleBulk || !panelBulk) return;

    let previewData = [];

    function iconSvg(paths) {
        return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5" aria-hidden="true">${paths}</svg>`;
    }

    function renderBulkRows() {
        const rows = bulkRows.querySelectorAll('tr[data-row]');
        rows.forEach((tr, index) => {
            tr.querySelector('.row-no').textContent = index + 1;
        });
    }

    function addBulkRow(data = {}) {
        const tr = document.createElement('tr');
        tr.setAttribute('data-row', '');
        tr.className = 'bg-white dark:bg-slate-800/60';
        tr.innerHTML = `
            <td class="py-2.5 px-4"><span class="row-no font-bold text-slate-400 dark:text-slate-500"></span></td>
            <td class="py-2.5 px-4"><input type="text" name="name" value="${data.name || ''}" placeholder="Nama lengkap siswa" class="w-full px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-700/60 dark:text-slate-100 border border-slate-200 dark:border-slate-600 text-xs focus:outline-none focus:ring-2 focus:ring-primary-500"></td>
            <td class="py-2.5 px-4"><input type="text" name="nis" value="${data.nis || ''}" placeholder="Nomor Induk Siswa" class="w-full px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-700/60 dark:text-slate-100 border border-slate-200 dark:border-slate-600 text-xs focus:outline-none focus:ring-2 focus:ring-primary-500"></td>
            <td class="py-2.5 px-4"><input type="text" name="password" value="${data.password || ''}" placeholder="Minimal 6 karakter" class="w-full px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-700/60 dark:text-slate-100 border border-slate-200 dark:border-slate-600 text-xs focus:outline-none focus:ring-2 focus:ring-primary-500"></td>
            <td class="py-2.5 px-4 text-center">
                <button type="button" class="btn-del-row inline-flex items-center justify-center w-8 h-8 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/15 transition-colors" title="Hapus baris">${iconSvg('<path d="M18 6 6 18" /><path d="m6 6 12 12" />')}</button>
            </td>`;
        tr.querySelector('.btn-del-row').addEventListener('click', () => {
            tr.remove();
            renderBulkRows();
        });
        bulkRows.appendChild(tr);
        renderBulkRows();
    }

    function collectBulkRows() {
        return Array.from(bulkRows.querySelectorAll('tr[data-row]')).map(tr => {
            const inputs = tr.querySelectorAll('input');
            return { name: inputs[0].value.trim(), nis: inputs[1].value.trim(), password: inputs[2].value };
        });
    }

    btnToggleBulk.addEventListener('click', () => {
        panelBulk.classList.toggle('hidden');
        if (!panelBulk.classList.contains('hidden') && !bulkRows.querySelectorAll('tr[data-row]').length) {
            addBulkRow();
        }
    });

    btnAddRow.addEventListener('click', () => addBulkRow());

    btnSaveBulk.addEventListener('click', async () => {
        const kelas = bulkKelas.value;
        const rows = collectBulkRows();

        if (!kelas) {
            alert('Pilih kelas terlebih dahulu.');
            return;
        }
        if (!rows.length || rows.every(row => !row.name && !row.nis && !row.password)) {
            alert('Belum ada data siswa yang diisi.');
            return;
        }

        btnSaveBulk.disabled = true;
        btnSaveBulk.textContent = 'Menyimpan...';

        try {
            const response = await fetch('{{ route('admin.students.bulk') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ kelas, rows })
            });
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.error || 'Terjadi kesalahan saat menyimpan.');
            }
            showResult(data);
            bulkRows.innerHTML = '';
            addBulkRow();
            panelBulk.classList.add('hidden');
        } catch (error) {
            alert(error.message);
        } finally {
            btnSaveBulk.disabled = false;
            btnSaveBulk.innerHTML = `@include('partials.icon', ['name' => 'save', 'class' => 'w-3.5 h-3.5']) Simpan Semua Siswa`;
        }
    });

    btnTriggerImport.addEventListener('click', () => {
        panelImport.classList.toggle('hidden');
        if (!panelImport.classList.contains('hidden')) {
            importFile.click();
        }
    });

    dropzone.addEventListener('click', () => importFile.click());
    importFile.addEventListener('change', async () => {
        const file = importFile.files[0];
        if (!file) return;

        dropzone.classList.add('hidden');
        importLoading.classList.remove('hidden');
        importPreview.classList.add('hidden');

        const formData = new FormData();
        formData.append('file', file);

        try {
            const response = await fetch('{{ route('admin.students.import.preview') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: formData
            });
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.error || 'Gagal membaca file.');
            }
            previewData = data.rows;
            renderPreview();
        } catch (error) {
            alert(error.message);
            dropzone.classList.remove('hidden');
            importFile.value = '';
        } finally {
            importLoading.classList.add('hidden');
        }
    });

    function renderPreview() {
        previewRows.innerHTML = '';
        const total = previewData.length;
        const validCount = previewData.filter(row => row.valid).length;
        const errorCount = total - validCount;

        previewTotal.textContent = total;
        previewValid.textContent = validCount + ' valid';
        previewError.textContent = errorCount + ' error';

        previewData.forEach((row, index) => {
            const tr = document.createElement('tr');
            const chip = row.valid
                ? '<span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300 font-extrabold text-[10px]">Valid</span>'
                : '<span class="px-2.5 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300 font-extrabold text-[10px]">Error</span>';
            const reason = row.errors && row.errors.length
                ? `<div class="text-[10px] text-rose-500 dark:text-rose-400 mt-1">${row.errors.join(' • ')}</div>`
                : '';
            tr.innerHTML = `
                <td class="py-3 px-4 font-bold text-slate-400 dark:text-slate-500">${index + 1}</td>
                <td class="py-3 px-4 font-bold text-slate-800 dark:text-slate-200">${escapeHtml(row.name) || '—'}</td>
                <td class="py-3 px-4 font-mono font-bold text-slate-800 dark:text-slate-200">${escapeHtml(row.nis) || '—'}</td>
                <td class="py-3 px-4 text-slate-500 dark:text-slate-400">${row.password ? '••••••' : '—'}</td>
                <td class="py-3 px-4">${chip}${reason}</td>`;
            previewRows.appendChild(tr);
        });

        importPreview.classList.remove('hidden');
    }

    btnSaveImport.addEventListener('click', async () => {
        const kelas = bulkKelas.value;
        const validRows = previewData.filter(row => row.valid).map(row => ({
            name: row.name, nis: row.nis, password: row.password
        }));

        if (!kelas) {
            alert('Pilih kelas terlebih dahulu.');
            return;
        }
        if (!validRows.length) {
            alert('Tidak ada data valid untuk disimpan.');
            return;
        }

        btnSaveImport.disabled = true;
        btnSaveImport.textContent = 'Menyimpan...';

        try {
            const response = await fetch('{{ route('admin.students.import.store') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ kelas, rows: validRows })
            });
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.error || 'Terjadi kesalahan saat menyimpan.');
            }
            showResult(data);
            importPreview.classList.add('hidden');
            dropzone.classList.remove('hidden');
            importFile.value = '';
            previewData = [];
        } catch (error) {
            alert(error.message);
        } finally {
            btnSaveImport.disabled = false;
            btnSaveImport.innerHTML = `@include('partials.icon', ['name' => 'save', 'class' => 'w-3.5 h-3.5']) Simpan Data Valid`;
        }
    });

    function showResult(data) {
        const total = data.success + data.failed.length;
        const allOk = data.failed.length === 0;

        resultIcon.className = 'w-10 h-10 rounded-2xl flex items-center justify-center ' +
            (allOk ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400'
                   : 'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400');
        resultIcon.innerHTML = allOk
            ? iconSvg('<path d="M20 6 9 17l-5-5" />')
            : iconSvg('<path d="M16 2v3" /><path d="m17 16 5 5" /><path d="m17 21 5-5" /><path d="M21 12V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2h8" /><path d="M3 9h18" /><path d="M8 2v3" />');

        resultTitle.textContent = `Berhasil ditambahkan: ${data.success} siswa${data.failed.length ? ` • Gagal: ${data.failed.length} siswa` : ''}`;
        resultSubtitle.textContent = data.failed.length
            ? `${data.failed.length} siswa gagal disimpan. Perbaiki datanya lalu coba lagi.`
            : `${total} data berhasil diproses. Siswa dapat langsung login menggunakan NIS dan password.`;

        if (data.failed.length) {
            resultFailedRows.innerHTML = '';
            data.failed.forEach(item => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="py-2.5 px-4 font-mono font-bold text-slate-800 dark:text-slate-200">${escapeHtml(item.nis) || '—'}</td>
                    <td class="py-2.5 px-4 font-bold text-slate-700 dark:text-slate-300">${escapeHtml(item.name) || '—'}</td>
                    <td class="py-2.5 px-4 text-rose-600 dark:text-rose-400">${escapeHtml(item.reason)}</td>`;
                resultFailedRows.appendChild(tr);
            });
            resultFailed.classList.remove('hidden');
        } else {
            resultFailed.classList.add('hidden');
        }

        resultPanel.classList.remove('hidden');
        resultPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    btnReload.addEventListener('click', () => window.location.reload());

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[char]));
    }
})();
</script>
@endpush
