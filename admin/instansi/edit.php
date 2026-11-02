<?php

    include "../../helper/auth_admin.php";
    include "../../config/config.php";

    $id = $_GET['id'];

    $query_instansi = mysqli_query($conn, "
SELECT *
FROM instansi
WHERE id='$id'
");

    if (mysqli_num_rows($query_instansi) == 0) {
    header("Location:index.php");
    exit;
    }

    $instansi = mysqli_fetch_assoc($query_instansi);

    include "../../include/header.php";
    include "../../include/navbar.php";
    include "../../include/sidebar.php";

?>

<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header bg-warning text-dark">

            <h4 class="mb-0">
                <i class="fa-solid fa-pen"></i>
                Edit Instansi
            </h4>

        </div>

        <div class="card-body">

            <form action="proses_edit.php" method="POST">

                <input type="hidden" name="id" value="<?php echo $instansi['id']; ?>">

                <div class="mb-3">

                    <label class="form-label">
                        Nama Instansi
                    </label>

                    <input type="text" name="nama_instansi" class="form-control"
                        value="<?php echo $instansi['nama_instansi']; ?>" required>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Alamat
                    </label>

                    <textarea name="alamat" class="form-control" rows="4"
                        required><?php echo $instansi['alamat']; ?></textarea>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Kota
                    </label>

                    <input type="text" name="kota" class="form-control" value="<?php echo $instansi['kota']; ?>" required>

                </div>

                <button type="submit" class="btn btn-warning">

                    <i class="fa-solid fa-floppy-disk"></i>
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