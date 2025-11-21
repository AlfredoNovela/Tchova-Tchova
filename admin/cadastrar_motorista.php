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
    <title>Cadastrar Motorista - Tchova-Tchova</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
    /* Ajuste para formulário não cortar */
    .form-container {
        max-width: 600px;
        margin: 0 auto;
        width: 100%;
    }
    .form-container form {
        display: flex;
        flex-direction: column;
        gap: 15px;
        padding: 20px;
        border-radius: 15px;
        background: #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .form-container form input,
    .form-container form button,
    .form-container form label {
        width: 100%;
        font-size: 15px;
    }
    .form-container form button {
        background: #1B4FA0;
        color: #fff;
        border: none;
        padding: 12px;
        border-radius: 10px;
        cursor: pointer;
        transition: 0.3s;
    }
    .form-container form button:hover {
        background: #2A66CA;
    }
    .msg {
        margin-top: 15px;
        text-align: center;
        font-weight: bold;
        color: green;
    }
    </style>
</head>
<body>

<!-- ===== SIDEBAR ===== -->
<div class="sidebar">
    <div class="brand">
        <a href="dashboard.php"> <!-- Torna a logo clicável -->
            <img src="../assets/img/logo.png" class="brand-logo" alt="dashboard">
        </a>
        <h2>Tchova-Tchova</h2>
    </div>

    <div class="profile-box">
        <div class="profile-img">
            <img src="../assets/img/admin.png" alt="Administrador" class="car-photo">
        </div>
        <h3>Administrador</h3>
        <p>Bem-vindo!</p>
    </div>

    <nav>
        <a href="cadastrar_passageiro.php">➕ Cadastrar Passageiro</a>
        <a href="cadastrar_motorista.php">🚗 Cadastrar Motorista</a>
        <a href="listar_usuarios.php">🔨 Gerir / Banir Usuários</a>
        <a href="../logout.php" class="logout">↩ Sair</a>
    </nav>
</div>


<!-- ===== MAIN CONTENT ===== -->
<div class="main">
    <header>
        <h1>Cadastrar Motorista</h1>
        <p>Preencha os dados do motorista e faça upload dos documentos e fotos do veículo.</p>
    </header>

    <div class="form-container">
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="nome" placeholder="Nome completo" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <input type="text" name="marca" placeholder="Marca do veículo" required>
            <input type="text" name="modelo" placeholder="Modelo do veículo" required>
            <input type="text" name="matricula" placeholder="Matrícula" required>

            <label>Carta de Condução:</label>
            <input type="file" name="carta" accept="image/*" required>

            <label>Foto do Veículo:</label>
            <input type="file" name="foto_veiculo" accept="image/*" required>

            <button type="submit">Cadastrar</button>
        </form>

        <?php if($msg): ?>
            <p class="msg"><?= $msg ?></p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
