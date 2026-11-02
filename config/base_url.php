<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

// Deteksi otomatis folder proyek relatif terhadap document root server
$project_root = strtolower(str_replace('\\', '/', realpath(__DIR__ . '/..')));
$doc_root     = strtolower(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])));

$folder = '';
if ($doc_root !== '' && strpos($project_root, $doc_root) === 0) {
    $folder = substr($project_root, strlen($doc_root));
}
// Pertahankan huruf asli nama folder
$folder = ($folder === '') ? '' : substr(str_replace('\\', '/', realpath(__DIR__ . '/..')), -strlen($folder));

define('BASE_URL', $protocol . '://' . $_SERVER['HTTP_HOST'] . $folder . '/');