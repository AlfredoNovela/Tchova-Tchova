<?php
session_start();
require "../config.php";

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "motorista") {
    header("Location: ../index.php");
    exit;
}

$id_motorista = intval($_SESSION['id']);

try {
    // Seleciona viagens pendentes
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
.card { padding:12px; margin-bottom:12px; border:1px solid #ccc; border-radius:6px; background:#fff;}
</style>
</head>
<body>
<div class="topbar">
    <img src="../assets/img/logo.png" class="logo" alt="logo">
    <span class="top-title">Novas Viagens</span>
</div>

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

<script>
async function aceitarViagem(id) {
    if(!confirm('Deseja aceitar esta viagem?')) return;

    try {
        const form = new FormData();
        form.append('id_viagem', id);

        const res = await fetch('aceitar_viagem_ajax.php', {method:'POST', body:form});
        const data = await res.json();

        if(data.sucesso){
            // Redireciona imediatamente para a tela de viagem ativa
            window.location.href = 'viagem_ativa.php';
        } else {
            alert('Erro: ' + (data.erro || 'Falha ao aceitar'));
        }
    } catch(e){
        console.error(e);
        alert('Erro na comunicação');
    }
}

// Atualização automática a cada 5s
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
