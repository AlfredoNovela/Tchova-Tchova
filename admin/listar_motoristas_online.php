<?php
session_start();
require "../config.php";

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "admin") {
    header("Location: ../index.php");
    exit;
}

// Buscar motoristas online
$motoristas = $pdo->query("SELECT * FROM motoristas WHERE online=1")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Motoristas Online - Tchova-Tchova</title>
<link rel="stylesheet" href="../assets/css/dashboard.css">
<style>
    .driver-card {
        cursor: pointer;
        transition: 0.3s;
    }
    .driver-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.15);
    }
    .driver-card img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 12px;
        margin-bottom: 10px;
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
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
        animation: fadeIn 0.3s ease;
    }
    @keyframes fadeIn {
        from {opacity: 0; transform: translateY(-20px);}
        to {opacity: 1; transform: translateY(0);}
    }
    .close-btn {
        position: absolute;
        top: 15px;
        right: 15px;
        background: #E63946;
        color: #fff;
        border: none;
        padding: 6px 12px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        transition: 0.3s;
    }
    .close-btn:hover {
        background: #FF5A5A;
    }
    .modal-content img {
        width: 100%;
        height: 180px;
        object-fit: cover;
        border-radius: 12px;
        margin-bottom: 15px;
    }
    .modal-content p {
        margin: 8px 0;
    }
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

<div class="sidebar">
    <div class="brand">
        <img src="../assets/img/logo.png" class="brand-logo" alt="Logo">
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
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="listar_motoristas_online.php">🚗 Motoristas Online</a>
        <a href="cadastrar_motorista.php">➕ Cadastrar Motorista</a>
        <a href="../logout.php" class="logout">↩ Sair</a>
    </nav>
</div>

<div class="main">
    <header>
        <h1>Motoristas Online</h1>
        <p>Clique em um motorista para ver detalhes.</p>
    </header>

    <div class="cards">
        <?php foreach($motoristas as $m): ?>
        <div class="card driver-card" onclick="openModal(<?= $m['id'] ?>)">
            <img src="../uploads/<?= $m['foto_veiculo'] ?>" alt="Veículo">
            <h3><?= $m['nome'] ?></h3>
            <p><?= $m['marca'] ?> <?= $m['modelo'] ?></p>
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

        <?php if(count($motoristas)===0): ?>
            <p>Nenhum motorista online no momento.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
