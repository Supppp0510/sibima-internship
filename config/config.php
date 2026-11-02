<?php

date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/base_url.php';
require_once __DIR__ . '/koneksi.php';

define('SECRET_KEY', 'SIPKL_BIMA_JATIM_2026');
function encrypt_password($plaintext_password) {
    return base64_encode(openssl_encrypt($plaintext_password, "AES-128-ECB", SECRET_KEY));
}

function decrypt_password($encrypted_password) {
    return openssl_decrypt(base64_decode($encrypted_password), "AES-128-ECB", SECRET_KEY);
}