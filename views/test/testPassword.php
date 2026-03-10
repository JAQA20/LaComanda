<?php
require __DIR__ . '/../../model/Conexion.php';

$stmt = $conexion->prepare('SELECT id, password FROM usuarios WHERE email = ?');
$stmt->execute(['admin@lacomanda.com']);
$result = $stmt->fetch();

$testPass = 'TestPass123#';
$isValid = password_verify($testPass, $result['password']);

echo "Hash from DB: " . substr($result['password'], 0, 30) . "..." . PHP_EOL;
echo "Test password: " . $testPass . PHP_EOL;
echo "Password verified: " . ($isValid ? "YES ✓" : "NO ✗") . PHP_EOL;
echo "User ID: " . $result['id'] . PHP_EOL;
