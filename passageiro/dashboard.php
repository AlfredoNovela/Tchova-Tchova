<?php
session_start();

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "passageiro") {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Passageiro - Tchova</title>
    <link rel="stylesheet" href="../assets/style-dashboard.css">
</head>
<body>

<div class="header">
    <img src="../assets/logo.png" class="logo">
    <h1>Bem-vindo, Passageiro</h1>
</div>

<div class="container">

    <a class="card" href="solicitar_viagem.php">
        <h2>📍 Solicitar Viagem</h2>
        <p>Pedir um motorista imediatamente.</p>
    </a>

    <a class="card" href="historico.php">
        <h2>🕒 Histórico de Viagens</h2>
        <p>Veja viagens passadas.</p>
    </a>

    <a class="card danger" href="../logout.php">
        <h2>↩ Sair</h2>
        <p>Terminar sessão.</p>
    </a>

</div>

</body>
</html>
