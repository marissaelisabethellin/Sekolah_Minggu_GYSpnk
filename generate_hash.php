<?php

$password = 'mingguceriaanak';   // ← ganti sesuai password yang diinginkan
$hash = kata_sandi_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

echo '<pre>';
echo "Password   : $password\n";
echo "Hash (bcrypt): $hash\n\n";
echo "Jalankan SQL berikut di phpMyAdmin:\n\n";
echo "UPDATE pengguna SET kata_sandi_hash = '$hash' WHERE nama_pengguna = 'admin';";
echo '</pre>';

$password = 'smgrj01'; // Password yang diinginkan

$hash = kata_sandi_hash($password, PASSWORD_BCRYPT, [
    'cost' => 12
]);

echo '<pre>';
echo "Password : $password\n";
echo "Hash     : $hash\n\n";

echo "SQL berdasarkan ID:\n";
echo "UPDATE pengguna SET kata_sandi_hash = '$hash' WHERE id = 3;\n\n";

echo "SQL berdasarkan nama_pengguna:\n";
echo "UPDATE pengguna SET kata_sandi_hash = '$hash' WHERE nama_pengguna = 'admin1';";
echo '</pre>';
