<?php
session_start();
require "../config.php";

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "admin") {
    header("Location: ../index.php");
    exit;
}

// Buscar motoristas online
$motoristas_online = $pdo->query("SELECT * FROM motoristas WHERE online=1")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Painel do Administrador - Tchova-Tchova</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
    /* Cards dos motoristas online */
    .driver-card {
        cursor: pointer;
        transition: 0.3s;
        margin-top: 15px;
        padding: 10px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .driver-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.15);
    }
    .driver-card img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0; top: 0;
        width: 100%; height: 100%;
        background-color: rgba(0,0,0,0.6);
        justify-content: center;
        align-items: center;
    }
    .modal-content {
        background: #fff;
        border-radius: 15px;
        padding: 25px;
        width: 400px;
        max-width: 90%;
        box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        position: relative;
    }
    .close-btn {
        position: absolute;
        top: 15px; right: 15px;
        background: #E63946;
        color: #fff;
        border: none;
        padding: 6px 12px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        transition: 0.3s;
    }
    .close-btn:hover { background: #FF5A5A; }
    .modal-content img {
        width: 100%;
        height: 180px;
        object-fit: cover;
        border-radius: 12px;
        margin-bottom: 15px;
    }
    .modal-content p { margin: 8px 0; }
    </style>
    <script>
    function openModal(id) {
        document.getElementById('modal-'+id).style.display = 'flex';
    }
    function closeModal(id) {
        document.getElementById('modal-'+id).style.display = 'none';
    }
    </script>
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
        <h1>Painel do Administrador</h1>
        <p>Gerencie passageiros, motoristas e usuários da plataforma.</p>
    </header>

    <div class="cards">
        <a class="card" href="cadastrar_passageiro.php">
            <h3>➕ Cadastrar Passageiro</h3>
            <p>Registar passageiros com foto e BI.</p>
        </a>

        <a class="card" href="cadastrar_motorista.php">
            <h3>🚗 Cadastrar Motorista</h3>
            <p>Adicionar motoristas, carta e veículo.</p>
        </a>

        <a class="card" href="listar_usuarios.php">
            <h3>🔨 Gerir / Banir Usuários</h3>
            <p>Controlar quem pode usar a plataforma.</p>
        </a>

        <a class="card logout" href="../logout.php">
            <h3>↩ Sair</h3>
            <p>Terminar sessão.</p>
        </a>
    </div>

    <!-- ===== MOTORISTAS ONLINE ===== -->
    <h2 style="margin-top:30px;">Motoristas Online</h2>
    <?php if(count($motoristas_online)===0): ?>
        <p>Nenhum motorista online no momento.</p>
    <?php else: ?>
        <?php foreach($motoristas_online as $m): ?>
            <div class="driver-card" onclick="openModal(<?= $m['id'] ?>)">
                <img src="../uploads/<?= $m['foto_veiculo'] ?>" alt="Veículo">
                <p><?= $m['nome'] ?></p>
            </div>

            <!-- Modal -->
            <div class="modal" id="modal-<?= $m['id'] ?>">
                <div class="modal-content">
                    <button class="close-btn" onclick="closeModal(<?= $m['id'] ?>)">✖</button>
                    <img src="../uploads/<?= $m['foto_veiculo'] ?>" alt="Veículo">
                    <h2><?= $m['nome'] ?></h2>
                    <p><strong>Marca / Modelo:</strong> <?= $m['marca'] ?> <?= $m['modelo'] ?></p>
                    <p><strong>Matrícula:</strong> <?= $m['matricula'] ?></p>
                    <p><strong>Carta de Condução:</strong> <a href="../uploads/<?= $m['carta_conducao'] ?>" target="_blank">Ver</a></p>
                    <p><strong>Latitude / Longitude:</strong> <?= $m['lat'] ?> / <?= $m['lng'] ?></p>
                    <p><strong>Créditos:</strong> <?= number_format($m['creditos'],2,",",".") ?> MT</p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

</body>
</html>
