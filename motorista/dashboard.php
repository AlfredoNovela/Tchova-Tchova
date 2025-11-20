<?php
session_start();
require "../config.php";

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "motorista") {
    header("Location: ../index.php");
    exit;
}

$id_motorista = $_SESSION["id"];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Painel do Motorista</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

<div class="topbar">
    <img src="../assets/img/logo.png" class="logo">
    <span class="top-title">Painel do Motorista</span>
</div>

<div class="container">
    <a href="novas_viagens.php" class="btn">Procurar Viagens</a>
    <a href="minhas_viagens.php" class="btn">Minhas Viagens</a>
    <a href="../logout.php" class="btn logout">Sair</a>
</div>

</body>
</html>
