<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Teste Mapa Leaflet</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css"/>
    <style>
        body { margin:0; padding:0; }
        #map { height: 500px; width: 100%; }
    </style>
</head>
<body>

<h2 style="text-align:center;">Teste Mapa Leaflet</h2>
<div id="map"></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
<script>
let map;
let origemMarker = null;
let destinoMarker = null;
let routeControl = null;

function initMap() {
    map = L.map('map').setView([-25.965, 32.583], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19
    }).addTo(map);

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;

            origemMarker = L.marker([lat, lng], {draggable:true}).addTo(map).bindPopup('Origem').openPopup();
            map.setView([lat, lng], 14);

            origemMarker.on('dragend', function(e) {
                const p = e.target.getLatLng();
                console.log('Nova origem:', p.lat, p.lng);
            });

        }, () => alert('Não foi possível obter localização.'));
    }

    map.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;

        if (destinoMarker) map.removeLayer(destinoMarker);
        destinoMarker = L.marker([lat, lng], {draggable:true}).addTo(map).bindPopup('Destino').openPopup();

        destinoMarker.on('dragend', function(ev){
            const p = ev.target.getLatLng();
            console.log('Novo destino:', p.lat, p.lng);
        });

        console.log('Destino clicado em:', lat, lng);
    });
}

document.addEventListener('DOMContentLoaded', initMap);
</script>

</body>
</html>
