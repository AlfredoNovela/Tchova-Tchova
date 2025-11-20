<?php
session_start();
require "../config.php";

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "admin") {
    header("Location: ../index.php");
    exit;
}

// Banir ou desbanir
if (isset($_GET['banir'])) {
    $id = $_GET['banir'];
    $pdo->prepare("UPDATE usuarios SET banido=1 WHERE id=?")->execute([$id]);
}
if (isset($_GET['desbanir'])) {
    $id = $_GET['desbanir'];
    $pdo->prepare("UPDATE usuarios SET banido=0 WHERE id=?")->execute([$id]);
}

// Buscar todos usuários
$usuarios = $pdo->query("SELECT * FROM usuarios")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Listar Usuários</title>
    <link rel="stylesheet" href="../assets/style-dashboard.css">
</head>
<body>
<div class="header">
    <img src="../assets/logo.png" class="logo">
    <h1>Gerir Usuários</h1>
</div>

<div class="container">
    <table border="1" style="width:100%; background:white; border-collapse:collapse; text-align:center;">
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Tipo</th>
            <th>Banido</th>
            <th>Ações</th>
        </tr>
        <?php foreach($usuarios as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= $u['nome'] ?></td>
                <td><?= $u['email'] ?></td>
                <td><?= $u['tipo'] ?></td>
                <td><?= $u['banido'] ? "Sim" : "Não" ?></td>
                <td>
                    <?php if(!$u['banido']): ?>
                        <a href="?banir=<?= $u['id'] ?>">Banir</a>
                    <?php else: ?>
                        <a href="?desbanir=<?= $u['id'] ?>">Desbanir</a>
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
