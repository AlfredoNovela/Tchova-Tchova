<?php
session_start();
require "../config.php";
header('Content-Type: application/json');

if(!isset($_SESSION["id"]) || $_SESSION["tipo"]!=="motorista"){
    echo json_encode(['ok'=>false]);
    exit;
}

$stmt = $pdo->query("SELECT v.*, u.nome AS nome_passageiro 
                     FROM viagens v 
                     JOIN usuarios u ON u.id=v.id_passageiro
                     WHERE v.estado='pendente'
                     ORDER BY v.data_hora DESC");
$viagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['ok'=>true, 'viagens'=>$viagens]);
