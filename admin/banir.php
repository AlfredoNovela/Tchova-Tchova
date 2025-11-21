<?php
session_start();
require "../config.php";

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "admin") {
    header("Location: ../index.php");
    exit;
}

$id = $_GET["id"];

$pdo->prepare("UPDATE usuarios SET banido=1 WHERE id=?")->execute([$id]);

$msg = "Usuário banido com sucesso!";
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Banir Usuário - Tchova-Tchova</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

<!-- ===== SIDEBAR ===== -->
<div class="sidebar">
    <div class="brand">
        <img src="../assets/img/logo.png" class="brand-logo" alt="Logo">
        <h2>Tchova-Tchova</h2>
    </div>

    <div class="profile-box">
        <div class="profile-img">
            <img src="../assets/img/admin.png" alt="Administrador" class="car-photo">
        </div>
        <h3>Administrador</h3>
        <p>Bem-vindo!</p>
    </div>

    <nav>
        <a href="cadastrar_passageiro.php">➕ Cadastrar Passageiro</a>
        <a href="cadastrar_motorista.php">🚗 Cadastrar Motorista</a>
        <a href="listar_usuarios.php">🔨 Gerir / Banir Usuários</a>
        <a href="../logout.php" class="logout">↩ Sair</a>
    </nav>
</div>

<!-- ===== MAIN CONTENT ===== -->
<div class="main">
    <header>
        <h1>Banir Usuário</h1>
        <p>Status da operação:</p>
    </header>

    <div class="cards">
        <div class="card">
            <h3>🚫 Usuário Banido</h3>
            <p><?= $msg ?></p>
            <a href="listar_usuarios.php" class="button">Voltar à Lista</a>
        </div>
    </div>
</div>

</body>
</html>
