<?php
session_start();
require "../config.php";

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "motorista") {
    header("Location: ../index.php");
    exit;
}

$id = (int)$_SESSION["id"];

try {
    // Seleciona todas as viagens do motorista
    $stmt = $pdo->prepare("SELECT * FROM viagens WHERE id_motorista = :id_motorista ORDER BY id DESC");
    $stmt->execute(['id_motorista' => $id]);
    $viagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Erro ao buscar viagens do motorista: " . $e->getMessage());
    $viagens = [];
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Minhas Viagens</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

<div class="topbar">
    <img src="../assets/img/logo.png" class="logo" alt="logo">
    <span class="top-title">Minhas Viagens</span>
</div>

<div class="container">
    <?php if (empty($viagens)): ?>
        <p>Você ainda não aceitou nenhuma viagem.</p>
    <?php else: ?>
        <?php foreach ($viagens as $d): ?>
            <div class="card">
                <p><b>Origem:</b> <?= htmlspecialchars($d['origem'] ?: 'Não informado') ?></p>
                <p><b>Destino:</b> <?= htmlspecialchars($d['destino'] ?: 'Não informado') ?></p>
                <p><b>Estado:</b> <?= htmlspecialchars($d['estado']) ?></p>

                <?php if ($d['estado'] === 'aceita'): ?>
                    <a href="finalizar.php?id=<?= (int)$d['id'] ?>" class="btn">Finalizar Viagem</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
