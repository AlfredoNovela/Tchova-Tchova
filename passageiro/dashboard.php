<?php
session_start();
require "../config.php";

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "passageiro") {
    header("Location: ../index.php");
    exit;
}

// Buscar nome do passageiro
$stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['id']]);
$passageiro = $stmt->fetch();
$nome = $passageiro ? $passageiro['nome'] : "Passageiro";
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Passageiro - Dashboard</title>
<link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
<div class="sidebar">
    <div class="brand">
        <a href="dashboard.php"> <!-- Torna a logo clicável -->
            <img src="../assets/img/logo.png" class="brand-logo" alt="dashboard">
        </a>
        <h2>Tchova-Tchova</h2>
    </div>

    <div class="profile-box">
        <div class="profile-img">
            <img src="../assets/img/user.png" alt="Passageiro" class="car-photo">
        </div>
        <h3>Passageiro</h3>
        
    </div>

    <nav>
        <a href="solicitar_viagem.php">📍 Solicitar Viagem</a>
        <a href="historico.php">🕒 Histórico de Viagens</a>
        <a href="../logout.php" class="logout">↩ Sair</a>
    </nav>
</div>


<div class="main">
    <header>
        <h1>Olá, <?= htmlspecialchars($nome) ?></h1>
        <p>Bem-vindo ao Tchova</p>
    </header>
    <div class="cards">
        <a href="solicitar_viagem.php" class="card">
            <h3>📍 Solicitar Viagem</h3>
            <p>Pedir um motorista imediatamente.</p>
        </a>

        <a href="historico.php" class="card">
            <h3>🕒 Histórico de Viagens</h3>
            <p>Veja suas viagens passadas.</p>
        </a>

        <a href="../logout.php" class="card logout">
            <h3>↩ Sair</h3>
            <p>Terminar sessão.</p>
        </a>
    </div>
</div>
</body>
</html>
