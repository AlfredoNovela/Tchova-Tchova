<?php
session_start();
require "../config.php";

if(!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "passageiro"){
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Aguardando Motorista</title>
<link rel="stylesheet" href="../assets/css/dashboard.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css"/>
<style>
/* ===== Dashboard style ===== */
.main {
    margin-left: 220px; /* largura da sidebar */
    padding: 20px 20px; /* padding horizontal reduzido para mais espaço */
    width: calc(100% - 220px); /* ocupa toda largura restante */
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

/* Mapa maior e mais largo */
#map {
    width: calc(100% + 40px); /* expande além do padding da main */
    margin-left: -20px;        /* centraliza o mapa */
    height: 700px;             /* altura aumentada */
    border-radius: 12px;
    margin-top: 12px;
}

/* Botões */
.btn { padding: 10px 16px; background:#1B4FA0; color:#fff; border:none; border-radius:8px; cursor:pointer; text-decoration:none; }
.btn:hover { background:#2A66CA; }
.btn.ghost { background:#ccc; color:#333; margin-left:10px; }
.btn.ghost:hover { background:#bbb; }

#infoMotorista { font-weight: 500; font-size: 16px; margin-bottom: 10px; }
</style>
</head>
<body>
<div class="sidebar">
    <div class="brand">
        <img src="../assets/img/logo.png" class="brand-logo" alt="Logo">
        <h2>Tchova</h2>
    </div>
    <div class="profile-box">
        <h3>Passageiro</h3>
    </div>
    <nav>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="historico.php">🕒 Histórico</a>
        <a href="../logout.php" class="logout">↩ Sair</a>
    </nav>
</div>

<div class="main">
    <header>
        <h1>Aguardando Motorista</h1>
        <p>Verifique a localização do motorista no mapa e acompanhe a viagem.</p>
    </header>

    <div class="card">
        <p id="infoMotorista">Procurando motorista... aguarde.</p>
        <div id="map"></div>
        <div style="margin-top:12px; display:flex; gap:8px;">
            <button id="btnCancelar" class="btn">Cancelar Viagem</button>
            <a class="btn ghost" href="dashboard.php">← Voltar</a>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
<script>
let map, origemMarker=null, destinoMarker=null, motoristaMarker=null, routeControl=null, viagemAtual=null;

function initMap(){
    map = L.map('map'); 
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{ maxZoom:19 }).addTo(map);

    if(navigator.geolocation){
        navigator.geolocation.getCurrentPosition(async pos=>{
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            map.setView([lat,lng],14);

            origemMarker = L.marker([lat,lng], {draggable:true}).addTo(map)
                .bindPopup("Sua localização").openPopup();

            origemMarker.on('dragend', e=>{
                const p = e.target.getLatLng();
                e.target.setPopupContent("Sua localização atualizada").openPopup();
            });

        }, err=>{
            const fallbackLat = -25.870336;
            const fallbackLng = 32.6074368;
            map.setView([fallbackLat,fallbackLng],13);
            origemMarker = L.marker([fallbackLat,fallbackLng], {draggable:true}).addTo(map)
                .bindPopup("Localização padrão").openPopup();
        }, { enableHighAccuracy:true, timeout:5000 });
    } else {
        const fallbackLat = -25.870336;
        const fallbackLng = 32.6074368;
        map.setView([fallbackLat,fallbackLng],13);
        origemMarker = L.marker([fallbackLat,fallbackLng], {draggable:true}).addTo(map)
            .bindPopup("Localização padrão").openPopup();
    }
}

function createMarker(lat,lng,text,icon=null){
    let opt = {};
    if(icon) opt.icon = icon;
    return L.marker([lat,lng],opt).addTo(map).bindPopup(text).openPopup();
}

async function atualizar(){
    try{
        const res = await fetch('get_viagem_atual.php', { method:'POST' });
        const data = await res.json();
        if(!data.ok) return;
        const v = data.viagem;
        viagemAtual = v;
        if(!v) return;

        if(!destinoMarker && v.lat_destino && v.lng_destino){
            destinoMarker = createMarker(v.lat_destino,v.lng_destino,'Destino');
            if(origemMarker) map.fitBounds([origemMarker.getLatLng(), destinoMarker.getLatLng()], {padding:[30,30]});
        }

        if(v.estado==='aceita' || v.estado==='andamento'){
            document.getElementById('infoMotorista').innerText = 
                `Motorista: ${v.nome_motorista||'-'} ${v.marca?(' | '+v.marca+' '+v.modelo+' ('+v.matricula+')'):''}`;

            if(!motoristaMarker && v.lat_motorista && v.lng_motorista){
                motoristaMarker = createMarker(v.lat_motorista,v.lng_motorista,'Motorista',
                    L.icon({iconUrl:'../assets/img/car.png', iconSize:[40,40]}));
            }

            if(routeControl) routeControl.getPlan().setWaypoints([]);
            if(motoristaMarker && origemMarker && destinoMarker){
                routeControl = L.Routing.control({
                    waypoints:[motoristaMarker.getLatLng(),origemMarker.getLatLng(),destinoMarker.getLatLng()],
                    routeWhileDragging:false, draggableWaypoints:false, addWaypoints:false
                }).addTo(map);
            }
        }

        if(v.estado==='concluida') document.getElementById('infoMotorista').innerText = 'Viagem concluída';
        if(v.estado==='cancelada') document.getElementById('infoMotorista').innerText = 'Viagem cancelada';

    }catch(e){ console.error(e); }
}

document.addEventListener('DOMContentLoaded',function(){
    initMap();
    atualizar();
    setInterval(atualizar,3500);

    document.getElementById('btnCancelar').addEventListener('click',async function(){
        if(!viagemAtual) return alert('Viagem inválida');
        if(!confirm('Deseja realmente cancelar a viagem?')) return;

        try{
            const form = new FormData();
            form.append('id_viagem',viagemAtual.id);

            const res = await fetch('cancelar_viagem.php',{method:'POST', body:form});
            const data = await res.json();
            if(data.ok){
                alert('Viagem cancelada!');
                window.location.href='dashboard.php';
            } else {
                alert('Erro: '+(data.erro||'Falha ao cancelar'));
            }
        } catch(err){
            console.error(err);
            alert('Erro na comunicação');
        }
    });
});
</script>
</body>
</html>
