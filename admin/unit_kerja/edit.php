<?php
    include "../../helper/auth_admin.php";
    include "../../config/config.php";

    $id = $_GET['id'];

    $query_unit = mysqli_query($conn, "
        SELECT *
        FROM unit_kerja
        WHERE id='$id'
    ");

    if (mysqli_num_rows($query_unit) == 0) {
        header("Location: index.php");
        exit;
    }

    $unitkerja = mysqli_fetch_assoc($query_unit);

    include "../../include/header.php";
    include "../../include/navbar.php";
    include "../../include/sidebar.php";
?>

<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header bg-warning">
            <h4>Edit Unit Kerja</h4>
        </div>

        <div class="card-body">

            <form action="proses_edit.php" method="POST">

                <input type="hidden" name="id" value="<?php echo $unitkerja['id']; ?>">

                <div class="mb-3">
                    <label>Unit Kerja</label>
                    <input type="text" name="unit_kerja" class="form-control"
                        value="<?php echo $unitkerja ['unit_kerja']; ?>" required>
                </div>

                <!-- Eselon II Otomatis Terisi & Dikunci -->
                <div class="mb-3">
                    <label>Unit Kerja Eselon II</label>
                    <input type="text" class="form-control bg-light" value="DINAS PEKERJAAN UMUM BINA MARGA" disabled>
                    <input type="hidden" name="unit_kerja_es_ii" value="DINAS PEKERJAAN UMUM BINA MARGA">
                </div>

                <div class="mb-3">
                    <label>Unit Kerja Eselon III</label>
                    <input type="text" name="unit_kerja_es_iii" class="form-control"
                        value="<?php echo $unitkerja['unit_kerja_es_iii']; ?>" placeholder="Opsional">
                </div>

                <div class="mb-3">
                    <label>Unit Kerja Eselon IV</label>
                    <input type="text" name="unit_kerja_es_iv" class="form-control"
                        value="<?php echo $unitkerja['unit_kerja_es_iv']; ?>" placeholder="Opsional">
                </div>

                <div class="mb-3">
                    <label>Kuota</label>
                    <input type="number" name="kuota" class="form-control"
                        value="<?php echo $unitkerja['kuota']; ?>" required>
                </div>

                <button class="btn btn-warning">
                    <i class="fa-solid fa-pen"></i>
                    Update
                </button>

                <a href="index.php" class="btn btn-secondary">
                    Kembali
                </a>

            </form>

        </div>

    </div>

</div>

<?php
include "../../include/footer.php";
?>