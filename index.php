<?php
session_start();
require "config.php";

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $senha = $_POST["senha"];

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

<link rel="stylesheet" href="assets/style.css">

<div class="login-container">
    <h2>Tchova-Tchova</h2>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="senha" placeholder="Senha" required><br>
        <button type="submit">Entrar</button>
    </form>
    <p class="msg"><?= $msg ?></p>
</div>
