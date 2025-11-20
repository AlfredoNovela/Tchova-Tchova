<?php
session_start();
require "../config.php";

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "admin") {
    header("Location: ../index.php");
    exit;
}

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);
    $marca = $_POST["marca"];
    $modelo = $_POST["modelo"];
    $matricula = $_POST["matricula"];

    $carta = $_FILES["carta"]["name"];
    $foto_veiculo = $_FILES["foto_veiculo"]["name"];

    move_uploaded_file($_FILES["carta"]["tmp_name"], "../uploads/".$carta);
    move_uploaded_file($_FILES["foto_veiculo"]["tmp_name"], "../uploads/".$foto_veiculo);

    $stmt = $pdo->prepare("INSERT INTO usuarios (nome,email,senha,tipo) VALUES (?,?,?,?)");
    $stmt->execute([$nome,$email,$senha,"motorista"]);
    $id_user = $pdo->lastInsertId();

    $stmt2 = $pdo->prepare("INSERT INTO motoristas (id,carta_conducao,foto_veiculo,marca,modelo,matricula) VALUES (?,?,?,?,?,?)");
    $stmt2->execute([$id_user,$carta,$foto_veiculo,$marca,$modelo,$matricula]);

    $msg = "Motorista cadastrado com sucesso!";
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Motorista</title>
    <link rel="stylesheet" href="../assets/style-dashboard.css">
</head>
<body>
<div class="header">
    <img src="../assets/logo.png" class="logo">
    <h1>Cadastrar Motorista</h1>
</div>

<div class="container">
    <form method="POST" enctype="multipart/form-data" class="card">
        <input type="text" name="nome" placeholder="Nome completo" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="senha" placeholder="Senha" required><br>
        <input type="text" name="marca" placeholder="Marca do veículo" required><br>
        <input type="text" name="modelo" placeholder="Modelo do veículo" required><br>
        <input type="text" name="matricula" placeholder="Matrícula" required><br>
        <label>Carta de Condução:</label><br>
        <input type="file" name="carta" accept="image/*" required><br>
        <label>Foto do Veículo:</label><br>
        <input type="file" name="foto_veiculo" accept="image/*" required><br><br>
        <button type="submit">Cadastrar</button>
    </form>
    <p class="msg"><?= $msg ?></p>
</div>
</body>
</html>
