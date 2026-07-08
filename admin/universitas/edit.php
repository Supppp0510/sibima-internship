<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$id = $_GET['id'];

$data = mysqli_query($conn,"
SELECT *
FROM universitas
WHERE id='$id'
");

if(mysqli_num_rows($data)==0){
    header("Location:index.php");
    exit;
}

$row = mysqli_fetch_assoc($data);

include "../../include/header.php";
include "../../include/navbar.php";
include "../../include/sidebar.php";

?>

<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header bg-warning text-dark">

            <h4 class="mb-0">
                <i class="fa-solid fa-pen"></i>
                Edit Universitas
            </h4>

        </div>

        <div class="card-body">

            <form action="proses_edit.php" method="POST">

                <input
                type="hidden"
                name="id"
                value="<?= $row['id']; ?>">

                <div class="mb-3">

                    <label class="form-label">
                        Nama Universitas
                    </label>

                    <input
                    type="text"
                    name="nama_universitas"
                    class="form-control"
                    value="<?= $row['nama_universitas']; ?>"
                    required>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Alamat
                    </label>

                    <textarea
                    name="alamat"
                    class="form-control"
                    rows="4"
                    required><?= $row['alamat']; ?></textarea>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Kota
                    </label>

                    <input
                    type="text"
                    name="kota"
                    class="form-control"
                    value="<?= $row['kota']; ?>"
                    required>

                </div>

                <button
                type="submit"
                class="btn btn-warning">

                    <i class="fa-solid fa-floppy-disk"></i>
                    Update

                </button>

                <a
                href="index.php"
                class="btn btn-secondary">

                    Kembali

                </a>

            </form>

        </div>

    </div>

</div>

<?php
include "../../include/footer.php";
?>