<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

include "../../include/header.php";
include "../../include/navbar.php";
include "../../include/sidebar.php";

?>

<div class="container-fluid">

    <h3 class="mb-4">Tambah Bidang</h3>

    <div class="card shadow">

        <div class="card-body">

            <form action="proses_tambah.php" method="POST">

                <div class="mb-3">

                    <label>Nama Bidang</label>

                    <input type="text"
                           name="nama_bidang"
                           class="form-control"
                           required>

                </div>

                <div class="mb-3">

                    <label>Kuota</label>

                    <input type="number"
                           name="kuota"
                           class="form-control"
                           required>

                </div>

                <div class="mb-3">

                    <label>Deskripsi</label>

                    <textarea
                        name="deskripsi"
                        class="form-control"
                        rows="4"></textarea>

                </div>

                <div class="mb-3">

                    <label>Status</label>

                    <select
                        name="status"
                        class="form-control">

                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Non Aktif</option>

                    </select>

                </div>

                <button class="btn btn-primary">

                    Simpan

                </button>

                <a href="index.php"
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