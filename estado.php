<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');

$usuario = usuario_logado();
echo json_encode([
    'logado' => $usuario !== null,
    'admin' => $usuario !== null && ($usuario['papel'] ?? '') === 'admin',
    'nome' => $usuario['nome'] ?? '',
], JSON_UNESCAPED_UNICODE);
