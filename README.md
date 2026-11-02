# SIBIMA INTERNSHIP

**Sistem Informasi Manajemen PKL / Magang Dinas PU Bina Marga Jawa Timur**

Aplikasi web berbasis PHP & MySQL untuk mengelola program praktik kerja lapangan (PKL) / magang mahasiswa dan pelajar di lingkungan Dinas PU Bina Marga: mulai dari pendaftaran, verifikasi, penempatan ruangan, absensi, pelaporan, sampai sertifikat.

---

## Daftar Isi

- [Fitur](#fitur)
- [Peran Pengguna](#peran-pengguna)
- [Teknologi](#teknologi)
- [Struktur Folder](#struktur-folder)
- [Instalasi (XAMPP)](#instalasi-xampp)
- [Konfigurasi](#konfigurasi)
- [Skema Basis Data](#skema-basis-data)
- [Konvensi Kode](#konvensi-kode)
- [Troubleshooting](#troubleshooting)
- [Rencana Pengembangan](#rencana-pengembangan)
- [Tim & Kontribusi](#tim--kontribusi)

---

## Fitur

- Registrasi & login pengguna dengan tiga peran (admin, pembimbing, pelajar)
- Pendaftaran PKL per periode, lengkap dengan verifikasi oleh admin
- Penempatan unit kerja (bidang) dan ruangan untuk setiap pendaftaran
- Absensi peserta PKL
- Laporan (termasuk tugas akhir) yang terhubung ke pendaftaran
- Penerbitan sertifikat
- Dashboard, sidebar, dan navigasi per peran
- Notifikasi dan konfirmasi interaktif (SweetAlert2)
- Tampilan responsif dengan Bootstrap 5

## Peran Pengguna

| Peran | Folder | Keterangan |
|---|---|---|
| Admin | `admin/` | Mengelola data master, memverifikasi pendaftaran, penempatan, absensi, dan sertifikat |
| Pembimbing (dospem) | `dospem/` | Memantau dan membimbing peserta bimbingannya |
| Pelajar | (halaman utama) | Mendaftar PKL, absen, dan mengumpulkan laporan |

## Teknologi

- **Backend:** PHP, MySQLi
- **Database:** MySQL / MariaDB
- **Frontend:** Bootstrap 5.3.3, Bootstrap Icons, Font Awesome, DataTables, SweetAlert2, JavaScript
- **Server lokal:** XAMPP (Apache + MySQL)

## Struktur Folder

```
sibima-internship/
├── admin/              # Halaman & modul khusus admin
├── dospem/             # Halaman & modul khusus pembimbing
├── includes/           # Komponen bersama (sidebar, koneksi, dll.)
├── assets/             # CSS, JS, gambar
├── database/           # File .sql (skema & data awal)
├── index.php
└── README.md
```

> Sesuaikan struktur di atas dengan isi folder proyek yang sebenarnya.

## Instalasi (XAMPP)

1. **Pasang XAMPP**, lalu jalankan **Apache** dan **MySQL** dari XAMPP Control Panel.
2. **Salin proyek** ke folder `htdocs`:
   ```
   C:\xampp\htdocs\sibima-internship
   ```
3. **Buat database** di phpMyAdmin (`http://localhost/phpmyadmin`), misalnya `sibima-internship`.
4. **Impor** file `.sql` dari folder `database/` ke database tersebut.
5. **Atur koneksi** database (lihat bagian [Konfigurasi](#konfigurasi)).
6. Buka di browser:
   ```
   http://localhost/sibima-internship/
   ```

## Konfigurasi

Sesuaikan kredensial koneksi pada file koneksi database proyek:

```php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "sibima-internship";
$port   = "";

$conn = mysqli_connect($host, $user, $pass, $db);
```

Setiap halaman mendefinisikan konstanta `BASE_URL` sebagai dasar tautan aset dan navigasi:

```php
define('BASE_URL', 'http://localhost/sibima-internship/');
```

## Skema Basis Data

Seluruh peran memakai satu tabel akun terpusat, dengan tabel profil terpisah per peran.

```
akun (email, password, role)
 ├── admin       (id_akun)
 ├── pembimbing  (id_akun)
 └── pelajar     (id_akun, id_jurusan, id_instansi)

pendaftaran (id_pelajar, bidang_id → unit_kerja, ruangan_id → ruangan, periode)
 ├── absensi     (id_pendaftaran)
 ├── laporan     (id_pendaftaran)
 └── sertifikat  (id_pendaftaran)

unit_kerja ── ruangan (id_unit_kerja, nama_ruangan, kuota)
```

Catatan desain:

- `pelajar` adalah entitas peserta (sebelumnya `mahasiswa`); jurusan disimpan sebagai FK `id_jurusan`.
- `id_instansi` pada `pelajar` adalah asal sekolah/kampus, berbeda dari `unit_kerja` yang merupakan bidang penempatan PKL di Bina Marga.
- Penempatan ruangan melekat pada **`pendaftaran`** (bukan `pelajar`), karena satu pelajar dapat mendaftar PKL beberapa kali dengan penempatan berbeda.
- `absensi`, `laporan`, dan `sertifikat` terhubung ke `id_pendaftaran`.
- `pendaftaran` memakai kolom `bidang_id` (bukan `unit_kerja_id`) sebagai FK ke `unit_kerja`.

Dokumen perancangan pendukung: ERD, use case, CDM, PDM, dan activity diagram per proses.

## Konvensi Kode

- Setiap halaman mendefinisikan `BASE_URL`, lalu meng-include sidebar bersama (`sidebar.php` untuk admin/pelajar, `sidebar_pembimbing.php` untuk pembimbing) agar markup tidak diduplikasi.
- Sidebar admin memakai submenu *collapsible* dengan posisi scroll disimpan lewat `sessionStorage`; menu pelajar berbentuk datar.
- Aksi pada tabel memakai menu dropdown tiga titik.
- Warna utama sidebar: biru `#2563eb`.

## Troubleshooting

**Koneksi MySQL gagal di XAMPP**
Pastikan modul MySQL berstatus *Running*, kredensial di file koneksi benar, dan nama database sudah dibuat.

**`mysqli_fetch_assoc()` error / `mysqli_query()` mengembalikan `false`**
Biasanya query memakai nama kolom lama atau yang tidak ada. Pastikan memakai `p.bidang_id` (bukan `p.unit_kerja_id`), `p.ruangan_id` pada `pendaftaran` (bukan `m.id_ruangan`), dan tidak memakai kolom `lokasi` karena tidak ada di tabel `ruangan`.

**Registrasi tidak berjalan tanpa pesan error**
Jangan menonaktifkan (`disabled`) tombol submit di dalam handler `submit` form: nilai `name=value` tombol tidak ikut terkirim sehingga `isset($_POST['register'])` selalu `false`. Gunakan input hidden atau nonaktifkan tombol setelah form terkirim.

## Rencana Pengembangan

- [ ] Login dengan pengenalan wajah sebagai metode utama (pemindaian otomatis, pencocokan 1:N ke seluruh wajah terdaftar) dengan login password sebagai cadangan
- [ ] Activity diagram untuk seluruh proses utama
- [ ] Penyempurnaan sistem notifikasi

## Tim & Kontribusi

Dikembangkan sebagai proyek magang di Dinas PU Bina Marga Jawa Timur oleh tim magang Universitas Negeri Surabaya (S1 Pendidikan Teknologi Informasi).

---

&copy; 2026 SIBIMA JATIM
