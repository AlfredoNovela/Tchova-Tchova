<?php
session_start();
require "../config.php";

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "passageiro") {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Aguardando Motorista</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
</head>
<body>
<div class="topbar">
    <img src="../assets/img/logo.png" class="logo">
    <span class="top-title">Aguardando Motorista</span>
</div>

<div class="container">
    <div class="card">
        <p id="infoMotorista">Procurando motorista... aguarde.</p>
        <div id="map" style="height:420px;margin-top:12px"></div>
        <div style="margin-top:12px"><a class="btn ghost" href="dashboard.php">Cancelar</a></div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
<script src="../assets/js/map.js"></script>
<script>
(async function(){
    const map = await initMap('map',[-25.965,32.583],13);
    let origemMarker=null, destinoMarker=null, motoristaMarker=null, routeControl=null;

    async function atualizar(){
        try{
            const res = await fetch('viagem_atual.php',{cache:'no-store'});
            const data = await res.json();
            if(!data.viagem) return;
            const v = data.viagem;

            // cria marcadores se não existirem
            if(!origemMarker){
                origemMarker = createMarker(map, v.lat_origem, v.lng_origem).bindPopup('Origem').openPopup();
                destinoMarker = createMarker(map, v.lat_destino, v.lng_destino).bindPopup('Destino');
                map.fitBounds([origemMarker.getLatLng(), destinoMarker.getLatLng()], {padding:[30,30]});
            }

            if(v.estado === 'aceita' || v.estado === 'andamento'){
                document.getElementById('infoMotorista').innerText = `Motorista: ${v.nome_motorista || '-'} ${v.marca?(' | '+v.marca+' '+v.modelo+' ('+v.matricula+')'):''}`;
                if(!motoristaMarker && v.lat_origem){
                    motoristaMarker = createMarker(map, v.lat_origem, v.lng_origem, {icon:L.icon({iconUrl:'../assets/img/car.png', iconSize:[40,40]})}).bindPopup('Motorista');
                }
                if(routeControl) clearRoute(routeControl);
                if(motoristaMarker) routeControl = L.Routing.control({
                    waypoints: [motoristaMarker.getLatLng(), origemMarker.getLatLng(), destinoMarker.getLatLng()],
                    routeWhileDragging:false, draggableWaypoints:false, addWaypoints:false
                }).addTo(map);
            }
            if(v.estado === 'concluida' || v.estado === 'finalizada'){
                document.getElementById('infoMotorista').innerText = 'Viagem concluída';
            }
        }catch(e){ console.error(e); }
    }

    setInterval(atualizar, 3500);
    // primeira chamada imediata
    atualizar();

})();
</script>
</body>
</html>
