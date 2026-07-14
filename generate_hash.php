

echo '<pre>';
echo "Password : $password\n";
echo "Hash     : $hash\n\n";

echo "SQL berdasarkan ID:\n";
echo "UPDATE pengguna SET kata_sandi_hash = '$hash' WHERE id = 3;\n\n";

echo "SQL berdasarkan nama_pengguna:\n";
echo "UPDATE pengguna SET kata_sandi_hash = '$hash' WHERE nama_pengguna = 'admin1';";
echo '</pre>';
