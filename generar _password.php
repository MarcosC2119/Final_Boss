<?php
// Esta es una utilidad para generar hashes de contraseña seguros
$passwords = [
    'admin123' => password_hash('admin123', PASSWORD_DEFAULT),
    'docente123' => password_hash('docente123', PASSWORD_DEFAULT)
];

echo "Hash para 'admin123': " . $passwords['admin123'] . "\n";
echo "Hash para 'docente123': " . $passwords['docente123'] . "\n";

// Verificar que los hashes funcionan
echo "\nVerificando hashes:\n";
echo "admin123: " . (password_verify('admin123', $passwords['admin123']) ? "OK" : "FAIL") . "\n";
echo "docente123: " . (password_verify('docente123', $passwords['docente123']) ? "OK" : "FAIL") . "\n";