<?php

include "auth.php";

if ($_SESSION['role_id'] != 2) {
    header("Location: ../login.php");
    exit;
}