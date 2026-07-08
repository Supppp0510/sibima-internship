<?php

include "auth.php";

if ($_SESSION['role_id'] != 3) {
    header("Location: ../login.php");
    exit;
}