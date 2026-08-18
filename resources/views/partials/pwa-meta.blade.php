{{-- ===== PWA: Web App Manifest & meta (dipakai di head layouts/app & layouts/guest) ===== --}}
<link rel="manifest" href="{{ asset('manifest.json') }}">
<meta name="theme-color" content="#10B981">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="Jurnal">
<link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192.png') }}">
<link rel="icon" type="image/png" sizes="512x512" href="{{ asset('icons/icon-512.png') }}">