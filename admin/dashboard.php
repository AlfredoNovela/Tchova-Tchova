<?php
session_start();

// Verifica se é admin
if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "admin") {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Painel do Administrador - Tchova-Tchova</title>
    <link rel="stylesheet" href="../assets/style-dashboard.css">
</head>
<body>

<div class="header">
    <img src="../assets/logo.png" class="logo">
    <h1>Tchova-Tchova — Administração</h1>
</div>

<div class="container">

    <a class="card" href="cadastrar_passageiro.php">
        <h2>➕ Cadastrar Passageiro</h2>
        <p>Registar passageiros com foto e BI.</p>
    </a>

    <a class="card" href="cadastrar_motorista.php">
        <h2>🚗 Cadastrar Motorista</h2>
        <p>Adicionar motoristas, carta e veículo.</p>
    </a>

    <a class="card" href="listar_usuarios.php">
        <h2>🔨 Gerir / Banir Usuários</h2>
        <p>Controlar quem pode usar a plataforma.</p>
    </a>

    <a class="card danger" href="../logout.php">
        <h2>↩ Sair</h2>
        <p>Terminar sessão.</p>
    </a>

</div>

</body>
</html>
