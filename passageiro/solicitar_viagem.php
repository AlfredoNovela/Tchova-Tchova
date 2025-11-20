<?php
session_start();
require "../config.php";

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "passageiro") {
    header("Location: ../index.php");
    exit;
}

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $origem = $_POST["origem"];
    $destino = $_POST["destino"];
    $lat_origem = $_POST["lat_origem"];
    $lng_origem = $_POST["lng_origem"];
    $lat_destino = $_POST["lat_destino"];
    $lng_destino = $_POST["lng_destino"];
    $id_passageiro = $_SESSION["id"];

    $stmt = $pdo->prepare("INSERT INTO viagens 
        (id_passageiro, origem, destino, lat_origem, lng_origem, lat_destino, lng_destino, status) 
        VALUES (?,?,?,?,?,?,?,'pendente')");
    $stmt->execute([$id_passageiro, $origem, $destino, $lat_origem, $lng_origem, $lat_destino, $lng_destino]);

    $msg = "Viagem solicitada com sucesso!";
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Solicitar Viagem</title>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

<style>
body { font-family:'Inter',sans-serif; background:#f4f7fa; margin:0; padding:0; }
.header { display:flex; align-items:center; background:#1f2937; color:#f2f2f2; padding:15px 20px; flex-wrap:wrap; }
.header .logo { width:50px; height:50px; margin-right:15px; border-radius:12px; }
.header h1 { font-size:1.3rem; }

.container { padding:20px; max-width:600px; margin:auto; }
form.card { background:#fff; padding:20px; border-radius:12px; box-shadow:0 6px 15px rgba(0,0,0,0.1); display:flex; flex-direction:column; gap:10px; }
input { padding:10px; border-radius:8px; border:1px solid #ccc; width:100%; }
button { padding:12px; background:#3b82f6; color:#fff; border:none; border-radius:8px; cursor:pointer; font-weight:600; }
button:hover { background:#2563eb; }

#map { width:100%; height:400px; margin-top:15px; border-radius:12px; }
.msg { margin-top:10px; color:green; font-weight:600; }
a { display:inline-block; margin-top:10px; color:#3b82f6; text-decoration:none; }
a:hover { text-decoration:underline; }

#info { margin-top:10px; font-weight:600; }
</style>
</head>
<body>

<div class="header">
    <img src="../assets/logo.png" class="logo">
    <h1>Solicitar Viagem</h1>
</div>

<div class="container">
<form method="POST" class="card" id="viagemForm">
    <input type="text" name="origem" id="origem" placeholder="Origem" required readonly>
    <input type="text" name="destino" id="destino" placeholder="Destino" required readonly>
    <input type="hidden" name="lat_origem" id="lat_origem">
    <input type="hidden" name="lng_origem" id="lng_origem">
    <input type="hidden" name="lat_destino" id="lat_destino">
    <input type="hidden" name="lng_destino" id="lng_destino">
    <button type="submit" id="btnSolicitar" disabled>Solicitar Corrida</button>
</form>

<p id="info"></p>
<p class="msg"><?= $msg ?></p>
<a href="dashboard.php">← Voltar</a>

<div id="map"></div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

<script>
// Inicializa mapa
let map = L.map('map').setView([-25.965, 32.583], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

let origemMarker, destinoMarker;
let routeControl;
const btn = document.getElementById('btnSolicitar');
const info = document.getElementById('info');

// Função para calcular distância entre dois pontos
function calcularDistancia(lat1,lng1,lat2,lng2){
    const R = 6371;
    const dLat = (lat2-lat1)*Math.PI/180;
    const dLng = (lng2-lng1)*Math.PI/180;
    const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLng/2)**2;
    const c = 2*Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R*c;
}

// Habilitar botão apenas se origem e destino existirem
function updateButtonState(){
    btn.disabled = !(origemMarker && destinoMarker);
}

// Atualizar rota e info
function updateRoute(){
    if(!origemMarker || !destinoMarker) return;
    if(routeControl) map.removeControl(routeControl);

    routeControl = L.Routing.control({
        waypoints: [origemMarker.getLatLng(), destinoMarker.getLatLng()],
        routeWhileDragging: true,
        draggableWaypoints: true,
        show: false,
        addWaypoints: false
    }).addTo(map);

    const o = origemMarker.getLatLng();
    const d = destinoMarker.getLatLng();
    const dist = calcularDistancia(o.lat,o.lng,d.lat,d.lng);
    const tempo = Math.round(dist/30*60); // 30 km/h
    const valor = Math.round(dist*1.2*100)/100;

    info.innerText = `Distância: ${dist.toFixed(2)} km | Tempo: ${tempo} min | Valor: R$ ${valor}`;
    document.getElementById('origem').value = "Minha Localização";
    document.getElementById('destino').value = `Destino selecionado`;
}

// Pegar localização GPS do passageiro
navigator.geolocation.getCurrentPosition(function(pos){
    let lat = pos.coords.latitude;
    let lng = pos.coords.longitude;
    document.getElementById('lat_origem').value = lat;
    document.getElementById('lng_origem').value = lng;

    origemMarker = L.marker([lat,lng], {draggable:true}).addTo(map).bindPopup("Origem").openPopup();
    map.setView([lat,lng], 14);

    origemMarker.on('dragend', function(e){
        let p = e.target.getLatLng();
        document.getElementById('lat_origem').value = p.lat;
        document.getElementById('lng_origem').value = p.lng;
        updateRoute();
    });

    updateButtonState();
}, function(err){
    alert("Não foi possível pegar a localização. Por favor, permita GPS.");
    btn.disabled = false;
});

// Selecionar destino clicando no mapa
map.on('click', function(e){
    let lat = e.latlng.lat;
    let lng = e.latlng.lng;
    document.getElementById('lat_destino').value = lat;
    document.getElementById('lng_destino').value = lng;

    if(destinoMarker) map.removeLayer(destinoMarker);
    destinoMarker = L.marker([lat,lng], {draggable:true}).addTo(map).bindPopup("Destino").openPopup();

    destinoMarker.on('dragend', function(ev){
        let p = ev.target.getLatLng();
        document.getElementById('lat_destino').value = p.lat;
        document.getElementById('lng_destino').value = p.lng;
        updateRoute();
    });

    updateRoute();
    updateButtonState();
});
</script>

</body>
</html>
