<?php
session_start();
require "../config.php";

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "motorista") {
    header("Location: ../index.php");
    exit;
}

$id_motorista = intval($_SESSION['id']);

try {
    $stmt = $pdo->query("SELECT v.*, u.nome AS nome_passageiro 
                         FROM viagens v 
                         JOIN usuarios u ON u.id = v.id_passageiro
                         WHERE v.estado='pendente' 
                         ORDER BY v.data_hora DESC");
    $viagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    error_log("Erro ao buscar viagens: ".$e->getMessage());
    $viagens = [];
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Novas Viagens</title>
<link rel="stylesheet" href="../assets/css/dashboard.css">
<style>
/* ajuste local para novos cards */
.container { display:flex; flex-wrap:wrap; gap:15px; }
.container .card { width: 250px; }
.btn { padding:8px 12px; background:#1B4FA0; color:#fff; border:none; border-radius:6px; cursor:pointer; }
.btn:hover { background:#4A83E8; }
</style>
</head>
<body>
<div class="sidebar">
    <div class="brand">
        <img src="../assets/img/logo.png" class="brand-logo" alt="Logo">
        <h2>Tchova-Tchova</h2>
    </div>

    <nav>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="novas_viagens.php">🚗 Novas Viagens</a>
        <a href="minhas_viagens.php">📌 Minhas Viagens</a>
        <a class="logout" href="../logout.php">Sair</a>
    </nav>
</div>

<div class="main">
    <header>
        <h1>Novas Viagens</h1>
        <p>Confira as viagens pendentes para aceitar.</p>
    </header>

    <div class="container" id="listaViagens">
        <?php if(empty($viagens)): ?>
            <p>Nenhuma viagem disponível no momento.</p>
        <?php else: ?>
            <?php foreach($viagens as $v): ?>
                <div class="card" id="viagem_<?= $v['id'] ?>">
                    <p><b>Passageiro:</b> <?= htmlspecialchars($v['nome_passageiro']) ?></p>
                    <p><b>Origem:</b> <?= htmlspecialchars($v['origem']) ?></p>
                    <p><b>Destino:</b> <?= htmlspecialchars($v['destino']) ?></p>
                    <p><b>Valor:</b> <?= isset($v['valor']) ? 'MTN$ '.number_format($v['valor'],2) : '-' ?></p>
                    <button class="btn" onclick="aceitarViagem(<?= $v['id'] ?>)">Aceitar</button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
async function aceitarViagem(id) {
    if(!confirm('Deseja aceitar esta viagem?')) return;

    try {
        const form = new FormData();
        form.append('id_viagem', id);

        const res = await fetch('aceitar_viagem_ajax.php', {method:'POST', body:form});
        const data = await res.json();

        if(data.sucesso){
            window.location.href = 'viagem_ativa.php';
        } else {
            alert('Erro: ' + (data.erro || 'Falha ao aceitar'));
        }
    } catch(e){
        console.error(e);
        alert('Erro na comunicação');
    }
}

// atualização automática
setInterval(async () => {
    try{
        const res = await fetch('listar_viagens_ajax.php');
        const data = await res.json();
        if(data.ok){
            const container = document.getElementById('listaViagens');
            container.innerHTML = '';
            if(data.viagens.length === 0){
                container.innerHTML = '<p>Nenhuma viagem disponível no momento.</p>';
            } else {
                data.viagens.forEach(v => {
                    const card = document.createElement('div');
                    card.className = 'card';
                    card.id = 'viagem_'+v.id;
                    card.innerHTML = `
                        <p><b>Passageiro:</b> ${v.nome_passageiro}</p>
                        <p><b>Origem:</b> ${v.origem}</p>
                        <p><b>Destino:</b> ${v.destino}</p>
                        <p><b>Valor:</b> MTN$ ${parseFloat(v.valor).toFixed(2)}</p>
                        <button class="btn" onclick="aceitarViagem(${v.id})">Aceitar</button>
                    `;
                    container.appendChild(card);
                });
            }
        }
    }catch(e){console.error(e);}
}, 5000);
</script>
</body>
</html>
