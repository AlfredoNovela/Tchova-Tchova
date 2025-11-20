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

// Ganhos hoje
$stmt = $pdo->prepare("SELECT COALESCE(SUM(valor),0) AS soma FROM viagens WHERE id_motorista = ? AND DATE(data_hora) = CURDATE() AND estado = 'concluida'");
$stmt->execute([$id]);
$g = $stmt->fetch(PDO::FETCH_ASSOC);
$ganhosHoje = $g ? number_format((float)$g['soma'], 2, '.', '') : "0.00";

// Viagens concluídas hoje
$stmt2 = $pdo->prepare("SELECT COUNT(*) AS cnt FROM viagens WHERE id_motorista = ? AND DATE(data_hora) = CURDATE() AND estado = 'concluida'");
$stmt2->execute([$id]);
$v = $stmt2->fetch(PDO::FETCH_ASSOC);
$viagensConcluidas = $v ? (int)$v['cnt'] : 0;

// Avaliação placeholder — depois pode pegar média de avaliações
$avaliacao = "★ ★ ★ ★ ☆";

echo json_encode([
    'ganhosHoje' => $ganhosHoje,
    'viagensConcluidas' => $viagensConcluidas,
    'avaliacao' => $avaliacao
]);
