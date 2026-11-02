<?php

    include "../../helper/auth_admin.php";
    include "../../config/config.php";

    $id = $_GET['id'];

    $query_periode = mysqli_query($conn, "
SELECT *
FROM periode_pkl
WHERE id='$id'
");

    if (mysqli_num_rows($query_periode) == 0) {

    header("Location:index.php");
    exit;

    }

    $periode = mysqli_fetch_assoc($query_periode);

    include "../../include/header.php";
    include "../../include/navbar.php";
    include "../../include/sidebar.php";

?>

<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header bg-warning">

            <h4>Edit Periode PKL</h4>

        </div>

        <div class="card-body">

            <form action="proses_edit.php" method="POST">

                <input type="hidden" name="id" value="<?php echo $periode['id']; ?>">

                <div class="mb-3">

                    <label>Nama Periode</label>

                    <input type="text" name="nama_periode" class="form-control"
                        value="<?php echo $periode['nama_periode']; ?>" required>

                </div>

                <div class="mb-3">

                    <label>Tanggal Mulai</label>

                    <input type="date" name="tanggal_mulai" class="form-control"
                        value="<?php echo $periode['tanggal_mulai']; ?>" required>

                </div>

                <div class="mb-3">

                    <label>Tanggal Selesai</label>

                    <input type="date" name="tanggal_selesai" class="form-control"
                        value="<?php echo $periode['tanggal_selesai']; ?>" required>

                </div>

                <div class="mb-3">

                    <label>Status</label>

                    <select name="status" class="form-control">

                        <option value="aktif" <?php echo ($periode['status']=="aktif") ? "selected" : ""; ?>>
                            Aktif
                        </option>

                        <option value="selesai" <?php echo ($periode['status']=="selesai") ? "selected" : ""; ?>>
                            Selesai
                        </option>

                    </select>

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