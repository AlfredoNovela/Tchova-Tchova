<?php
session_start();
require "../config.php";

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "passageiro") {
    header("Location: ../index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['id']]);
$passageiro = $stmt->fetch();
$nome = $passageiro ? $passageiro['nome'] : "Passageiro";
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Viagem Atual</title>

<link rel="stylesheet" href="../assets/css/dashboard.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css"/>

<style>
:root {
    --primary-color: #1B4FA0;
    --primary-hover: #2A66CA;
    --secondary-color: #ccc;
    --secondary-hover: #bbb;
    --text-color: #444;
    --bg-card: #fff;
    --border-radius: 12px;
    --shadow-card: rgba(0,0,0,0.15);
}

body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.main {
    margin-left: 220px;
    padding: 20px 40px;
    box-sizing: border-box;
}

header h1 {
    font-size: 28px;
    margin-bottom: 6px;
}

header p {
    font-size: 16px;
    color: #555;
    margin-bottom: 20px;
}

#map {
    width: 100%;
    height: 520px;
    border-radius: var(--border-radius);
    margin-bottom: 24px;
}

.motorista-card {
    width: 100%;
    max-width: 600px;
    background: var(--bg-card);
    padding: 24px;
    border-radius: var(--border-radius);
    box-shadow: 0 6px 18px var(--shadow-card);
    margin: 0 auto 30px auto;
    text-align: center;
    display: none;
}

.motorista-card img {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 12px;
    border: 2px solid var(--primary-color);
}

.motorista-card h3 {
    margin: 6px 0;
    font-size: 20px;
    font-weight: 600;
}

.motorista-card p {
    margin: 6px 0;
    font-size: 16px;
    color: var(--text-color);
}

#infoMotorista {
    font-weight: 500;
    font-size: 18px;
    margin-bottom: 16px;
}

.btn {
    padding: 10px 16px;
    background: var(--primary-color);
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: background 0.3s ease;
}

.btn:hover {
    background: var(--primary-hover);
}

.btn.ghost {
    background: var(--secondary-color);
    color: #333;
}

.btn.ghost:hover {
    background: var(--secondary-hover);
}

@media (max-width: 700px) {
    .motorista-card {
        padding: 16px;
    }
    #map {
        height: 400px;
    }
}
</style>
</head>
<body>

<div class="sidebar">
    <div class="brand">
        <a href="dashboard.php">
            <img src="../assets/img/logo.png" class="brand-logo">
        </a>
        <h2>Tchova-Tchova</h2>
    </div>

    <div class="profile-box">
        <div class="profile-img">
            <img src="../assets/img/user.png" class="car-photo">
        </div>
        <h3><?= htmlspecialchars($nome) ?></h3>
    </div>

    <nav>
        <a href="solicitar_viagem.php">📍 Solicitar Viagem</a>
        <a href="historico.php">🕒 Histórico de Viagens</a>
        <a href="../logout.php" class="logout">↩ Sair</a>
    </nav>
</div>

<div class="main">
<header>
    <h1>Viagem Atual</h1>
    <p>Acompanhe o status da sua viagem e a localização do motorista.</p>
</header>

<div id="infoMotorista">Procurando motorista... aguarde.</div>
<div id="map"></div>

<div id="cardMotorista" class="motorista-card">
    <img id="fotoMotorista" src="../assets/img/user.png">
    <h3 id="nomeMotorista">Motorista</h3>
    <p id="carroMotorista">Carro</p>
    <p id="matriculaMotorista">Matrícula</p>
    <p id="valorViagem">Valor: --</p>
    <p id="tempoViagem">Tempo estimado: -- min</p>
    <div style="margin-top:12px; display:flex; gap:8px; justify-content:center;">
        <a class="btn ghost" href="dashboard.php">← Voltar</a>
    </div>
</div>

</div><!-- main -->

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

<script>
let map, origemMarker=null, destinoMarker=null, motoristaMarker=null, routeControl=null, viagemAtual=null;

function initMap(){
    map = L.map('map').setView([-25.965,32.583],13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{ maxZoom:19 }).addTo(map);

    if(navigator.geolocation){
        navigator.geolocation.getCurrentPosition(pos=>{
            origemMarker = L.marker([pos.coords.latitude, pos.coords.longitude], {draggable:true})
                .addTo(map).bindPopup("Sua localização").openPopup();
        });
    }
}

function clearRoute(){
    if(routeControl) map.removeControl(routeControl);
}

async function atualizar(){
    try{
        const res = await fetch('get_viagem_atual.php',{method:'POST'});
        const data = await res.json();
        if(!data.ok || !data.viagem) return;
        const v = data.viagem;
        viagemAtual = v;

        if(v.estado === 'concluida'){
            alert('Viagem concluída!');
            window.location.href = 'historico.php';
            return;
        }

        if(!destinoMarker && v.lat_destino && v.lng_destino){
            destinoMarker = L.marker([v.lat_destino, v.lng_destino]).addTo(map).bindPopup("Destino");
        }

        const card = document.getElementById('cardMotorista');

        if(v.estado === 'aceita' || v.estado === 'andamento'){
            card.style.display = 'block';
            document.getElementById('nomeMotorista').innerText = v.nome_motorista || "Motorista";
            document.getElementById('carroMotorista').innerText =
                v.marca ? `${v.marca} ${v.modelo}` : "Carro não informado";
            document.getElementById('matriculaMotorista').innerText =
                v.matricula ? `Matrícula: ${v.matricula}` : "Sem matrícula";
            if(v.foto_motorista){
                document.getElementById('fotoMotorista').src =
                    "../uploads/motoristas/" + v.foto_motorista;
            }

            document.getElementById('infoMotorista').innerText =
                v.estado === 'aceita' ? `Motorista a caminho: ${v.nome_motorista}` : `Viagem em andamento`;

            document.getElementById('valorViagem').innerText =
                `Valor: ${v.valor ? "MT " + v.valor : "--"}`;
            document.getElementById('tempoViagem').innerText =
                `Tempo estimado: ${v.tempo_estimado ? v.tempo_estimado + " min" : "--"}`;

            if(!motoristaMarker){
                motoristaMarker = L.marker([v.lat_motorista, v.lng_motorista],
                    {icon: L.icon({iconUrl:'../assets/img/car.png', iconSize:[40,40]})})
                    .addTo(map)
                    .bindPopup("Motorista");
            } else {
                motoristaMarker.setLatLng([v.lat_motorista, v.lng_motorista]);
            }

            clearRoute();
            let waypoints = [];
            if(v.estado === 'aceita'){
                waypoints = [
                    L.latLng(v.lat_motorista, v.lng_motorista),
                    L.latLng(v.lat_origem, v.lng_origem)
                ];
            } else if(v.estado === 'andamento'){
                waypoints = [
                    L.latLng(v.lat_origem, v.lng_origem),
                    L.latLng(v.lat_destino, v.lng_destino)
                ];
            }

            routeControl = L.Routing.control({
                waypoints: waypoints,
                routeWhileDragging: false,
                draggableWaypoints: false,
                addWaypoints: false
            }).addTo(map);
        }

        if(v.estado === 'cancelada'){
            document.getElementById('infoMotorista').innerText = 'Viagem cancelada';
        }

    } catch(e){ console.error(e); }
}

document.addEventListener('DOMContentLoaded',()=>{
    initMap();
    atualizar();
    setInterval(atualizar, 3500);
});
</script>

</body>
</html>
