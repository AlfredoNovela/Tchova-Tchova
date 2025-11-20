<?php
session_start();
require "../config.php";

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "motorista") {
    header("Location: ../index.php");
    exit;
}

$id_motorista = $_SESSION["id"];

// Aceitar viagem
if (isset($_GET['aceitar'])) {
    $id_viagem = $_GET['aceitar'];
    $pdo->prepare("UPDATE viagens SET id_motorista=?, estado='aceita' WHERE id=?")->execute([$id_motorista,$id_viagem]);
}

// Buscar viagens pendentes
$viagens = $pdo->query("SELECT v.*, u.nome as passageiro_nome FROM viagens v JOIN usuarios u ON v.id_passageiro=u.id WHERE estado='pendente'")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Viagens Disponíveis</title>
    <link rel="stylesheet" href="../assets/style-dashboard.css">
</head>
<body>
<div class="header">
    <img src="../assets/logo.png" class="logo">
    <h1>Viagens Disponíveis</h1>
</div>

<div class="container">
    <table border="1" style="width:100%; background:white; border-collapse:collapse; text-align:center;">
        <tr>
            <th>ID</th>
            <th>Passageiro</th>
            <th>Origem</th>
            <th>Destino</th>
            <th>Ação</th>
        </tr>
        <?php foreach($viagens as $v): ?>
            <tr>
                <td><?= $v['id'] ?></td>
                <td><?= $v['passageiro_nome'] ?></td>
                <td><?= $v['origem'] ?></td>
                <td><?= $v['destino'] ?></td>
                <td>
                    <a href="?aceitar=<?= $v['id'] ?>">Aceitar</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <a href="dashboard.php">← Voltar</a>
</div>
</body>
</html>
