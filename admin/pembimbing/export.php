<?php
include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../config/koneksi.php";

// Nama file export dengan tanggal hari ini
$filename = "Data_Pembimbing_SIPKLa_" . date('Y-m-d') . ".xls";

// Header untuk mendownload file sebagai Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Ambil data pembimbing beserta unit kerjanya
$query = mysqli_query($conn, "
    SELECT 
        p.*, 
        u.unit_kerja AS nama_unit_kerja
    FROM pembimbing p
    LEFT JOIN unit_kerja u 
        ON p.unit_kerja_id = u.id
    ORDER BY p.id DESC
");
?>

<table border="1">
    <thead>
        <tr style="background-color: #2563eb; color: #ffffff; font-weight: bold; text-align: center;">
            <th>No</th>
            <th>NIP</th>
            <th>Nama Pembimbing & Gelar</th>
            <th>Email</th>
            <th>Unit Kerja</th>
            <th>Golongan Ruang</th>
            <th>Pangkat</th>
            <th>Nama Jabatan</th>
            <th>Jenis Jabatan</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        while ($row = mysqli_fetch_assoc($query)) {
        ?>
        <tr>
            <td style="text-align: center;"><?= $no++; ?></td>
            <td style="mso-number-format:'\@'; text-align: center;"><?= htmlspecialchars($row['nip'] ?? '-'); ?></td>
            <td><?= htmlspecialchars($row['nama']); ?></td>
            <td><?= htmlspecialchars($row['email'] ?? '-'); ?></td>
            <td><?= htmlspecialchars($row['nama_unit_kerja'] ?? '-'); ?></td>
            <td style="text-align: center;"><?= htmlspecialchars($row['golongan_ruang'] ?? '-'); ?></td>
            <td><?= htmlspecialchars($row['pangkat'] ?? '-'); ?></td>
            <td><?= htmlspecialchars($row['nama_jabatan'] ?? '-'); ?></td>
            <td><?= htmlspecialchars($row['jenis_jabatan'] ?? '-'); ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>