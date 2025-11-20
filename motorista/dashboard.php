<?php
session_start();
require "../config.php";

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "motorista") {
    header("Location: ../index.php");
    exit;
}

$id_motorista = $_SESSION["id"];
$motorista_lat = -25.965;
$motorista_lng = 32.583;
?>

<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Motorista - Dashboard</title>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />

<!-- Fonte Google -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<style>
* { box-sizing: border-box; margin:0; padding:0; font-family: 'Inter', sans-serif; }
body { background: #f4f7fa; color: #333; }
.header { display: flex; align-items: center; padding: 15px 25px; background: #1f2937; color: #f2f2f2; flex-wrap: wrap; }
.header .logo { width: 60px; height: 60px; margin-right: 15px; border-radius:12px; }
.header h1 { font-size: 1.5rem; }
.container { display: flex; flex-wrap: wrap; justify-content: space-around; padding: 20px; gap: 20px; }
.card { flex: 1 1 250px; background: #fff; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 6px 15px rgba(0,0,0,0.1); transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; text-decoration: none; color: inherit; }
.card:hover { transform: translateY(-5px); box-shadow: 0 12px 25px rgba(0,0,0,0.15); }
.card h2 { font-size: 1.3rem; margin-bottom: 8px; }
.card p { font-size: 0.9rem; color: #555; }
.card.danger { background: #ef4444; color: #fff; }
.card.danger:hover { background: #dc2626; }
#map { width: 100%; height: 60vh; margin-top: 20px; border-radius: 12px; }
#btnProcurar { margin-top: 10px; padding: 10px 20px; background: #22c55e; color: #fff; border:none; border-radius:8px; cursor:pointer; font-weight:600; }
#btnProcurar:hover { background: #16a34a; }
.popup-viagem { position: fixed; top: 20px; right: 20px; background: #fff; padding: 15px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.25); display: none; z-index: 999; width: 280px; font-size: 0.95rem; }
.popup-viagem img { width: 55px; height: 55px; border-radius: 50%; float: left; margin-right: 10px; object-fit: cover; }
.popup-viagem p { margin: 4px 0; }
.popup-viagem button { margin-top: 10px; width: 100%; padding: 8px; background: #22c55e; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
.popup-viagem button:hover { background: #16a34a; }
@media (max-width: 768px) { .header { justify-content: center; text-align: center; } .container { flex-direction: column; align-items: center; } #map { height: 50vh; } }
</style>
</head>
<body>

<div class="header">
    <img src="../assets/logo.png" class="logo">
    <h1>Bem-vindo, Motorista</h1>
</div>

<div class="container">
    <a class="card" href="minhas_viagens.php">
        <h2>📝 Minhas Viagens</h2>
        <p>Acompanhe suas viagens ativas e finalizadas.</p>
    </a>

    <a class="card danger" href="../logout.php">
        <h2>↩ Sair</h2>
        <p>Termine sua sessão com segurança.</p>
    </a>

    <div id="map"></div>
</div>

<!-- Popup viagem -->
<div class="popup-viagem" id="popup">
    <img id="passageiroFoto">
    <div>
        <p id="passageiroNome"></p>
        <p id="origemDestino"></p>
        <p id="distancia"></p>
        <button id="aceitarBtn">Aceitar Viagem</button>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

<script>
let map = L.map('map').setView([<?= $motorista_lat ?>, <?= $motorista_lng ?>], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{ maxZoom: 19 }).addTo(map);
let motoristaMarker = L.marker([<?= $motorista_lat ?>, <?= $motorista_lng ?>]).addTo(map).bindPopup('Você').openPopup();

let popup = document.getElementById("popup");
let aceitarBtn = document.getElementById("aceitarBtn");
let viagemAtualId = null;
let origemMarker, destinoMarker;
let routeControl = null;

// Buscar novas viagens a cada 5s
async function buscarViagem(){
    const res = await fetch("novas_viagens.php");
    const data = await res.json();

    if(data.viagem && viagemAtualId!==data.viagem.id){
        viagemAtualId = data.viagem.id;
        document.getElementById("passageiroFoto").src = "../uploads/" + (data.viagem.foto_perfil||"user.png");
        document.getElementById("passageiroNome").innerText = data.viagem.nome;
        document.getElementById("origemDestino").innerText = data.viagem.origem + " → " + data.viagem.destino;
        document.getElementById("distancia").innerText = "Distância: " + (data.viagem.distancia||0) + " km | Valor: R$ " + (data.viagem.valor||0) + " | Tempo: " + (data.viagem.tempo||0) + " min";
        popup.style.display = "block";

        // Remove marcadores antigos
        if(origemMarker) map.removeLayer(origemMarker);
        if(destinoMarker) map.removeLayer(destinoMarker);
        if(routeControl) map.removeControl(routeControl);

        origemMarker = L.marker([data.viagem.lat_origem, data.viagem.lng_origem]).addTo(map).bindPopup("Passageiro").openPopup();
        destinoMarker = L.marker([data.viagem.lat_destino, data.viagem.lng_destino]).addTo(map).bindPopup("Destino").openPopup();
    }
}

setInterval(buscarViagem,5000);

// Aceitar viagem
aceitarBtn.onclick = function(){
    if(!viagemAtualId) return;
    fetch("aceitar_viagem_ajax.php?id=" + viagemAtualId).then(()=>{

        popup.style.display="none";

        // Traça rota até passageiro
        if(routeControl) map.removeControl(routeControl);
        routeControl = L.Routing.control({
            waypoints: [motoristaMarker.getLatLng(), origemMarker.getLatLng(), destinoMarker.getLatLng()],
            routeWhileDragging:false,
            draggableWaypoints:false,
            addWaypoints:false
        }).addTo(map);

        alert("Viagem aceita! Siga até o passageiro.");
    });
};
</script>
</body>
</html>
