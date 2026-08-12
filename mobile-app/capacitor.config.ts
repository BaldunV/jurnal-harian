import { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'com.smkbppi.jurnal',
  appName: 'Jurnal 7 Kebiasaan',
  webDir: 'www',
  server: {
    // GANTI INI: Gunakan alamat IP lokal komputer ini (misal: 'http://192.168.1.x:8000') 
    // jika ingin menguji di emulator lokal atau HP yang tersambung di WiFi yang sama,
    // atau gunakan domain live (misal: 'https://jurnal.smkbppi.com') jika aplikasi sudah online.
    url: 'http://192.168.1.100', 
    cleartext: true
  }
};

export default config;
