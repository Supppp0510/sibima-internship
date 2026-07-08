<?php

session_start();

include "config/koneksi.php";
include "helper/activity_log.php";

activityLog(
    $conn,
    "Logout dari sistem"
);

session_destroy();

header("Location: login.php");
exit;