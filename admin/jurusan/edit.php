<?php

    include "../../helper/auth_admin.php";
    include "../../config/config.php";

    $id = $_GET['id'];

    $jurusan_query = mysqli_query($conn, "
SELECT *
FROM jurusan
WHERE id='$id'
");

    if (mysqli_num_rows($jurusan_query) == 0) {
    header("Location:index.php");
    exit;
    }

    $jurusan = mysqli_fetch_assoc($jurusan_query);

    include "../../include/header.php";
    include "../../include/navbar.php";
    include "../../include/sidebar.php";

?>

<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header bg-warning">

            <h4> Edit Jurusan </h4>

        </div>

        <div class="card-body">

            <form action="proses_edit.php" method="POST">

                <input type="hidden" name="id" value="<?php echo $jurusan['id']; ?>">

                <div class="mb-3">

                    <label>Nama Jurusan</label>

                    <input type="text" name="nama_jurusan" class="form-control"
                        value="<?php echo $jurusan['nama_jurusan']; ?>" required>

                </div>

                <div class="mb-3">

                    <label>Jenjang</label>

                    <select name="jenjang" class="form-control">

                        <option value="SMA" <?php echo $jurusan['jenjang']=="SMA" ? "selected" : ""; ?>>
                            SMA
                        </option>

                        <option value="SMK" <?php echo $jurusan['jenjang']=="SMK" ? "selected" : ""; ?>>
                            SMK
                        </option>

                        <option value="MA" <?php echo $jurusan['jenjang']=="MA" ? "selected" : ""; ?>>
                            MA
                        </option>
                    
                        <option value="D3" <?php echo $jurusan['jenjang']=="D3" ? "selected" : ""; ?>>
                            D3
                        </option>

                        <option value="D4" <?php echo $jurusan['jenjang']=="D4" ? "selected" : ""; ?>>
                            D4
                        </option>

                        <option value="S1" <?php echo $jurusan['jenjang']=="S1" ? "selected" : ""; ?>>
                            S1
                        </option>

                        <option value="S2" <?php echo $jurusan['jenjang']=="S2" ? "selected" : ""; ?>>
                            S2
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