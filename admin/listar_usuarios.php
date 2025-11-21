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
    <title>Gerir Usuários - Tchova-Tchova</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead {
            background: #1B4FA0;
            color: #fff;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        tr:hover {
            background: #f1f1f1;
        }
        .action-btn {
            padding: 6px 12px;
            border-radius: 8px;
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
        }
        .banir {
            background: #E63946;
        }
        .banir:hover {
            background: #FF5A5A;
        }
        .desbanir {
            background: #1B4FA0;
        }
        .desbanir:hover {
            background: #4A83E8;
        }
        .table-card {
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow-x: auto;
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
        <h1>Gerir Usuários</h1>
        <p>Banir ou desbanir usuários da plataforma.</p>
    </header>

    <div class="cards table-card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Tipo</th>
                    <th>Banido</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($usuarios as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= $u['nome'] ?></td>
                    <td><?= $u['email'] ?></td>
                    <td><?= $u['tipo'] ?></td>
                    <td><?= $u['banido'] ? "Sim" : "Não" ?></td>
                    <td>
                        <?php if(!$u['banido']): ?>
                            <a class="action-btn banir" href="?banir=<?= $u['id'] ?>">Banir</a>
                        <?php else: ?>
                            <a class="action-btn desbanir" href="?desbanir=<?= $u['id'] ?>">Desbanir</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>
        <a href="dashboard.php" class="action-btn desbanir">← Voltar</a>
    </div>
</div>

</body>
</html>
