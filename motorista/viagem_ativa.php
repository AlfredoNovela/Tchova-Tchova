<?php
session_start();
require "../config.php";

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "motorista") {
    header("Location: ../index.php");
    exit;
}

$id_motorista = intval($_SESSION['id']);

// Busca a viagem aceita ou em andamento pelo motorista
$stmt = $pdo->prepare("
    SELECT v.*, u.nome AS nome_passageiro, m.marca, m.modelo, m.matricula, m.foto_veiculo 
    FROM viagens v 
    JOIN usuarios u ON u.id=v.id_passageiro
    LEFT JOIN motoristas m ON m.id=? 
    WHERE v.id_motorista=? AND v.estado IN ('aceita','andamento')
");
$stmt->execute([$id_motorista, $id_motorista]);
$viagem = $stmt->fetch();

if (!$viagem) {
    die("Nenhuma viagem ativa encontrada.");
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Viagem Ativa</title>
<link rel="stylesheet" href="../assets/css/dashboard.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css"/>
<style>
#map { width:100%; height:500px; margin-top:12px; border-radius:6px;}
.card { padding:12px; margin-bottom:12px; border:1px solid #ccc; border-radius:6px; background:#fff;}
.container { margin-top:20px; display:flex; flex-direction:column; gap:15px; }
.btn { padding:10px 16px; border:none; border-radius:6px; cursor:pointer; font-weight:500; }
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
        <h1>Viagem Ativa</h1>
        <p>Acompanhe a viagem em tempo real.</p>
    </header>

    <div class="container">
        <div class="card">
            <p><b>Passageiro:</b> <?= htmlspecialchars($viagem['nome_passageiro']) ?></p>
            <p><b>Origem:</b> <?= htmlspecialchars($viagem['origem']) ?></p>
            <p><b>Destino:</b> <?= htmlspecialchars($viagem['destino']) ?></p>
            <p><b>Veículo:</b> <?= htmlspecialchars($viagem['marca'].' '.$viagem['modelo'].' ('.$viagem['matricula'].')') ?></p>
            <?php if(!empty($viagem['foto_veiculo'])): ?>
                <p><img src="../assets/uploads/veiculos/<?= htmlspecialchars($viagem['foto_veiculo']) ?>" alt="Veículo" style="max-width:300px; border-radius:6px;"></p>
            <?php endif; ?>
            <p><b>Valor da corrida:</b> MTN$ <?= number_format($viagem['valor'],2) ?></p>

            <div id="acaoViagem">
            <?php if($viagem['estado'] === 'aceita'): ?>
                <button id="btnIniciar" class="btn" style="background:#28a745; color:#fff;">🚦 Iniciar Viagem</button>
            <?php elseif($viagem['estado'] === 'andamento'): ?>
                <button id="btnFinalizar" class="btn" style="background:#dc3545; color:#fff;">🏁 Finalizar Viagem</button>
            <?php else: ?>
                <p><b>Status:</b> <?= htmlspecialchars($viagem['estado']) ?></p>
            <?php endif; ?>
            </div>
        </div>

        <div id="map"></div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
<script>
let map, motoristaMarker=null, origemMarker=null, destinoMarker=null, routeControl=null;
let estadoViagem = "<?= $viagem['estado'] ?>";

function initMap(){
    map = L.map('map').setView([<?= $viagem['lat_origem'] ?>, <?= $viagem['lng_origem'] ?>], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{ maxZoom:19 }).addTo(map);

    origemMarker = L.marker([<?= $viagem['lat_origem'] ?>, <?= $viagem['lng_origem'] ?>]).addTo(map).bindPopup('Origem').openPopup();
    destinoMarker = L.marker([<?= $viagem['lat_destino'] ?>, <?= $viagem['lng_destino'] ?>]).addTo(map).bindPopup('Destino').openPopup();

    // Inicializa marcador do motorista na origem
    motoristaMarker = L.marker([<?= $viagem['lat_origem'] ?>, <?= $viagem['lng_origem'] ?>], {icon:L.icon({
        iconUrl:'../assets/img/car.png', iconSize:[40,40]
    })}).addTo(map).bindPopup('Você');

    atualizarRota();
}

function atualizarRota(){
    if(routeControl) map.removeControl(routeControl);
    let waypoints = [];

    if(estadoViagem === 'aceita'){
        waypoints = [motoristaMarker.getLatLng(), origemMarker.getLatLng()];
    } else if(estadoViagem === 'andamento'){
        waypoints = [origemMarker.getLatLng(), destinoMarker.getLatLng()];
    }

    routeControl = L.Routing.control({
        waypoints: waypoints,
        routeWhileDragging:false, draggableWaypoints:false, addWaypoints:false
    }).addTo(map);

    map.fitBounds(waypoints, {padding:[30,30]});
}

// Atualiza posição do motorista no servidor
function enviarPosicaoMotorista(lat,lng){
    fetch('atualizar_posicao_motorista.php',{
        method:'POST',
        body: new URLSearchParams({lat:lat,lng:lng})
    }).then(res=>res.json()).then(data=>{
        if(!data.ok) console.error('Erro ao atualizar posição:', data.erro);
    }).catch(err=>console.error(err));
}

// GPS do motorista
function startGPS(){
    if(navigator.geolocation){
        navigator.geolocation.watchPosition((pos)=>{
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            if(motoristaMarker) motoristaMarker.setLatLng([lat,lng]);
            enviarPosicaoMotorista(lat,lng);
            atualizarRota();
        }, (err)=>{
            console.error('Erro GPS:', err);
            alert('Não foi possível obter a localização do GPS.');
        }, { enableHighAccuracy:true, maximumAge:3000, timeout:5000 });
    } else {
        alert('GPS não suportado no navegador.');
    }
}

document.addEventListener('DOMContentLoaded', ()=>{
    initMap();
    startGPS();

    // Iniciar viagem
    const btnIniciar = document.getElementById('btnIniciar');
    if(btnIniciar){
        btnIniciar.onclick = async ()=>{
            if(!confirm("Deseja iniciar a viagem?")) return;
            const fd = new FormData();
            fd.append('id_viagem', <?= $viagem['id'] ?>);

            const res = await fetch('iniciar_viagem.php', {method:'POST', body:fd});
            const data = await res.json();

            if(data.ok){
                alert("Viagem iniciada!");
                estadoViagem = 'andamento';
                atualizarRota();
                location.reload(); // recarrega para mostrar botão Finalizar
            } else {
                alert("Erro: "+(data.erro||'Desconhecido'));
            }
        };
    }

    // Finalizar viagem
    const btnFinalizar = document.getElementById('btnFinalizar');
    if(btnFinalizar){
        btnFinalizar.onclick = async ()=>{
            if(!confirm("Deseja finalizar a viagem?")) return;
            const fd = new FormData();
            fd.append('id_viagem', <?= $viagem['id'] ?>);

            const res = await fetch('finalizar_viagem.php', {method:'POST', body:fd});
            const data = await res.json();

            if(data.ok){
                alert("Viagem finalizada!");
                window.location.href = "minhas_viagens.php"; // redireciona após finalizar
            } else {
                alert("Erro: "+(data.erro||'Desconhecido'));
            }
        };
    }
});
</script>
</body>
</html>
