<?php
session_start();
require "../config.php";
header('Content-Type: application/json');

if(!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "passageiro"){
    echo json_encode(['ok'=>false,'erro'=>'Acesso negado']);
    exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    echo json_encode(['ok'=>false,'erro'=>'Método inválido']);
    exit;
}

$id_viagem = isset($_POST['id_viagem']) ? intval($_POST['id_viagem']) : 0;
$id_passageiro = intval($_SESSION['id']);

if($id_viagem <= 0){
    echo json_encode(['ok'=>false,'erro'=>'Viagem inválida']);
    exit;
}

$stmt = $pdo->prepare("UPDATE viagens SET estado='cancelada' 
                       WHERE id=? AND id_passageiro=? AND estado='pendente'");
$ok = $stmt->execute([$id_viagem, $id_passageiro]);

if($ok){
    echo json_encode(['ok'=>true]);
}else{
    echo json_encode(['ok'=>false,'erro'=>'Falha ao cancelar viagem']);
}
