<?php
session_start();
require "config.php";

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $senha = trim($_POST["senha"]);

    $q = $pdo->prepare("SELECT * FROM usuarios WHERE email=?");
    $q->execute([$email]);
    $user = $q->fetch();

    if ($user && password_verify($senha, $user["senha"])) {

        if ($user["banido"] == 1) {
            $msg = "Sua conta foi banida!";
        } else {
            $_SESSION["id"] = $user["id"];
            $_SESSION["tipo"] = $user["tipo"];

            if ($user["tipo"] == "admin") header("Location: admin/dashboard.php");
            if ($user["tipo"] == "passageiro") header("Location: passageiro/dashboard.php");
            if ($user["tipo"] == "motorista") header("Location: motorista/dashboard.php");
            exit;
        }
    } else {
        $msg = "Email ou senha incorretos!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Login - Tchova-Tchova</title>
<link rel="stylesheet" href="assets/login.css">
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">

        <img src="assets/img/logo.png" class="logo">


        <h2 class="title">Tchova-Tchova</h2>
        <p class="subtitle">Entre na sua conta</p>

        <form method="POST">

            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="input-group">
                <label>Senha</label>
                <input type="password" name="senha" required>
            </div>

            <button class="btn-login" type="submit">Entrar</button>
        </form>

        <?php if ($msg != ""): ?>
            <p class="msg"><?= $msg ?></p>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
