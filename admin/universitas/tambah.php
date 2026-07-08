<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

include "../../include/header.php";
include "../../include/navbar.php";
include "../../include/sidebar.php";

?>

<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header bg-primary text-white">

            <h4 class="mb-0">
                Tambah Universitas
            </h4>

        </div>

        <div class="card-body">

            <form action="proses_tambah.php" method="POST">

                <div class="mb-3">

                    <label class="form-label">
                        Nama Universitas
                    </label>

                    <input
                    type="text"
                    name="nama_universitas"
                    class="form-control"
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
                    required></textarea>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Kota
                    </label>

                    <input
                    type="text"
                    name="kota"
                    class="form-control"
                    required>

                </div>

                <button
                type="submit"
                class="btn btn-success">

                    <i class="fa-solid fa-floppy-disk"></i>

                    Simpan

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