<?php
session_start();
require "../config.php";

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "passageiro") {
    header("Location: ../index.php");
    exit;
}

$id_passageiro = $_SESSION["id"];
$viagens = $pdo->prepare("SELECT * FROM viagens WHERE id_passageiro=? ORDER BY data_hora DESC");
$viagens->execute([$id_passageiro]);
$viagens = $viagens->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Histórico de Viagens</title>
    <link rel="stylesheet" href="../assets/style-dashboard.css">
</head>
<body>
<div class="header">
    <img src="../assets/logo.png" class="logo">
    <h1>Histórico de Viagens</h1>
</div>

<div class="container">
    <table border="1" style="width:100%; background:white; border-collapse:collapse; text-align:center;">
        <tr>
            <th>ID</th>
            <th>Origem</th>
            <th>Destino</th>
            <th>Motorista</th>
            <th>Estado</th>
            <th>Data/Hora</th>
        </tr>
        <?php foreach($viagens as $v):
            $motorista = $v['id_motorista'] ? $pdo->query("SELECT nome FROM usuarios WHERE id=".$v['id_motorista'])->fetchColumn() : "-";
        ?>
            <tr>
                <td><?= $v['id'] ?></td>
                <td><?= $v['origem'] ?></td>
                <td><?= $v['destino'] ?></td>
                <td><?= $motorista ?></td>
                <td><?= $v['estado'] ?></td>
                <td><?= $v['data_hora'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <a href="dashboard.php">← Voltar</a>
</div>
</body>
</html>
