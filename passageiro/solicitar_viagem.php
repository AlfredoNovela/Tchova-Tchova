<?php
session_start();
require "../config.php";

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "passageiro") {
    header("Location: ../index.php");
    exit;
}

$msg = '';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Solicitar Viagem</title>
<link rel="stylesheet" href="../assets/css/dashboard.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css"/>
<style>
/* ===== Layout do dashboard ===== */
.main {
    margin-left: 220px; /* largura da sidebar */
    padding: 20px 40px;
}
header h1 { font-size: 28px; margin-bottom: 6px; }
header p { font-size: 16px; color: #555; margin-bottom: 20px; }

.card {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

/* ===== Formulário ===== */
form label { display: block; margin-top: 10px; font-weight: 500; color: #333; }
form input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; margin-top: 4px; }
form .btn { margin-top: 15px; padding: 10px 16px; background:#1B4FA0; color:#fff; border:none; border-radius:8px; cursor:pointer; text-decoration:none; }
form .btn:hover { background:#2A66CA; }
form .btn.ghost { background:#ccc; color:#333; margin-left:10px; }
form .btn.ghost:hover { background:#bbb; }

/* ===== Mapa ===== */
#map {
    width: 100%;
    height: 600px; /* Aumentado para melhor visualização */
    border-radius: 12px;
}
</style>
</head>
<body>
<div class="sidebar">
    <div class="brand">
        <a href="dashboard.php"> <!-- Torna a logo clicável -->
            <img src="../assets/img/logo.png" class="brand-logo" alt="dashboard">
        </a>
        <h2>Tchova-Tchova</h2>
    </div>

    <div class="profile-box">
        <div class="profile-img">
            <img src="../assets/img/user.png" alt="Passageiro" class="car-photo">
        </div>
        <h3>Passageiro</h3>
        
    </div>

    <nav>
        <a href="solicitar_viagem.php">📍 Solicitar Viagem</a>
        <a href="historico.php">🕒 Histórico de Viagens</a>
        <a href="../logout.php" class="logout">↩ Sair</a>
    </nav>
</div>

<div class="main">
    <header>
        <h1>Solicitar Viagem</h1>
        <p>Defina origem e destino no mapa e solicite seu motorista.</p>
    </header>

    <!-- Formulário -->
    <div class="card">
        <form id="viagemForm">
            <label>Origem</label>
            <input type="text" id="origem" name="origem" placeholder="Origem" readonly required>

            <label>Destino</label>
            <input type="text" id="destino" name="destino" placeholder="Destino" readonly required>

            <input type="hidden" id="lat_origem" name="lat_origem">
            <input type="hidden" id="lng_origem" name="lng_origem">
            <input type="hidden" id="lat_destino" name="lat_destino">
            <input type="hidden" id="lng_destino" name="lng_destino">

            <button type="submit" id="btnSolicitar" class="btn" disabled>Solicitar Corrida</button>
            

            <p id="info" class="small-muted" style="margin-top:8px"></p>
            <p id="msg" style="color:green; font-weight:700; margin-top:8px;"><?= htmlspecialchars($msg) ?></p>
        </form>
    </div>

    <!-- Mapa -->
    <div id="map"></div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
<script>
// --- Script do mapa e solicitação ---
let map, origemMarker=null, destinoMarker=null, routeControl=null;

async function getEndereco(lat, lng){
    try{
        const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
        const data = await res.json();
        return data.display_name || `${lat}, ${lng}`;
    } catch { return `${lat}, ${lng}`; }
}

function initMap(){
    map = L.map('map').setView([-25.965,32.583], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{ maxZoom:19 }).addTo(map);

    if(navigator.geolocation){
        navigator.geolocation.getCurrentPosition(async pos=>{
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            map.setView([lat,lng],14);

            const endereco = await getEndereco(lat,lng);
            origemMarker = L.marker([lat,lng], {draggable:true}).addTo(map)
                .bindPopup(endereco).openPopup();

            document.getElementById('lat_origem').value = lat;
            document.getElementById('lng_origem').value = lng;
            document.getElementById('origem').value = endereco;

            origemMarker.on('dragend', async e=>{
                const p = e.target.getLatLng();
                const novoEndereco = await getEndereco(p.lat,p.lng);
                e.target.setPopupContent(novoEndereco).openPopup();
                document.getElementById('lat_origem').value = p.lat;
                document.getElementById('lng_origem').value = p.lng;
                document.getElementById('origem').value = novoEndereco;
                updateRoute();
            });
        }, ()=> alert('Não foi possível obter sua localização.'));
    } else { alert('Geolocalização não suportada pelo navegador.'); }

    map.on('click', async function(e){
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        const endereco = await getEndereco(lat,lng);

        document.getElementById('lat_destino').value = lat;
        document.getElementById('lng_destino').value = lng;
        document.getElementById('destino').value = endereco;

        if(destinoMarker) map.removeLayer(destinoMarker);
        destinoMarker = L.marker([lat,lng], {draggable:true}).addTo(map).bindPopup(endereco).openPopup();

        destinoMarker.on('dragend', async ev=>{
            const p = ev.target.getLatLng();
            const novoEndereco = await getEndereco(p.lat,p.lng);
            ev.target.setPopupContent(novoEndereco).openPopup();
            document.getElementById('lat_destino').value = p.lat;
            document.getElementById('lng_destino').value = p.lng;
            document.getElementById('destino').value = novoEndereco;
            updateRoute();
        });

        updateRoute();
    });
}

function calcularDist(lat1,lng1,lat2,lng2){
    const R = 6371;
    const dLat = (lat2-lat1)*Math.PI/180;
    const dLng = (lng2-lng1)*Math.PI/180;
    const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLng/2)**2;
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R*c;
}

function updateRoute(){
    if(!origemMarker || !destinoMarker) return;
    if(routeControl) map.removeControl(routeControl);

    const o = origemMarker.getLatLng();
    const d = destinoMarker.getLatLng();

    routeControl = L.Routing.control({
        waypoints:[o,d],
        lineOptions:{styles:[{color:'#1abc9c', weight:5}]},
        addWaypoints:false,
        draggableWaypoints:false,
        createMarker:()=>null
    }).addTo(map);

    const dist = calcularDist(o.lat,o.lng,d.lat,d.lng);
const tempo = Math.round(dist / 30 * 60); // mesmo cálculo de tempo

// ===== Parâmetros básicos =====
const valorBase = 50;        // taxa inicial mínima
const valorPorKm = 30;       // MZN por km
const valorPorMin = 2;       // MZN por minuto

// ===== Tarifa variável por hora do dia =====
const horaAtual = new Date().getHours();
let multiplicador = 1; // tarifa normal

if (horaAtual >= 22 || horaAtual < 6) {
    multiplicador = 1.5; // tarifa noturna 50% a mais
} else if (horaAtual >= 7 && horaAtual <= 9) {
    multiplicador = 1.2; // hora do rush manhã
} else if (horaAtual >= 17 && horaAtual <= 19) {
    multiplicador = 1.3; // hora do rush tarde
}

// ===== Cálculo final =====
const valorCalculado = (valorBase + (dist * valorPorKm) + (tempo * valorPorMin)) * multiplicador;
const valor = Math.round(Math.max(94, valorCalculado)); // garante valor mínimo

document.getElementById('info').innerText =
    `Distância: ${dist.toFixed(2)} km | Tempo: ${tempo} min | Valor: ${valor} MZN`;

}

document.addEventListener('DOMContentLoaded', function(){
    initMap();
    const btn = document.getElementById('btnSolicitar');

    const observer = new MutationObserver(()=>{
        const latO = document.getElementById('lat_origem').value;
        const latD = document.getElementById('lat_destino').value;
        btn.disabled = !(latO && latD);
    });
    observer.observe(document.getElementById('lat_origem'), { attributes:true, attributeFilter:['value'] });
    observer.observe(document.getElementById('lat_destino'), { attributes:true, attributeFilter:['value'] });

    document.getElementById('viagemForm').addEventListener('submit', async function(e){
        e.preventDefault();
        btn.disabled = true;

        if(!origemMarker || !destinoMarker){
            alert('Defina origem e destino');
            btn.disabled = false;
            return;
        }

        const latO = origemMarker.getLatLng().lat;
        const lngO = origemMarker.getLatLng().lng;
        const latD = destinoMarker.getLatLng().lat;
        const lngD = destinoMarker.getLatLng().lng;
        const enderecoO = origemMarker.getPopup().getContent();
        const enderecoD = destinoMarker.getPopup().getContent();

        const form = new FormData();
        form.append('lat_origem', latO);
        form.append('lng_origem', lngO);
        form.append('lat_destino', latD);
        form.append('lng_destino', lngD);
        form.append('origem', enderecoO);
        form.append('destino', enderecoD);

        try{
            const res = await fetch('solicitar_viagem_ajax.php', { method:'POST', body:form });
            const data = await res.json();
            if(data.ok){
                document.getElementById('msg').innerText = 'Viagem solicitada com sucesso!';
                setTimeout(()=> location.href='viagem_atual.php',800);
            } else {
                alert('Erro: ' + (data.erro || 'Falha ao solicitar'));
            }
        } catch(err){
            console.error(err);
            alert('Erro na comunicação');
        } finally { btn.disabled = false; }
    });
});
</script>
</body>
</html>
