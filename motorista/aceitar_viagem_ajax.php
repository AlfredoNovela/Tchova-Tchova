<?php
session_start();
require "../config.php";
header("Content-Type: application/json");

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "motorista") {
    echo json_encode(["erro" => "Acesso negado"]);
    exit;
}

$id_motorista = intval($_SESSION["id"]);
$id_viagem = intval($_POST["id_viagem"]);

$stmt = $pdo->prepare("SELECT estado FROM viagens WHERE id=?");
$stmt->execute([$id_viagem]);
$viagem = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$viagem){
    echo json_encode(["erro"=>"Viagem não encontrada"]);
    exit;
}

if($viagem['estado'] !== 'pendente'){
    echo json_encode(["erro"=>"Esta viagem já foi aceita"]);
    exit;
}

// Atualiza viagem para aceita e associa motorista
$stmt = $pdo->prepare("UPDATE viagens SET estado='aceita', id_motorista=? WHERE id=?");
$stmt->execute([$id_motorista, $id_viagem]);

echo json_encode(["sucesso"=>true]);
