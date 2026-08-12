# Flowchart dan ERD Sistem Jurnal 7 Kebiasaan Baik

## Flowchart alur aplikasi

```mermaid
flowchart TD
    A([Mulai]) --> B[Halaman Login]
    B --> C{Pilih portal}
    C -->|Siswa| D[Validasi NIS dan password]
    C -->|Admin / Guru| E[Validasi ID dan password]
    D --> F{Akun siswa valid?}
    E --> G{Akun admin/guru valid?}
    F -->|Tidak| H[Tampilkan pesan login gagal]
    G -->|Tidak| H
    H --> B
    F -->|Ya| I[Dashboard Siswa]
    G -->|Admin| J[Dashboard Admin]
    G -->|Guru| K[Data dan pemantauan siswa]

    I --> L[Isi 7 kebiasaan harian]
    L --> M[Bangun pagi dan tidur cepat: waktu perangkat]
    M --> N[Simpan otomatis jurnal]
    N --> O{Simpan jurnal final?}
    O -->|Ya| P[Jurnal dikunci]
    O -->|Belum| L
    P --> Q[Riwayat dan statistik siswa]

    J --> R[Rekap semua siswa]
    R --> S[Filter kelas / cari siswa]
    S --> T[Rekap mingguan dan bulanan]
    J --> U[Data siswa dan detail riwayat]

    K --> U
    Q --> V([Selesai])
    T --> V
    U --> V
```

## ERD database inti

```mermaid
erDiagram
    USERS ||--o{ JOURNALS : "mengisi"
    USERS ||--o{ SESSIONS : "memiliki"

    USERS {
        bigint id PK
        string nis UK
        string name
        string email
        string password
        string role "admin | guru | siswa"
        string kelas
        string worship_type "muslim | non_muslim"
        datetime created_at
        datetime updated_at
    }

    JOURNALS {
        bigint id PK
        bigint user_id FK
        date date
        boolean bangun_pagi
        time bangun_pagi_time
        boolean beribadah
        json ibadah_details
        boolean berolahraga
        string olahraga_note
        boolean makan_sehat
        string makan_note
        boolean gemar_belajar
        string belajar_note
        boolean bermasyarakat
        string masyarakat_note
        boolean tidur_cepat
        string tidur_note
        integer completed_count
        boolean is_fully_completed
        boolean is_submitted
        datetime created_at
        datetime updated_at
    }

    SESSIONS {
        string id PK
        bigint user_id FK
        string ip_address
        text user_agent
        longtext payload
        integer last_activity
    }

    PASSWORD_RESET_TOKENS {
        string email PK
        string token
        datetime created_at
    }
```

Catatan: satu siswa dapat memiliki banyak jurnal, tetapi hanya satu jurnal untuk setiap tanggal (`user_id` + `date`). Akun admin dan guru tidak mengisi jurnal; keduanya menggunakan data jurnal siswa untuk pemantauan dan rekap.
