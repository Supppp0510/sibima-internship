<?php
/**
 * Menentukan URL foto profil siswa/pelajar yang valid.
 *
 * Foto profil bisa tersimpan di beberapa folder berbeda tergantung cara input:
 * - assets/upload/pelajar/profile/  -> upload sendiri lewat halaman profil pelajar (folder aktif saat ini)
 * - assets/upload/mahasiswa/foto/   -> peninggalan modul mahasiswa lama (kalau masih ada datanya)
 * - assets/upload/foto/             -> peninggalan cara input lama lainnya
 *
 * Fungsi ini mengecek LANGSUNG DI SERVER (file_exists) folder mana yang benar-benar
 * punya filenya, baru mengembalikan URL yang tepat. Kalau tidak ketemu di semuanya,
 * mengembalikan string kosong supaya halaman bisa fallback ke inisial nama.
 *
 * @param string $nama_file Nilai kolom foto dari database (tabel pelajar/mahasiswa)
 * @return string URL foto (pakai BASE_URL) atau '' kalau tidak ditemukan
 */
if (!function_exists('getFotoProfilUrl')) {
    function getFotoProfilUrl($nama_file) {
        if (empty($nama_file)) {
            return '';
        }

        // __DIR__ di sini = folder helper/, jadi naik 1 level ke root project
        $root_fs = realpath(__DIR__ . '/..');

        $kandidat = [
            'assets/upload/pelajar/profile/' => $root_fs . '/assets/upload/pelajar/profile/' . $nama_file,
            'assets/upload/mahasiswa/foto/'  => $root_fs . '/assets/upload/mahasiswa/foto/' . $nama_file,
            'assets/upload/foto/'            => $root_fs . '/assets/upload/foto/' . $nama_file,
        ];

        foreach ($kandidat as $url_relatif => $path_fisik) {
            if (file_exists($path_fisik)) {
                return BASE_URL . $url_relatif . $nama_file;
            }
        }

        return '';
    }
}