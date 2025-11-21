<?php
session_start();
require "../config.php";

header('Content-Type: application/json');

if(!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'motorista'){
    echo json_encode(['ok'=>false,'erro'=>'Não autorizado']);
    exit;
}

$id_motorista = intval($_SESSION['id']);
$id_viagem = intval($_POST['id_viagem'] ?? 0);

if($id_viagem === 0){
    echo json_encode(['ok'=>false,'erro'=>'ID da viagem inválido']);
    exit;
}

// Atualiza o estado da viagem para 'andamento' apenas se estiver 'aceita'
$stmt = $pdo->prepare("UPDATE viagens SET estado='andamento' WHERE id=? AND id_motorista=? AND estado='aceita'");
if($stmt->execute([$id_viagem, $id_motorista])){
    if($stmt->rowCount() > 0){
        echo json_encode(['ok'=>true]);
    } else {
        echo json_encode(['ok'=>false,'erro'=>'Não foi possível iniciar a viagem (estado incorreto ou viagem não encontrada)']);
    }
} else {
    echo json_encode(['ok'=>false,'erro'=>'Erro no banco de dados']);
}
