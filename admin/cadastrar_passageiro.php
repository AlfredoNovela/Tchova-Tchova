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

    // Upload de fotos
    $foto_perfil = $_FILES["foto_perfil"]["name"];
    $foto_bi = $_FILES["foto_bi"]["name"];

    move_uploaded_file($_FILES["foto_perfil"]["tmp_name"], "../uploads/".$foto_perfil);
    move_uploaded_file($_FILES["foto_bi"]["tmp_name"], "../uploads/".$foto_bi);

    // Inserir usuário
    $stmt = $pdo->prepare("INSERT INTO usuarios (nome,email,senha,tipo) VALUES (?,?,?,?)");
    $stmt->execute([$nome,$email,$senha,"passageiro"]);
    $id_user = $pdo->lastInsertId();

    // Inserir na tabela passageiros
    $stmt2 = $pdo->prepare("INSERT INTO passageiros (id,foto_perfil,foto_bi) VALUES (?,?,?)");
    $stmt2->execute([$id_user,$foto_perfil,$foto_bi]);

    $msg = "Passageiro cadastrado com sucesso!";
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Passageiro</title>
    <link rel="stylesheet" href="../assets/style-dashboard.css">
</head>
<body>
<div class="header">
    <img src="../assets/logo.png" class="logo">
    <h1>Cadastrar Passageiro</h1>
</div>

<div class="container">
    <form method="POST" enctype="multipart/form-data" class="card">
        <input type="text" name="nome" placeholder="Nome completo" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="senha" placeholder="Senha" required><br>
        <label>Foto do Perfil:</label><br>
        <input type="file" name="foto_perfil" accept="image/*" required><br>
        <label>Foto do BI:</label><br>
        <input type="file" name="foto_bi" accept="image/*" required><br><br>
        <button type="submit">Cadastrar</button>
    </form>
    <p class="msg"><?= $msg ?></p>
</div>
</body>
</html>
