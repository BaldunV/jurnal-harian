@extends('layouts.app')

@section('title', 'Riwayat & Kalender Jurnal')

@section('content')

<!-- Header Card -->
<div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h2 class="text-xl font-extrabold text-slate-900 flex items-center gap-2">
            <i class="fa-solid fa-calendar-days text-emerald-500"></i>
            <span>Riwayat & Kalender Bulanan</span>
        </h2>
        <p class="text-xs text-slate-500 mt-1">Pantau tingkat kepatuhan harian Anda melalui indikator warna kalender.</p>
    </div>

    <!-- Color Legend -->
    <div class="flex items-center gap-3 text-xs font-semibold bg-slate-50 p-3 rounded-xl border border-slate-200/60">
        <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
            <span class="text-slate-700">Lengkap (7/7)</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span>
            <span class="text-slate-700">Sebagian (1-6)</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-slate-300 inline-block"></span>
            <span class="text-slate-700">Kosong (0)</span>
        </div>
    </div>
</div>

<!-- Calendar Control & Navigation -->
@php
    $currentCarbon = \Carbon\Carbon::createFromDate($year, $month, 1);
    $prevMonth = $currentCarbon->copy()->subMonth();
    $nextMonth = $currentCarbon->copy()->addMonth();
@endphp

<div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-extrabold text-slate-800">
            {{ $currentCarbon->translatedFormat('F Y') }}
        </h3>

        <div class="flex items-center gap-2">
            <a href="{{ route('history', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}" class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 hover:bg-emerald-50 hover:text-emerald-600 flex items-center justify-center text-sm font-bold transition-all">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
            <a href="{{ route('history', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}" class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 hover:bg-emerald-50 hover:text-emerald-600 flex items-center justify-center text-sm font-bold transition-all">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
        </div>
    </div>

    <!-- Calendar Grid Headers -->
    <div class="grid grid-cols-7 gap-2 mb-2 text-center text-xs font-extrabold text-slate-400 uppercase tracking-wider">
        <div>Min</div>
        <div>Sen</div>
        <div>Sel</div>
        <div>Rab</div>
        <div>Kam</div>
        <div>Jum</div>
        <div>Sab</div>
    </div>

    <!-- Calendar Days Grid -->
    <div class="grid grid-cols-7 gap-2 text-center">
        @php
            $startDayOfWeek = $startDate->dayOfWeek; // 0 (Sunday) to 6 (Saturday)
            $daysInMonth = $startDate->daysInMonth;
        @endphp

        <!-- Empty slots before the first day -->
        @for($i = 0; $i < $startDayOfWeek; $i++)
            <div class="h-16 sm:h-20 rounded-2xl bg-slate-50/50 border border-slate-100"></div>
        @endfor

        <!-- Days of the month -->
        @for($day = 1; $day <= $daysInMonth; $day++)
            @php
                $dateString = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $journal = $journals->get($dateString);
                $isToday = ($dateString === \Carbon\Carbon::today()->toDateString());

                $bgClass = 'bg-slate-100 text-slate-400 border-slate-200';
                $badgeClass = 'bg-slate-200 text-slate-500';
                $statusText = 'Belum Isi';

                if ($journal) {
                    if ($journal->is_fully_completed) {
                        $bgClass = 'bg-emerald-50 text-emerald-900 border-emerald-300 shadow-sm hover:shadow-md';
                        $badgeClass = 'bg-emerald-500 text-white font-extrabold';
                        $statusText = '7/7 Lengkap';
                    } elseif ($journal->completed_count > 0) {
                        $bgClass = 'bg-amber-50 text-amber-900 border-amber-300 shadow-sm hover:shadow-md';
                        $badgeClass = 'bg-amber-400 text-amber-950 font-bold';
                        $statusText = $journal->completed_count . '/7 Terisi';
                    }
                }
            @endphp

            <button type="button" onclick="showDateDetail('{{ $dateString }}')" class="h-16 sm:h-20 rounded-2xl border p-2 flex flex-col justify-between items-center transition-all cursor-pointer hover:scale-105 active:scale-95 relative {{ $bgClass }} {{ $isToday ? 'ring-2 ring-emerald-500 ring-offset-2' : '' }}">
                @if($isToday)
                    <span class="absolute -top-2 left-1/2 -translate-x-1/2 text-[9px] font-extrabold bg-emerald-600 text-white px-2 py-0.5 rounded-full uppercase shadow-xs">Hari Ini</span>
                @endif

                <span class="text-xs sm:text-sm font-extrabold mt-1">{{ $day }}</span>

                <span class="text-[9px] sm:text-[10px] px-1.5 py-0.5 rounded-full w-full truncate text-center {{ $badgeClass }}">
                    {{ $statusText }}
                </span>
            </button>
        @endfor
    </div>
</div>

<!-- Modal Detail Jurnal Tanggal -->
<div id="detail-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl p-6 max-w-lg w-full shadow-2xl relative animate-fade-in">
        <button onclick="toggleModal('detail-modal')" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center text-sm">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="mb-4 border-b border-slate-100 pb-3">
            <h3 class="text-lg font-extrabold text-slate-900" id="modal-date-title">Detail Jurnal</h3>
            <p class="text-xs text-slate-500" id="modal-date-subtitle">Rincian pelaksanaan 7 kebiasaan baik</p>
        </div>

        <div id="modal-content-body" class="space-y-3 max-h-96 overflow-y-auto pr-1">
            <!-- Dynamic content loaded via JavaScript -->
        </div>

        <div class="mt-6 border-t border-slate-100 pt-4 flex justify-end">
            <button onclick="toggleModal('detail-modal')" class="px-6 py-2.5 bg-slate-800 text-white rounded-xl font-bold text-xs">
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

    function showDateDetail(date) {
        const modalBody = document.getElementById('modal-content-body');
        const titleElem = document.getElementById('modal-date-title');
        
        modalBody.innerHTML = '<div class="text-center py-8 text-slate-400 text-sm"><i class="fa-solid fa-spinner fa-spin text-2xl mb-2"></i><p>Memuat data jurnal...</p></div>';
        toggleModal('detail-modal');

        fetch("{{ url('/api/journal') }}/" + date)
        .then(res => res.json())
        .then(data => {
            if (!data.found) {
                titleElem.innerText = "Jurnal Tanggal " + date;
                modalBody.innerHTML = `
                    <div class="text-center py-6">
                        <div class="w-12 h-12 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-2 text-xl">
                            <i class="fa-solid fa-calendar-xmark"></i>
                        </div>
                        <p class="font-bold text-slate-700 text-sm">Belum Ada Catatan</p>
                        <p class="text-xs text-slate-400 mt-1">Siswa tidak mengisi jurnal pada tanggal ini.</p>
                    </div>
                `;
                return;
            }

            titleElem.innerText = "Jurnal " + data.formatted_date;
            const j = data.journal;

            const habitList = [
                { name: 'Bangun Pagi', status: j.bangun_pagi, icon: '🌅', note: null },
                { name: 'Beribadah', status: j.beribadah, icon: '🤲', note: formatPrayerDetails(j.ibadah_details) },
                { name: 'Berolahraga', status: j.berolahraga, icon: '🏃‍♂️', note: j.olahraga_note },
                { name: 'Makan Sehat', status: j.makan_sehat, icon: '🥗', note: j.makan_note },
                { name: 'Gemar Belajar', status: j.gemar_belajar, icon: '📚', note: j.belajar_note },
                { name: 'Bermasyarakat', status: j.bermasyarakat, icon: '🤝', note: j.masyarakat_note },
                { name: 'Tidur Cepat', status: j.tidur_cepat, icon: '🌙', note: j.tidur_note },
            ];

            let html = '';
            habitList.forEach((h, index) => {
                html += `
                    <div class="p-3.5 rounded-xl border flex items-start gap-3 ${h.status ? 'bg-emerald-50/70 border-emerald-200' : 'bg-slate-50 border-slate-200/60'}">
                        <div class="text-xl">${h.icon}</div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <h4 class="font-bold text-xs text-slate-800">${index+1}. ${h.name}</h4>
                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-md ${h.status ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-500'}">
                                    ${h.status ? 'Selesai ✅' : 'Belum ❌'}
                                </span>
                            </div>
                            ${h.note ? `<p class="text-[11px] text-slate-600 mt-1 italic bg-white/60 p-2 rounded-lg border border-slate-100">"${h.note}"</p>` : ''}
                        </div>
                    </div>
                `;
            });

            modalBody.innerHTML = html;
        })
        .catch(err => {
            modalBody.innerHTML = '<p class="text-xs text-rose-500">Gagal mengambil data jurnal.</p>';
        });
    }

    function formatPrayerDetails(details) {
        if (!details) return null;
        if (details.subuh !== undefined) {
            const list = [];
            if (details.subuh) list.push('Subuh');
            if (details.dzuhur) list.push('Dzuhur');
            if (details.ashar) list.push('Ashar');
            if (details.maghrib) list.push('Maghrib');
            if (details.isya) list.push('Isya');
            return 'Sholat terisi: ' + (list.length > 0 ? list.join(', ') : 'Belum ada');
        }
        return null;
    }
</script>
@endpush
