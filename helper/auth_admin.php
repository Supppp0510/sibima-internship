<?php

include "auth.php";

if ($_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit;
}