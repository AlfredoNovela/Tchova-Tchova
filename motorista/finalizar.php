<?php
session_start();
require "../config.php"; // aqui já define $pdo

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "motorista") {
    header("Location: ../index.php");
    exit;
}

$id_viagem = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_viagem <= 0) {
    die("ID de viagem inválido.");
}

try {
    // Atualiza o estado da viagem para 'finalizada'
    $stmt = $pdo->prepare("UPDATE viagens SET estado='finalizada' WHERE id = ? AND id_motorista = ?");
    $stmt->execute([$id_viagem, $_SESSION['id']]);

    // Redireciona de volta para Minhas Viagens
    header("Location: minhas_viagens.php?msg=viagem_finalizada");
    exit;
} catch (Exception $e) {
    die("Erro ao finalizar a viagem: " . $e->getMessage());
}
