<?php
session_start();
require "../config.php";

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "motorista") {
    header("Location: ../index.php");
    exit;
}

$id_motorista = (int)$_SESSION["id"];

try {
    // Seleciona todas as viagens do motorista com info do passageiro
    $stmt = $pdo->prepare("
        SELECT v.*, u.nome AS nome_passageiro 
        FROM viagens v 
        JOIN usuarios u ON u.id = v.id_passageiro
        WHERE v.id_motorista = :id_motorista 
        ORDER BY v.id DESC
    ");
    $stmt->execute(['id_motorista' => $id_motorista]);
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
<style>
.container { margin-top:20px; display:flex; flex-direction:column; gap:15px; }
.card .btn { display:inline-block; margin-top:10px; padding:8px 14px; background:#1B4FA0; color:#fff; text-decoration:none; border-radius:6px; transition:0.3s; }
.card .btn:hover { background:#2A66CA; }
</style>
</head>
<body>

<div class="sidebar">
    <div class="brand">
        <img src="../assets/img/logo.png" class="brand-logo" alt="Logo">
        <h2>Tchova-Tchova</h2>
    </div>

    <nav>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="novas_viagens.php">🚗 Novas Viagens</a>
        <a href="minhas_viagens.php">📌 Minhas Viagens</a>
        <a class="logout" href="../logout.php">Sair</a>
    </nav>
</div>

<div class="main">
    <header>
        <h1>Minhas Viagens</h1>
        <p>Lista de todas as viagens que você aceitou.</p>
    </header>

    <div class="container">
        <?php if (empty($viagens)): ?>
            <p>Você ainda não aceitou nenhuma viagem.</p>
        <?php else: ?>
            <?php foreach ($viagens as $v): ?>
                <div class="card">
                    <p><b>Passageiro:</b> <?= htmlspecialchars($v['nome_passageiro']) ?></p>
                    <p><b>Origem:</b> <?= htmlspecialchars($v['origem'] ?: '-') ?></p>
                    <p><b>Destino:</b> <?= htmlspecialchars($v['destino'] ?: '-') ?></p>
                    <p><b>Estado:</b> <?= htmlspecialchars($v['estado']) ?></p>
                    <p><b>Valor:</b> <?= isset($v['valor']) ? 'MTN$ '.number_format($v['valor'],2) : '-' ?></p>

                    <?php if ($v['estado'] === 'aceita'): ?>
                        <a href="finalizar.php?id=<?= (int)$v['id'] ?>" class="btn">Finalizar Viagem</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
