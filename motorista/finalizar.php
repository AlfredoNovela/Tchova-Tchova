<?php
session_start();
require "../config.php";

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "motorista") {
    header("Location: ../index.php");
    exit;
}

$id_viagem = intval($_GET["id"]);

$sql = $conn->query("SELECT * FROM viagens WHERE id=$id_viagem");
if ($sql->num_rows == 0) {
    die("Viagem inexistente.");
}

$viagem = $sql->fetch_assoc();

$conn->query("UPDATE viagens SET estado='finalizada' WHERE id=$id_viagem");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

<div class="popup">
    <h2>Viagem Finalizada</h2>
    <p>Valor da viagem: <b><?= number_format($viagem['valor'], 2, ',', '.') ?> MT</b></p>
    <a href="minhas_viagens.php" class="btn">Voltar</a>
</div>

</body>
</html>
