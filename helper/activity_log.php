<?php

function activityLog($conn, $aktivitas)
{
    if(session_status() == PHP_SESSION_NONE){
        session_start();
    }

    if(!isset($_SESSION['id'])){
        return;
    }

    $user_id = $_SESSION['id'];

    $ip = $_SERVER['REMOTE_ADDR'];

    mysqli_query($conn,"
        INSERT INTO activity_logs
        (
            user_id,
            aktivitas,
            ip_address
        )
        VALUES
        (
            '$user_id',
            '$aktivitas',
            '$ip'
        )
    ");
}