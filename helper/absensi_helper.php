<?php
/**
 * Helper klasifikasi status kehadiran per tanggal, dipakai bersama oleh
 * halaman layar (riwayat_absensi.php) maupun halaman cetak (cetak_riwayat_absensi.php)
 * supaya hasilnya selalu konsisten.
 */

if (!function_exists('klasifikasiTanggal')) {
    /**
     * Klasifikasikan status 1 tanggal untuk 1 siswa.
     * Return: ['kode' => 'hadir|izin|sakit|alpha|libur|belum_absen|belum_waktu', 'ket' => string, 'row' => array|null]
     */
    function klasifikasiTanggal($tgl, $data_absen, $data_libur_nasional, $hari_ini) {
        $hari_ke = (int) date('N', strtotime($tgl));

        if ($hari_ke >= 6) {
            return ['kode' => 'libur', 'ket' => 'Akhir Pekan', 'row' => null];
        }
        if (isset($data_libur_nasional[$tgl])) {
            return ['kode' => 'libur', 'ket' => $data_libur_nasional[$tgl], 'row' => null];
        }
        if (isset($data_absen[$tgl])) {
            $row = $data_absen[$tgl];
            $st  = strtolower($row['status'] ?? 'hadir');
            return ['kode' => $st, 'ket' => $row['keterangan'] ?? '-', 'row' => $row];
        }
        if ($tgl == $hari_ini) {
            return ['kode' => 'belum_absen', 'ket' => 'Belum absen hari ini', 'row' => null];
        }
        if (strtotime($tgl) > strtotime($hari_ini)) {
            return ['kode' => 'belum_waktu', 'ket' => 'Masa mendatang', 'row' => null];
        }
        // Tanggal sudah lewat & tidak ada record -> idealnya sudah ditandai Alpha otomatis,
        // tapi kalau belum sempat (misalnya job auto-alpha belum jalan), anggap Alpha juga.
        return ['kode' => 'alpha', 'ket' => 'Tidak melakukan absensi', 'row' => null];
    }
}

if (!function_exists('klasifikasikanTanggalSafe')) {
    /**
     * Wrapper ringkas: kembalikan cuma kode status (untuk hitung rekap cepat).
     * 'belum_absen' & 'belum_waktu' dikembalikan sebagai null supaya tidak ikut dihitung di rekap.
     */
    function klasifikasikanTanggalSafe($tgl, $data_absen, $data_libur_nasional, $hari_ini) {
        $r = klasifikasiTanggal($tgl, $data_absen, $data_libur_nasional, $hari_ini);
        $kode = $r['kode'];
        if ($kode === 'belum_absen' || $kode === 'belum_waktu') return null;
        return $kode;
    }
}