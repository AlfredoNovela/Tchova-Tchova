<?php
session_start();
require "../config.php";
header('Content-Type: application/json');

if(!isset($_SESSION['id']) || $_SESSION['tipo']!=='motorista'){
    echo json_encode(['ok'=>false,'erro'=>'Não autorizado']); exit;
}

$id_viagem = intval($_POST['id_viagem'] ?? 0);
$id_motorista = intval($_SESSION['id']);

$stmt = $pdo->prepare("UPDATE viagens SET estado='concluida' WHERE id=? AND id_motorista=? AND estado='andamento'");
$ok = $stmt->execute([$id_viagem,$id_motorista]);
echo json_encode(['ok'=>$ok]);
