<?php
require 'db/conexion.php';

$stmt = $conexion->prepare('SELECT id, password FROM usuarios WHERE email = ?');
$stmt->execute(['barista@lacomanda.com']);
$result = $stmt->fetch();

$testPass = 'NewBarista789@';
$isValid = password_verify($testPass, $result['password']);

echo "Email: barista@lacomanda.com" . PHP_EOL;
echo "Hash: " . substr($result['password'], 0, 30) . "..." . PHP_EOL;
echo "Test password: " . $testPass . PHP_EOL;
echo "Password valid: " . ($isValid ? "YES ✓" : "NO ✗") . PHP_EOL;
