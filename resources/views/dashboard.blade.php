@extends('layouts.app')

@section('title', 'Dashboard Jurnal Harian 7 Kebiasaan Baik')

@section('content')

<!-- Header Banner -->
<div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-emerald-600/15 relative overflow-hidden">
    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-semibold mb-3">
                <i class="fa-solid fa-calendar-day text-amber-300"></i>
                <span>{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Hai, {{ $user->name }}! 👋</h1>
            <p class="text-emerald-100 text-sm mt-1 max-w-xl font-medium">
                Disiplin adalah jembatan antara cita-cita dan pencapaian. Mari lengkapi 7 Kebiasaan Baik hari ini!
            </p>
        </div>

        <!-- Streak & Badge Pill -->
        <div class="flex items-center gap-3">
            <div class="bg-white/10 backdrop-blur-md border border-white/20 p-3.5 rounded-2xl flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-amber-500 text-amber-950 flex items-center justify-center text-2xl shadow-md">
                    🔥
                </div>
                <div>
                    <div class="text-[11px] uppercase font-bold tracking-wider text-emerald-200">Streak Kamu</div>
                    <div class="text-xl font-extrabold" id="streak-counter">{{ $streak }} Hari</div>
                </div>
            </div>

            <button onclick="toggleModal('badges-modal')" class="bg-white text-emerald-800 hover:bg-emerald-50 px-4 py-3.5 rounded-2xl font-bold text-xs shadow-md transition-all flex items-center gap-2">
                <i class="fa-solid fa-trophy text-amber-500 text-base"></i>
                <span>Lencana</span>
            </button>
        </div>
    </div>
    
    <!-- Background Decorative Elements -->
    <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
</div>

<!-- Progress Bar Card -->
<div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80">
    <div class="flex items-center justify-between mb-3">
        <div>
            <h3 class="font-extrabold text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-bars-progress text-emerald-500"></i>
                <span>Progress Kebiasaan Hari Ini</span>
            </h3>
            <p class="text-xs text-slate-500">Lengkapi seluruh 7 kebiasaan untuk mempertahankan streak!</p>
        </div>
        <div class="text-right">
            <span class="text-2xl font-extrabold text-emerald-600" id="progress-count">{{ $journal->completed_count }}</span>
            <span class="text-sm font-bold text-slate-400">/ 7</span>
        </div>
    </div>

    <!-- Progress Bar Element -->
    @php
        $percent = round(($journal->completed_count / 7) * 100);
    @endphp
    <div class="w-full h-4 bg-slate-100 rounded-full overflow-hidden p-0.5 border border-slate-200/60">
        <div id="progress-bar-fill" class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full transition-all duration-500 shadow-sm" style="width: {{ $percent }}%;"></div>
    </div>

    <div class="flex items-center justify-between mt-3 text-xs">
        <span class="text-slate-500 font-medium" id="progress-percentage-text">{{ $percent }}% Selesai</span>
        <button type="button" onclick="requestNotificationPermission()" class="text-emerald-600 hover:text-emerald-700 font-bold flex items-center gap-1.5">
            <i class="fa-solid fa-bell"></i>
            <span>Aktifkan Pengingat Malam</span>
        </button>
    </div>
</div>

<!-- Permanent Lock Status Banner -->
@if($journal->is_submitted)
    <div id="lock-banner" class="bg-gradient-to-r from-slate-900 to-slate-800 text-white rounded-2xl p-5 shadow-lg border border-amber-500/30 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-400 border border-amber-500/40 flex items-center justify-center text-xl shrink-0">
                🔒
            </div>
            <div>
                <h4 class="font-extrabold text-sm text-white flex items-center gap-2">
                    <span>Jurnal Hari Ini Telah Terkunci Permanen</span>
                    <span class="px-2 py-0.5 bg-emerald-500 text-white text-[10px] font-black rounded-md">SUDAH DISIMPAN</span>
                </h4>
                <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                    Anda telah mengklik <strong>"Simpan Jurnal Hari Ini"</strong>. Sesuai ketentuan, jurnal yang telah disimpan <strong>tidak dapat diubah kembali selamanya</strong> demi menjaga kejujuran dan kedisiplinan.
                </p>
            </div>
        </div>
    </div>
@endif

<!-- Form Checklist 7 Kebiasaan Baik -->
<form id="journal-form" action="{{ route('journal.save') }}" method="POST" class="space-y-4">
    @csrf
    <input type="hidden" name="date" value="{{ $today->toDateString() }}">

    <fieldset {{ $journal->is_submitted ? 'disabled' : '' }} id="journal-fieldset" class="space-y-4">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <!-- 1. BANGUN PAGI -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:border-emerald-300 transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-lg font-bold">
                            🌅
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900">1. Bangun Pagi</h4>
                            <span class="text-[11px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">Kedisiplinan</span>
                        </div>
                    </div>

                    <!-- Single Checkbox -->
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="bangun_pagi" value="1" onchange="autoSaveJournal()" class="peer sr-only" {{ $journal->bangun_pagi ? 'checked' : '' }}>
                        <div class="w-6 h-6 bg-slate-200 peer-focus:outline-none rounded-lg peer peer-checked:after:translate-x-full peer-checked:bg-emerald-500 flex items-center justify-center text-white text-xs font-bold transition-all shadow-sm">
                            <i class="fa-solid fa-check opacity-0 peer-checked:opacity-100"></i>
                        </div>
                    </label>
                </div>
                <p class="text-xs text-slate-500 mt-3 leading-relaxed">
                    Memulai hari lebih awal untuk melatih kedisiplinan dan kesiapan mental.
                </p>
            </div>
        </div>

        <!-- 2. BERIBADAH (KHUSUS SUBUH, DZUHUR, ASHAR, MAGHRIB, ISYA) -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:border-emerald-300 transition-all md:col-span-2">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg font-bold">
                        🤲
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-slate-900">2. Beribadah</h4>
                            <span id="ibadah-status-badge" class="text-[11px] font-bold px-2 py-0.5 rounded-md {{ $journal->beribadah ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $journal->beribadah ? 'Sudah Lengkap ✅' : 'Belum Lengkap ⏳' }}
                            </span>
                        </div>
                        <span class="text-xs text-slate-500">Membentuk fondasi spiritual, kejujuran, serta rasa syukur kepada Tuhan.</span>
                    </div>
                </div>

                <!-- Master Checkbox (Read-only status) -->
                <div class="flex flex-col items-end">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-sm shadow-sm transition-all {{ $journal->beribadah ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-400' }}" id="master-ibadah-icon">
                        <i class="fa-solid fa-check"></i>
                    </div>
                </div>
            </div>

            @if($user->worship_type === 'muslim')
                <!-- Breakdown 5 Sholat Wajib -->
                @php
                    $details = $journal->ibadah_details ?? ['subuh'=>false,'dzuhur'=>false,'ashar'=>false,'maghrib'=>false,'isya'=>false];
                    $prayersCount = collect($details)->filter()->count();
                @endphp
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/60 mt-2">
                    <div class="flex items-center justify-between text-xs font-bold text-slate-700 mb-2">
                        <span>Checklist 5 Sholat Wajib:</span>
                        <span class="text-emerald-600" id="sholat-progress-text">{{ $prayersCount }}/5 Sholat</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                        @foreach(['subuh' => 'Subuh 🌅', 'dzuhur' => 'Dzuhur ☀️', 'ashar' => 'Ashar 🌤️', 'maghrib' => 'Maghrib 🌆', 'isya' => 'Isya 🌙'] as $key => $label)
                            <label class="flex items-center gap-2 p-2.5 bg-white rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 cursor-pointer hover:border-emerald-400 transition-all">
                                <input type="checkbox" name="ibadah_{{ $key }}" value="1" onchange="updatePrayerLogic()" class="prayer-checkbox w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500" {{ !empty($details[$key]) ? 'checked' : '' }}>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @else
                <!-- Breakdown Non-Muslim -->
                @php
                    $details = $journal->ibadah_details ?? ['doa_pagi'=>false,'kitab_meditasi'=>false,'doa_malam'=>false];
                @endphp
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/60 mt-2">
                    <div class="text-xs font-bold text-slate-700 mb-2">Checklist Ibadah Rutin Harian:</div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <label class="flex items-center gap-2 p-2.5 bg-white rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 cursor-pointer hover:border-emerald-400">
                            <input type="checkbox" name="ibadah_doa_pagi" value="1" onchange="updatePrayerLogic()" class="prayer-checkbox w-4 h-4 text-emerald-600 rounded border-slate-300" {{ !empty($details['doa_pagi']) ? 'checked' : '' }}>
                            <span>Doa Pagi / Saat Teduh</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 bg-white rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 cursor-pointer hover:border-emerald-400">
                            <input type="checkbox" name="ibadah_kitab" value="1" onchange="updatePrayerLogic()" class="prayer-checkbox w-4 h-4 text-emerald-600 rounded border-slate-300" {{ !empty($details['kitab_meditasi']) ? 'checked' : '' }}>
                            <span>Membaca Kitab / Meditasi</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 bg-white rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 cursor-pointer hover:border-emerald-400">
                            <input type="checkbox" name="ibadah_doa_malam" value="1" onchange="updatePrayerLogic()" class="prayer-checkbox w-4 h-4 text-emerald-600 rounded border-slate-300" {{ !empty($details['doa_malam']) ? 'checked' : '' }}>
                            <span>Doa Malam</span>
                        </label>
                    </div>
                </div>
            @endif
        </div>

        <!-- 3. BEROLAHRAGA -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:border-emerald-300 transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-lg font-bold">
                            🏃‍♂️
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900">3. Berolahraga</h4>
                            <span class="text-[11px] font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md">Kesehatan Fisik</span>
                        </div>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="berolahraga" value="1" onchange="autoSaveJournal()" class="peer sr-only" {{ $journal->berolahraga ? 'checked' : '' }}>
                        <div class="w-6 h-6 bg-slate-200 peer-focus:outline-none rounded-lg peer peer-checked:bg-emerald-500 flex items-center justify-center text-white text-xs font-bold transition-all shadow-sm">
                            <i class="fa-solid fa-check opacity-0 peer-checked:opacity-100"></i>
                        </div>
                    </label>
                </div>
                <p class="text-xs text-slate-500 mt-3 leading-relaxed">
                    Menjaga kebugaran tubuh dan kesehatan mental agar lebih fokus belajar.
                </p>
            </div>

            <!-- Optional Note -->
            <div class="mt-3 pt-3 border-t border-slate-100">
                <input type="text" name="olahraga_note" value="{{ $journal->olahraga_note }}" onblur="autoSaveJournal()"
                    placeholder="Opsional: Jenis olahraga (misal: Push up 20x / Jogging)"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>
        </div>

        <!-- 4. MAKAN SEHAT DAN BERGIZI -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:border-emerald-300 transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-teal-100 text-teal-600 flex items-center justify-center text-lg font-bold">
                            🥗
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900">4. Makan Sehat & Bergizi</h4>
                            <span class="text-[11px] font-semibold text-teal-600 bg-teal-50 px-2 py-0.5 rounded-md">Nutrisi Organik</span>
                        </div>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="makan_sehat" value="1" onchange="autoSaveJournal()" class="peer sr-only" {{ $journal->makan_sehat ? 'checked' : '' }}>
                        <div class="w-6 h-6 bg-slate-200 peer-focus:outline-none rounded-lg peer peer-checked:bg-emerald-500 flex items-center justify-center text-white text-xs font-bold transition-all shadow-sm">
                            <i class="fa-solid fa-check opacity-0 peer-checked:opacity-100"></i>
                        </div>
                    </label>
                </div>
                <p class="text-xs text-slate-500 mt-3 leading-relaxed">
                    Memenuhi kebutuhan nutrisi seimbang untuk mendukung pertumbuhan otak dan tubuh.
                </p>
            </div>

            <!-- Optional Note -->
            <div class="mt-3 pt-3 border-t border-slate-100">
                <input type="text" name="makan_note" value="{{ $journal->makan_note }}" onblur="autoSaveJournal()"
                    placeholder="Opsional: Menu makan (misal: Nasi, Sayur bayam, Telur, Buah)"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>
        </div>

        <!-- 5. GEMAR BELAJAR -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:border-emerald-300 transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-lg font-bold">
                            📚
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900">5. Gemar Belajar</h4>
                            <span class="text-[11px] font-semibold text-purple-600 bg-purple-50 px-2 py-0.5 rounded-md">Literasi & Wawasan</span>
                        </div>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="gemar_belajar" value="1" onchange="autoSaveJournal()" class="peer sr-only" {{ $journal->gemar_belajar ? 'checked' : '' }}>
                        <div class="w-6 h-6 bg-slate-200 peer-focus:outline-none rounded-lg peer peer-checked:bg-emerald-500 flex items-center justify-center text-white text-xs font-bold transition-all shadow-sm">
                            <i class="fa-solid fa-check opacity-0 peer-checked:opacity-100"></i>
                        </div>
                    </label>
                </div>
                <p class="text-xs text-slate-500 mt-3 leading-relaxed">
                    Menumbuhkan rasa ingin tahu serta semangat membaca sepanjang hayat.
                </p>
            </div>

            <!-- Optional Note -->
            <div class="mt-3 pt-3 border-t border-slate-100">
                <input type="text" name="belajar_note" value="{{ $journal->belajar_note }}" onblur="autoSaveJournal()"
                    placeholder="Opsional: Materi / Buku yang dipelajari"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>
        </div>

        <!-- 6. BERMASYARAKAT -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:border-emerald-300 transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center text-lg font-bold">
                            🤝
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900">6. Bermasyarakat</h4>
                            <span class="text-[11px] font-semibold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-md">Empati & Kerjasama</span>
                        </div>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="bermasyarakat" value="1" onchange="autoSaveJournal()" class="peer sr-only" {{ $journal->bermasyarakat ? 'checked' : '' }}>
                        <div class="w-6 h-6 bg-slate-200 peer-focus:outline-none rounded-lg peer peer-checked:bg-emerald-500 flex items-center justify-center text-white text-xs font-bold transition-all shadow-sm">
                            <i class="fa-solid fa-check opacity-0 peer-checked:opacity-100"></i>
                        </div>
                    </label>
                </div>
                <p class="text-xs text-slate-500 mt-3 leading-relaxed">
                    Mengasah rasa empati, toleransi, dan kemampuan kerja sama dengan lingkungan sekitar.
                </p>
            </div>

            <!-- Optional Note -->
            <div class="mt-3 pt-3 border-t border-slate-100">
                <input type="text" name="masyarakat_note" value="{{ $journal->masyarakat_note }}" onblur="autoSaveJournal()"
                    placeholder="Opsional: Kegiatan sosial (misal: Kerja bakti / Bantu teman)"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>
        </div>

        <!-- 7. TIDUR CEPAT -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:border-emerald-300 transition-all md:col-span-2 flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg font-bold">
                            🌙
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-slate-900">7. Tidur Cepat</h4>
                                <button type="button" onclick="autoFillTidurCepat()" class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-700 hover:bg-indigo-200 transition-all flex items-center gap-1">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                                    <span>Otomatis Isikan Jam</span>
                                </button>
                            </div>
                            <span class="text-[11px] font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md mt-0.5 inline-block">Istirahat Cukup</span>
                        </div>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="checkbox-tidur-cepat" name="tidur_cepat" value="1" onchange="onTidurCepatChange()" class="peer sr-only" {{ $journal->tidur_cepat ? 'checked' : '' }}>
                        <div class="w-6 h-6 bg-slate-200 peer-focus:outline-none rounded-lg peer peer-checked:bg-emerald-500 flex items-center justify-center text-white text-xs font-bold transition-all shadow-sm">
                            <i class="fa-solid fa-check opacity-0 peer-checked:opacity-100"></i>
                        </div>
                    </label>
                </div>
                <p class="text-xs text-slate-500 mt-3 leading-relaxed">
                    Memastikan istirahat yang cukup guna memulihkan tenaga untuk esok hari (Disarankan tidur sebelum pukul 22.00).
                </p>
            </div>

            <!-- Optional Note -->
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center gap-2">
                <input type="text" id="input-tidur-note" name="tidur_note" value="{{ $journal->tidur_note }}" onblur="autoSaveJournal()"
                    placeholder="Opsional: Jam tidur (misal: 21:30 WIB)"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <button type="button" onclick="autoFillTidurCepat()" class="px-3 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-lg text-xs font-bold whitespace-nowrap transition-all">
                    ⚡ Set Jam Sekarang
                </button>
            </div>
    </div>
    </fieldset>

    <!-- Manual Save Button -->
    <div class="flex justify-end pt-2">
        @if($journal->is_submitted)
            <button type="button" disabled class="w-full sm:w-auto px-8 py-3.5 bg-slate-200 text-slate-500 rounded-xl font-bold text-sm cursor-not-allowed flex items-center justify-center gap-2 border border-slate-300">
                <i class="fa-solid fa-lock text-slate-400"></i>
                <span>Jurnal Selesai & Terkunci Permanen</span>
            </button>
        @else
            <button type="submit" class="w-full sm:w-auto px-8 py-3.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold text-sm shadow-lg shadow-emerald-600/20 transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Simpan Jurnal Hari Ini</span>
            </button>
        @endif
    </div>
</form>

<!-- Riwayat Pengisian 7 Hari Terakhir -->
<div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 mt-6">
    <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
        <div>
            <h3 class="font-extrabold text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-emerald-500"></i>
                <span>Riwayat Pengisian Kebiasaan Selesai</span>
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Daftar pencapaian jurnal harian Anda selama 7 hari terakhir.</p>
        </div>
        <a href="{{ route('history') }}" class="text-xs font-bold text-emerald-600 hover:underline flex items-center gap-1">
            <span>Lihat Kalender</span>
            <i class="fa-solid fa-arrow-right text-[10px]"></i>
        </a>
    </div>

    <div class="space-y-3">
        @forelse($recentJournals as $rj)
            @php
                $formattedDate = \Carbon\Carbon::parse($rj->date)->translatedFormat('l, d F Y');
                $isTodayItem = ($rj->date->toDateString() === $today->toDateString());
            @endphp
            <div class="p-4 rounded-2xl border flex items-center justify-between gap-4 transition-all {{ $rj->is_fully_completed ? 'bg-emerald-50/60 border-emerald-200' : ($rj->completed_count > 0 ? 'bg-amber-50/60 border-amber-200' : 'bg-slate-50 border-slate-200/60') }}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg font-bold shadow-xs {{ $rj->is_fully_completed ? 'bg-emerald-500 text-white' : ($rj->completed_count > 0 ? 'bg-amber-400 text-amber-950' : 'bg-slate-200 text-slate-500') }}">
                        {{ $rj->is_fully_completed ? '✅' : ($rj->completed_count > 0 ? '⏳' : '🔴') }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-xs text-slate-900">{{ $formattedDate }}</h4>
                            @if($isTodayItem)
                                <span class="text-[9px] font-extrabold bg-emerald-600 text-white px-2 py-0.5 rounded-full uppercase">Hari Ini</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Status: <span class="font-extrabold {{ $rj->is_fully_completed ? 'text-emerald-700' : 'text-slate-700' }}">{{ $rj->completed_count }}/7 Kebiasaan Terisi</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs hidden sm:inline-block">
                        {{ $rj->bangun_pagi ? '🌅' : '' }}
                        {{ $rj->beribadah ? '🤲' : '' }}
                        {{ $rj->berolahraga ? '🏃‍♂️' : '' }}
                        {{ $rj->makan_sehat ? '🥗' : '' }}
                        {{ $rj->gemar_belajar ? '📚' : '' }}
                        {{ $rj->bermasyarakat ? '🤝' : '' }}
                        {{ $rj->tidur_cepat ? '🌙' : '' }}
                    </span>
                    <button type="button" onclick="showDateDetail('{{ $rj->date->toDateString() }}')" class="px-3 py-1.5 bg-white hover:bg-slate-100 border border-slate-200 rounded-xl text-slate-700 text-xs font-bold shadow-xs transition-all">
                        Rincian
                    </button>
                </div>
            </div>
        @empty
            <div class="text-center py-6 text-slate-400 text-xs">Belum ada riwayat pengisian.</div>
        @endforelse
    </div>
</div>

<!-- Modal Detail Jurnal Tanggal (Ringkasan Riwayat) -->
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

<!-- Modal Lencana & Achievements -->
<div id="badges-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl relative animate-fade-in">
        <button onclick="toggleModal('badges-modal')" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center text-sm">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="text-center mb-6">
            <div class="w-16 h-16 rounded-2xl bg-amber-100 text-amber-500 flex items-center justify-center text-3xl mx-auto mb-2 shadow-inner">
                🏆
            </div>
            <h3 class="text-xl font-extrabold text-slate-900">Lencana & Prestasi</h3>
            <p class="text-xs text-slate-500 mt-0.5">Kumpulkan seluruh lencana dengan mempertahankan streak harian!</p>
        </div>

        <div class="space-y-3 max-h-80 overflow-y-auto pr-1">
            @foreach($badges as $badge)
                <div class="p-3.5 rounded-2xl border flex items-center gap-4 transition-all {{ $badge['unlocked'] ? 'bg-emerald-50/60 border-emerald-200' : 'bg-slate-50 border-slate-200/60 opacity-60' }}">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl shadow-sm {{ $badge['unlocked'] ? 'bg-white' : 'bg-slate-200 filter grayscale' }}">
                        {{ $badge['icon'] }}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <h4 class="font-bold text-xs text-slate-800">{{ $badge['name'] }}</h4>
                            @if($badge['unlocked'])
                                <span class="text-[10px] font-extrabold text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-md">Terbuka 🎉</span>
                            @else
                                <span class="text-[10px] font-bold text-slate-400">Terkunci 🔒</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $badge['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <button onclick="toggleModal('badges-modal')" class="w-full mt-6 py-3 bg-slate-800 hover:bg-slate-900 text-white rounded-xl font-bold text-xs">
            Tutup
        </button>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function autoFillTidurCepat() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const timeString = hours + ':' + minutes + ' WIB';

        const inputNote = document.getElementById('input-tidur-note');
        const checkbox = document.getElementById('checkbox-tidur-cepat');

        if (inputNote) {
            inputNote.value = timeString;
        }
        if (checkbox) {
            checkbox.checked = true;
        }
        autoSaveJournal();
    }

    function onTidurCepatChange() {
        const checkbox = document.getElementById('checkbox-tidur-cepat');
        const inputNote = document.getElementById('input-tidur-note');

        if (checkbox && checkbox.checked && inputNote && !inputNote.value.trim()) {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            inputNote.value = hours + ':' + minutes + ' WIB';
        }
        autoSaveJournal();
    }

    function unlockForm() {
        alert('✏️ Kunci jurnal dibuka. Anda dapat mengedit atau merevisi kembali isi jurnal.');
    }

    function showDateDetail(date) {
        const modalBody = document.getElementById('modal-content-body');
        const titleElem = document.getElementById('modal-date-title');
        
        if (!modalBody || !titleElem) return;

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

    function toggleModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.toggle('hidden');
        }
    }

    function updatePrayerLogic() {
        const checkboxes = document.querySelectorAll('.prayer-checkbox');
        let allChecked = true;
        let count = 0;

        checkboxes.forEach(cb => {
            if (cb.checked) {
                count++;
            } else {
                allChecked = false;
            }
        });

        const progressText = document.getElementById('sholat-progress-text');
        if (progressText) {
            progressText.innerText = count + '/' + checkboxes.length + ' Sholat';
        }

        const masterIcon = document.getElementById('master-ibadah-icon');
        const statusBadge = document.getElementById('ibadah-status-badge');

        if (allChecked) {
            masterIcon.classList.remove('bg-slate-200', 'text-slate-400');
            masterIcon.classList.add('bg-emerald-500', 'text-white');
            if (statusBadge) {
                statusBadge.className = 'text-[11px] font-bold px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-700';
                statusBadge.innerText = 'Sudah Lengkap ✅';
            }
        } else {
            masterIcon.classList.remove('bg-emerald-500', 'text-white');
            masterIcon.classList.add('bg-slate-200', 'text-slate-400');
            if (statusBadge) {
                statusBadge.className = 'text-[11px] font-bold px-2 py-0.5 rounded-md bg-amber-100 text-amber-700';
                statusBadge.innerText = 'Belum Lengkap ⏳';
            }
        }

        autoSaveJournal();
    }

    function autoSaveJournal() {
        const form = document.getElementById('journal-form');
        const formData = new FormData(form);

        fetch("{{ route('journal.save') }}", {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update Progress Count & Bar UI
                const countElem = document.getElementById('progress-count');
                const fillElem = document.getElementById('progress-bar-fill');
                const textElem = document.getElementById('progress-percentage-text');
                const streakElem = document.getElementById('streak-counter');

                if (countElem) countElem.innerText = data.completed_count;
                if (streakElem) streakElem.innerText = data.streak + ' Hari';

                const percent = Math.round((data.completed_count / 7) * 100);
                if (fillElem) fillElem.style.width = percent + '%';
                if (textElem) textElem.innerText = percent + '% Selesai';

                // Confetti Explosion when 7/7 completed!
                if (data.is_fully_completed && typeof confetti === 'function') {
                    confetti({
                        particleCount: 100,
                        spread: 70,
                        origin: { y: 0.6 }
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error auto-saving journal:', error);
        });
    }

    function requestNotificationPermission() {
        if ('Notification' in window) {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    alert('🔔 Notifikasi berhasil diaktifkan! Kami akan mengingatkan Anda untuk mengisi jurnal di malam hari.');
                    new Notification("7 Kebiasaan Baik", {
                        body: "Pengingat Jurnal Malam aktif! Jangan lupa isi checklist sebelum tidur.",
                        icon: "https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                    });
                } else {
                    alert('Izin notifikasi ditolak. Anda dapat mengaktifkannya di pengaturan peramban Anda.');
                }
            });
        } else {
            alert('Peramban Anda tidak mendukung Notifikasi Browser.');
        }
    }
</script>
@endpush
