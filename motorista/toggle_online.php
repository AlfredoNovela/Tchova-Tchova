<?php
require "../config.php";

// Lê o JSON enviado via fetch()
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["id"])) {
    echo json_encode(["success" => false, "message" => "ID não enviado"]);
    exit;
}

$id = intval($data["id"]);

// Faz toggle do estado online/offline
$stmt = $pdo->prepare("UPDATE motoristas SET online = NOT online WHERE id = ?");
$stmt->execute([$id]);

// Retorna o estado atualizado
$stmt2 = $pdo->prepare("SELECT online FROM motoristas WHERE id = ?");
$stmt2->execute([$id]);
$online = $stmt2->fetchColumn();

echo json_encode([
    "success" => true,
    "online" => (int)$online
]);
