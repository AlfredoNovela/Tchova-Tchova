<?php
session_start();
require "../config.php";

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "motorista") {
    header("Location: ../index.php");
    exit;
}

$id_motorista = $_SESSION["id"];
$viagens = $pdo->prepare("SELECT v.*, u.nome as passageiro_nome FROM viagens v JOIN usuarios u ON v.id_passageiro=u.id WHERE id_motorista=? ORDER BY data_hora DESC");
$viagens->execute([$id_motorista]);
$viagens = $viagens->fetchAll();

// Finalizar viagem
if (isset($_GET['finalizar'])) {
    $pdo->prepare("UPDATE viagens SET estado='concluida' WHERE id=?")->execute([$_GET['finalizar']]);
    header("Location: minhas_viagens.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Minhas Viagens</title>
    <link rel="stylesheet" href="../assets/style-dashboard.css">
</head>
<body>
<div class="header">
    <img src="../assets/logo.png" class="logo">
    <h1>Minhas Viagens</h1>
</div>

<div class="container">
    <table border="1" style="width:100%; background:white; border-collapse:collapse; text-align:center;">
        <tr>
            <th>ID</th>
            <th>Passageiro</th>
            <th>Origem</th>
            <th>Destino</th>
            <th>Estado</th>
            <th>Ação</th>
        </tr>
        <?php foreach($viagens as $v): ?>
            <tr>
                <td><?= $v['id'] ?></td>
                <td><?= $v['passageiro_nome'] ?></td>
                <td><?= $v['origem'] ?></td>
                <td><?= $v['destino'] ?></td>
                <td><?= $v['estado'] ?></td>
                <td>
                    <?php if($v['estado']=='aceita'): ?>
                        <a href="?finalizar=<?= $v['id'] ?>">Finalizar</a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <a href="dashboard.php">← Voltar</a>
</div>
</body>
</html>
