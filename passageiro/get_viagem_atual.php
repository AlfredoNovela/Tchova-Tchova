<?php
session_start();
require "../config.php";
header('Content-Type: application/json');

if(!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "passageiro"){
    echo json_encode(['ok'=>false,'erro'=>'Acesso negado']);
    exit;
}

$id_passageiro = intval($_SESSION['id']);

$stmt = $pdo->prepare("SELECT v.*, u.nome AS nome_motorista, u.marca, u.modelo, u.matricula
                       FROM viagens v
                       LEFT JOIN usuarios u ON u.id = v.id_motorista
                       WHERE v.id_passageiro = ? AND v.estado IN ('pendente','aceita','andamento')
                       ORDER BY v.data_hora DESC LIMIT 1");
$stmt->execute([$id_passageiro]);
$viagem = $stmt->fetch();

echo json_encode(['ok'=>true, 'viagem'=>$viagem]);
