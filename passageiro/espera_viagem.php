<?php
session_start();
require "../config.php";
if(!isset($_SESSION["id"]) || $_SESSION["tipo"]!=="passageiro"){ header("Location: ../index.php"); exit; }
$id = $_SESSION["id"];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Aguardando Motorista</title>
<link rel="stylesheet" href="../assets/style-dashboard.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css"/>
<style>#map{height:50vh; border-radius:12px;}</style>
</head>
<body>
<div class="header"><h1>Aguardando Motorista</h1></div>
<div class="container">
    <p id="infoMotorista">Nenhum motorista aceitou ainda...</p>
    <div id="map"></div>
    <a href="dashboard.php">← Voltar</a>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
<script>
let map = L.map('map').setView([-25.965,32.583],13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19}).addTo(map);
let origemMarker, destinoMarker, motoristaMarker, routeControl;

async function atualizar(){
    const res = await fetch('viagem_atual.php');
    const data = await res.json();
    if(!data.viagem) return;

    const v = data.viagem;
    if(!origemMarker){
        origemMarker = L.marker([v.lat_origem,v.lng_origem]).addTo(map).bindPopup("Origem").openPopup();
        destinoMarker = L.marker([v.lat_destino,v.lng_destino]).addTo(map).bindPopup("Destino").openPopup();
    }
    if(v.estado === 'aceita' || v.estado==='andamento'){
        document.getElementById('infoMotorista').innerText = `Motorista: ${v.nome_motorista} | ${v.marca} ${v.modelo} (${v.matricula})`;
        if(!motoristaMarker){
            motoristaMarker = L.marker([v.lat_origem,v.lng_origem],{icon:L.icon({iconUrl:'../assets/car.png',iconSize:[40,40]})}).addTo(map).bindPopup("Motorista");
        }
        if(routeControl) map.removeControl(routeControl);
        routeControl = L.Routing.control({
            waypoints:[motoristaMarker.getLatLng(), origemMarker.getLatLng(), destinoMarker.getLatLng()],
            routeWhileDragging:false, draggableWaypoints:false, addWaypoints:false
        }).addTo(map);
    }
}

setInterval(atualizar,5000);
</script>
</body>
</html>
