<?php
session_start();
require "../config.php";
header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    http_response_code(400);
    echo json_encode(['error'=>'id faltando']);
    exit;
}

// segurança: só o próprio motorista ou admin pode ver
if (!isset($_SESSION['id']) || ($_SESSION['id'] != $id && $_SESSION['tipo'] !== 'admin')) {
    http_response_code(403);
    echo json_encode(['error'=>'Acesso negado']);
    exit;
}

$stmt = $pdo->prepare("SELECT lat, lng FROM motoristas WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    http_response_code(404);
    echo json_encode(['error'=>'Não encontrado']);
    exit;
}

echo json_encode([
    'lat' => $row['lat'],
    'lng' => $row['lng']
]);
