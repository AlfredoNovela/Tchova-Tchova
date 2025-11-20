<?php
session_start();
require "../config.php";

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "passageiro") {
    header("Location: ../index.php");
    exit;
}

$id = intval($_SESSION['id']);

// Consulta usando PDO
$stmt = $pdo->prepare("SELECT * FROM viagens WHERE id_passageiro = ? ORDER BY data_hora DESC");
$stmt->execute([$id]);
$viagens = $stmt->fetchAll();

// Função para buscar motorista
function getMotorista($pdo, $id_motorista) {
    if (!$id_motorista) return '-';
    $stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
    $stmt->execute([$id_motorista]);
    $row = $stmt->fetch();
    return $row ? $row['nome'] : '-';
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Histórico de Viagens</title>
<link rel="stylesheet" href="../assets/css/dashboard.css">
<style>
body {
    background-color: #f9f9f9;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}
.topbar {
    background-color: #1abc9c;
    color: #fff;
    padding: 12px 24px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.topbar .logo {
    height: 40px;
}
.top-title {
    font-size: 20px;
    font-weight: bold;
}
.container {
    padding: 20px;
    max-width: 900px;
    margin: auto;
}
.table-box {
    background-color: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    overflow: hidden;
}
table {
    width: 100%;
    border-collapse: collapse;
}
thead {
    background-color: #1abc9c;
    color: #fff;
}
thead th {
    padding: 12px;
    text-align: left;
}
tbody tr {
    border-bottom: 1px solid #eee;
}
tbody tr:nth-child(even) {
    background-color: #f7f7f7;
}
tbody td {
    padding: 12px;
}
.btn.ghost {
    display: inline-block;
    padding: 10px 16px;
    border: 2px solid #1abc9c;
    color: #1abc9c;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: 0.3s;
}
.btn.ghost:hover {
    background-color: #1abc9c;
    color: #fff;
}
</style>
</head>
<body>
<div class="topbar">
    <img src="../assets/img/logo.png" class="logo">
    <span class="top-title">Histórico de Viagens</span>
</div>

<div class="container">
    <div class="table-box">
        <table>
            <thead>
                <tr><th>ID</th><th>Origem</th><th>Destino</th><th>Motorista</th><th>Estado</th><th>Data/Hora</th></tr>
            </thead>
            <tbody>
            <?php foreach($viagens as $v): ?>
                <tr>
                    <td><?= htmlspecialchars($v['id']) ?></td>
                    <td><?= htmlspecialchars($v['origem']) ?></td>
                    <td><?= htmlspecialchars($v['destino']) ?></td>
                    <td><?= htmlspecialchars(getMotorista($pdo, $v['id_motorista'])) ?></td>
                    <td><?= htmlspecialchars($v['estado']) ?></td>
                    <td><?= htmlspecialchars($v['data_hora']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <br>
    <a class="btn ghost" href="dashboard.php">← Voltar</a>
</div>
</body>
</html>
