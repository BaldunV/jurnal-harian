<div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1.5">
        @include('partials.icon', ['name' => 'camera', 'class' => 'w-3 h-3 inline-block align-[-1px] text-teal-500 mr-1'])
        Foto Bukti <span class="text-slate-400 font-semibold">(Opsional)</span>
    </label>

    <div id="{{ $type }}-photo-empty" class="{{ $journal->{$type.'_photo'} ? 'hidden' : '' }}">
        <div class="flex items-center gap-2">
            <label for="file-{{ $type }}-camera" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-600 text-xs font-bold text-slate-600 dark:text-slate-300 cursor-pointer hover:border-teal-400 dark:hover:border-teal-500/50 hover:text-teal-600 dark:hover:text-teal-300 transition-all">
                <input type="file" id="file-{{ $type }}-camera" accept="image/*" capture="environment" class="hidden" onchange="handlePhotoUpload('{{ $type }}', this)">
                @include('partials.icon', ['name' => 'camera', 'class' => 'w-3.5 h-3.5'])
                Kamera
            </label>
            <label for="file-{{ $type }}-gallery" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-600 text-xs font-bold text-slate-600 dark:text-slate-300 cursor-pointer hover:border-teal-400 dark:hover:border-teal-500/50 hover:text-teal-600 dark:hover:text-teal-300 transition-all">
                <input type="file" id="file-{{ $type }}-gallery" accept="image/*" class="hidden" onchange="handlePhotoUpload('{{ $type }}', this)">
                @include('partials.icon', ['name' => 'image', 'class' => 'w-3.5 h-3.5'])
                Galeri
            </label>
            <span id="{{ $type }}-photo-uploading" class="hidden text-[10px] font-extrabold text-teal-600 dark:text-teal-300 items-center gap-1.5">
                @include('partials.icon', ['name' => 'loader-circle', 'class' => 'w-3.5 h-3.5 animate-spin'])
                Mengunggah...
            </span>
        </div>
        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1.5 font-medium">Bukti kegiatan — tampil di Riwayat &amp; Recap wali kelas.</p>
    </div>

    <div id="{{ $type }}-photo-preview" class="{{ $journal->{$type.'_photo'} ? 'flex' : 'hidden' }} items-center gap-3">
        <img id="{{ $type }}-photo-img" src="{{ $journal->{$type.'_photo_url'} }}" alt="Foto bukti {{ $type }}" class="w-24 h-24 rounded-xl object-cover border border-slate-200 dark:border-slate-600 shadow-sm">
        <button type="button" onclick="removePhoto('{{ $type }}')" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-rose-50 dark:bg-rose-500/15 text-rose-600 dark:text-rose-300 text-[11px] font-bold hover:bg-rose-100 dark:hover:bg-rose-500/25 transition-all">
            @include('partials.icon', ['name' => 'trash-2', 'class' => 'w-3.5 h-3.5'])
            Hapus
        </button>
    </div>
</div>