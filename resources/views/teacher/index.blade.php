@extends('layouts.app')

@section('title', 'Panel Wali Kelas / Guru Monitoring')

@section('content')

<!-- Header Banner -->
<div class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <div class="inline-flex items-center gap-2 px-3 py-1 bg-amber-500/20 text-amber-300 border border-amber-500/30 rounded-full text-xs font-semibold mb-2">
            <i class="fa-solid fa-chalkboard-user"></i>
            <span>Mode Pemantauan Guru / Wali Kelas</span>
        </div>
        <h1 class="text-2xl font-extrabold tracking-tight">Rekap Kedisiplinan Siswa</h1>
        <p class="text-slate-400 text-xs mt-1">Pantau keterisian jurnal 7 Kebiasaan Baik siswa secara real-time.</p>
    </div>

    <!-- Export / Print Button -->
    <button onclick="window.print()" class="bg-white/10 hover:bg-white/20 border border-white/20 text-white px-5 py-3 rounded-2xl font-bold text-xs transition-all flex items-center gap-2 self-start md:self-auto">
        <i class="fa-solid fa-print"></i>
        <span>Cetak Rekap Laporan</span>
    </button>
</div>

<!-- Overview Stats Cards -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80">
        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Siswa</div>
        <div class="text-2xl font-black text-slate-800 mt-1">{{ $totalStudents }}</div>
    </div>
    <div class="bg-emerald-50 rounded-2xl p-4 shadow-sm border border-emerald-200">
        <div class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Lengkap Hari Ini (7/7)</div>
        <div class="text-2xl font-black text-emerald-600 mt-1">🟢 {{ $completedToday }}</div>
    </div>
    <div class="bg-amber-50 rounded-2xl p-4 shadow-sm border border-amber-200">
        <div class="text-xs font-bold text-amber-700 uppercase tracking-wider">Sebagian Terisi</div>
        <div class="text-2xl font-black text-amber-600 mt-1">🟡 {{ $partialToday }}</div>
    </div>
    <div class="bg-rose-50 rounded-2xl p-4 shadow-sm border border-rose-200">
        <div class="text-xs font-bold text-rose-700 uppercase tracking-wider">Belum Mengisi</div>
        <div class="text-2xl font-black text-rose-600 mt-1">🔴 {{ $emptyToday }}</div>
    </div>
</div>

<!-- Filter Bar & Search -->
<div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80">
    <form action="{{ route('teacher.index') }}" method="GET" class="flex flex-col sm:flex-row items-center justify-between gap-3">
        
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <select name="kelas" onchange="this.form.submit()" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="">-- Semua Kelas --</option>
                @foreach($classList as $c)
                    <option value="{{ $c }}" {{ request('kelas') == $c ? 'selected' : '' }}>Kelas {{ $c }}</option>
                @endforeach
            </select>
        </div>

        <div class="relative w-full sm:w-72">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-xs">
                <i class="fa-solid fa-magnifying-glass"></i>
            </span>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama / NIS Siswa..."
                class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>

    </form>
</div>

<!-- Student List Table -->
<div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <h3 class="font-extrabold text-slate-800 text-sm">Daftar Status Siswa Hari Ini ({{ \Carbon\Carbon::now()->translatedFormat('d F Y') }})</h3>
        <span class="text-xs text-slate-400">Total {{ $students->count() }} Siswa</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-700">
            <thead class="bg-slate-50 text-slate-400 font-extrabold uppercase tracking-wider text-[10px] border-b border-slate-100">
                <tr>
                    <th class="py-3.5 px-4">No</th>
                    <th class="py-3.5 px-4">NIS</th>
                    <th class="py-3.5 px-4">Nama Siswa</th>
                    <th class="py-3.5 px-4">Kelas</th>
                    <th class="py-3.5 px-4 text-center">Status Hari Ini</th>
                    <th class="py-3.5 px-4 text-center">Streak 🔥</th>
                    <th class="py-3.5 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($students as $index => $student)
                    @php
                        $tj = $student->journals->first();
                        $statusBadge = '<span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 font-semibold">🔴 Belum Isi</span>';
                        if ($tj) {
                            if ($tj->is_fully_completed) {
                                $statusBadge = '<span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 font-extrabold">🟢 7/7 Lengkap</span>';
                            } elseif ($tj->completed_count > 0) {
                                $statusBadge = '<span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 font-bold">🟡 ' . $tj->completed_count . '/7 Terisi</span>';
                            }
                        }
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition-all">
                        <td class="py-3.5 px-4 font-bold text-slate-400">{{ $index + 1 }}</td>
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-800">{{ $student->nis }}</td>
                        <td class="py-3.5 px-4 font-bold text-slate-900">{{ $student->name }}</td>
                        <td class="py-3.5 px-4 font-semibold text-slate-500">{{ $student->kelas }}</td>
                        <td class="py-3.5 px-4 text-center">{!! $statusBadge !!}</td>
                        <td class="py-3.5 px-4 text-center font-extrabold text-amber-600">🔥 {{ $student->current_streak }} Hari</td>
                        <td class="py-3.5 px-4 text-center">
                            <button onclick="openStudentModal({{ $student->id }})" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold rounded-lg transition-all text-[11px]">
                                Detail Riwayat
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400 font-medium">Tidak ada data siswa ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Detail Siswa untuk Guru -->
<div id="student-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl p-6 max-w-lg w-full shadow-2xl relative animate-fade-in">
        <button onclick="toggleModal('student-modal')" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center text-sm">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="mb-4 border-b border-slate-100 pb-3">
            <h3 class="text-lg font-extrabold text-slate-900" id="student-modal-name">Detail Siswa</h3>
            <p class="text-xs text-slate-500" id="student-modal-info">NIS & Kelas</p>
        </div>

        <div id="student-modal-body" class="space-y-3 max-h-96 overflow-y-auto pr-1">
            <!-- Dynamic content via JS -->
        </div>

        <div class="mt-6 border-t border-slate-100 pt-4 flex justify-end">
            <button onclick="toggleModal('student-modal')" class="px-6 py-2.5 bg-slate-800 text-white rounded-xl font-bold text-xs">
                Tutup
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function toggleModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.toggle('hidden');
        }
    }

    function openStudentModal(studentId) {
        const body = document.getElementById('student-modal-body');
        const nameElem = document.getElementById('student-modal-name');
        const infoElem = document.getElementById('student-modal-info');

        body.innerHTML = '<div class="text-center py-8 text-slate-400 text-sm"><i class="fa-solid fa-spinner fa-spin text-2xl mb-2"></i><p>Memuat riwayat siswa...</p></div>';
        toggleModal('student-modal');

        fetch("{{ url('/api/teacher/student') }}/" + studentId)
        .then(res => res.json())
        .then(data => {
            nameElem.innerText = data.student.name;
            infoElem.innerText = "NIS: " + data.student.nis + " • " + data.student.kelas + " • Streak: 🔥 " + data.streak + " Hari";

            let html = `
                <div class="p-3 bg-emerald-50 rounded-xl text-xs font-bold text-emerald-800 mb-3 flex items-center justify-between">
                    <span>Lencana Terbuka:</span>
                    <span>${data.badges.filter(b => b.unlocked).map(b => b.icon + ' ' + b.name).join(', ') || 'Belum ada'}</span>
                </div>
                <h4 class="font-bold text-xs text-slate-700 uppercase tracking-wider mb-2">Catatan 30 Hari Terakhir:</h4>
            `;

            if (data.journals.length === 0) {
                html += '<p class="text-xs text-slate-400 italic">Belum ada riwayat jurnal yang diisi.</p>';
            } else {
                data.journals.forEach(j => {
                    const statusClass = j.is_fully_completed ? 'bg-emerald-100 text-emerald-700' : (j.completed_count > 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500');
                    html += `
                        <div class="p-3 rounded-xl border border-slate-200/80 text-xs flex items-center justify-between">
                            <div>
                                <span class="font-bold text-slate-800">${j.date}</span>
                                <div class="text-[11px] text-slate-500">Terisi: ${j.completed_count}/7 kebiasaan</div>
                            </div>
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold ${statusClass}">
                                ${j.is_fully_completed ? '7/7 Lengkap ✅' : j.completed_count + '/7 Terisi'}
                            </span>
                        </div>
                    `;
                });
            }

            body.innerHTML = html;
        })
        .catch(err => {
            body.innerHTML = '<p class="text-xs text-rose-500">Gagal mengambil data detail siswa.</p>';
        });
    }
</script>
@endpush
