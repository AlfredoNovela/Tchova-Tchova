<?php
session_start();
require "../config.php";

// Só motorista pode acessar
if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "motorista") {
    header("Location: ../index.php");
    exit;
}

$id_motorista = $_SESSION["id"];

// Aceitar viagem via GET
if (isset($_GET['aceitar'])) {
    $id_viagem = intval($_GET['aceitar']);

    // Atualiza a viagem no banco
    $stmt = $pdo->prepare("UPDATE viagens SET id_motorista=?, estado='aceita' WHERE id=?");
    $stmt->execute([$id_motorista, $id_viagem]);

    // Redireciona para a tela de viagem ativa
    header("Location: viagem_ativa.php?id_viagem=$id_viagem");
    exit;
}

// Buscar viagens pendentes
$viagens = $pdo->query("
    SELECT v.*, u.nome AS passageiro_nome 
    FROM viagens v 
    JOIN usuarios u ON v.id_passageiro = u.id 
    WHERE estado='pendente'
    ORDER BY v.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
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
    <?php if (empty($viagens)): ?>
        <p>Nenhuma viagem disponível no momento.</p>
    <?php else: ?>
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
                    <td><?= htmlspecialchars($v['passageiro_nome']) ?></td>
                    <td><?= htmlspecialchars($v['origem']) ?></td>
                    <td><?= htmlspecialchars($v['destino']) ?></td>
                    <td>
                        <a href="?aceitar=<?= $v['id'] ?>" class="btn">Aceitar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
    <br>
    <a href="dashboard.php" class="btn ghost">← Voltar</a>
</div>
</body>
</html>
