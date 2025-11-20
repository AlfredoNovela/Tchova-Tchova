<?php
session_start();
require "../config.php";

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "passageiro") {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Passageiro - Dashboard</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        /* Ajustes específicos para o dashboard do passageiro */
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-top: 40px;
        }

        .card {
            display: block;
            width: 250px;
            padding: 20px;
            background-color: #1a1a1a;
            color: #f2f2f2;
            border-radius: 12px;
            text-decoration: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.3);
        }

        .card h2 {
            margin: 0 0 10px 0;
            font-size: 20px;
        }

        .card p {
            font-size: 14px;
            color: #ccc;
        }

        .card.danger {
            background-color: #c0392b;
            color: #fff;
        }

        .topbar {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            background-color: #111;
            color: #f2f2f2;
        }

        .topbar .logo {
            height: 50px;
            margin-right: 15px;
        }

        .top-title {
            font-size: 22px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="topbar">
    <img src="../assets/img/logo.png" class="logo">
    <span class="top-title">Passageiro — Tchova</span>
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
