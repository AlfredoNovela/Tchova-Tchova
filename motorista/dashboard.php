<?php
session_start();
require "../config.php";

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "motorista") {
    header("Location: ../index.php");
    exit;
}

$id_motorista = $_SESSION["id"];

// =========================
// Buscar informações do motorista + usuário
// =========================
$stmt = $pdo->prepare("
    SELECT 
        usuarios.nome,
        motoristas.foto_veiculo,
        motoristas.marca,
        motoristas.modelo,
        motoristas.matricula,
        COALESCE(motoristas.lat,-25.93502870038931) AS lat,
        COALESCE(motoristas.lng,32.5480006173226) AS lng,
        COALESCE(motoristas.online,0) AS online,
        COALESCE(motoristas.creditos,0.00) AS creditos
    FROM usuarios
    LEFT JOIN motoristas ON usuarios.id = motoristas.id
    WHERE usuarios.id = ?
");
$stmt->execute([$id_motorista]);
$motorista = $stmt->fetch(PDO::FETCH_ASSOC);

// Caso não exista registro em motoristas
if (!$motorista) {
    $motorista = [
        'nome' => 'Motorista',
        'foto_veiculo' => '',
        'marca' => '-',
        'modelo' => '-',
        'matricula' => '-',
        'lat' => -25.965,
        'lng' => 32.583,
        'online' => 0,
        'creditos' => 0.00
    ];
}

// Define a imagem do carro
$fotoVeiculo = !empty($motorista['foto_veiculo']) ? $motorista['foto_veiculo'] : 'car-placeholder.png';

// =========================
// Funções de estatísticas
// =========================
function viagensConcluidasHoje($pdo, $id_motorista) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM viagens 
        WHERE id_motorista = ? 
          AND estado = 'concluida' 
          AND DATE(data_hora) = CURDATE()
    ");
    $stmt->execute([$id_motorista]);
    return (int)$stmt->fetchColumn();
}

function ganhosHoje($pdo, $id_motorista) {
    $stmt = $pdo->prepare("
        SELECT SUM(valor) 
        FROM viagens 
        WHERE id_motorista = ? 
          AND estado = 'concluida' 
          AND DATE(data_hora) = CURDATE()
    ");
    $stmt->execute([$id_motorista]);
    return (float)$stmt->fetchColumn() ?? 0.00;
}

// Estatísticas atuais
$viagensHoje = viagensConcluidasHoje($pdo, $id_motorista);
$ganhosHoje = ganhosHoje($pdo, $id_motorista);
$creditos = (float)$motorista['creditos'];

// =========================
// Controle botão Online
// =========================
$btnDisabled = $creditos <= 16 ? "disabled" : "";
$btnClass = $motorista['online'] ? "online-true" : "online-false";
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Painel do Motorista</title>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<link rel="stylesheet" href="../assets/css/dashboard.css">
<style>
.profile-img img.car-photo { width:100%; max-width:180px; height:auto; border-radius:6px; display:block; margin:0 auto; }
.map-container { height:320px; border-radius:6px; overflow:hidden; margin-bottom:1rem; }
.online-btn { display:inline-block; padding:8px 14px; border-radius:6px; cursor:pointer; border:none; font-weight:600; }
.online-true { background:#2ecc71; color:#fff; }
.online-false { background:#e74c3c; color:#fff; }
.stats { display:flex; gap:1rem; flex-wrap:wrap; margin-top:1rem; }
.card { background:#fff; padding:12px; border-radius:8px; box-shadow:0 1px 4px rgba(0,0,0,.08); min-width:120px; text-align:center; }
.header-right { display:flex; gap:1rem; align-items:center; }
p.credit-warning { color:red; margin-top:5px; font-weight:600; }
</style>
</head>
<body>

<div class="sidebar">
    <div class="brand">
        <img src="../assets/img/logo.png" class="brand-logo" alt="Logo">
        <h2>Tchova-Tchova</h2>
    </div>

    <div class="profile-box">
        <div class="profile-img">
            <img src="../assets/uploads/veiculos/<?php echo htmlspecialchars($fotoVeiculo); ?>" class="car-photo" alt="Veículo">
        </div>
        <h3><?php echo htmlspecialchars($motorista['marca'] . " " . $motorista['modelo']); ?></h3>
        <p>Matrícula: <?php echo htmlspecialchars($motorista['matricula']); ?></p>
    </div>

    <nav>
        <a href="novas_viagens.php">🚗 Procurar Viagens</a>
        <a href="minhas_viagens.php">📌 Minhas Viagens</a>
        <a class="logout" href="../logout.php">Sair</a>
    </nav>
</div>

<div class="main">
    <header style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1>Bem-vindo, <?php echo htmlspecialchars($motorista['nome']); ?>!</h1>
            <p>Mantenha-se pronto para novas viagens.</p>
        </div>
        <div class="header-right">
            <button id="toggleOnlineBtn" class="online-btn <?php echo $btnClass ?>" <?php echo $btnDisabled; ?>>
                <?php echo $motorista['online'] ? 'Online' : 'Offline'; ?>
            </button>
        </div>
    </header>

    <?php if($creditos <= 16): ?>
        <p class="credit-warning">Você precisa de pelo menos 16 créditos para ficar online. Créditos atuais: <?= number_format($creditos,2) ?></p>
    <?php endif; ?>

    <div style="margin-top:16px;">
        <div class="map-container" id="map"></div>

        <div class="stats">
            <div class="card">
                <h4>Ganhos Hoje</h4>
                <p id="ganhosHoje"><?= number_format($ganhosHoje, 2) ?> MT</p>
            </div>

            <div class="card">
                <h4>Viagens Concluídas (Hoje)</h4>
                <p id="viagensConcluidas"><?= $viagensHoje ?></p>
            </div>

            <div class="card">
                <h4>Créditos</h4>
                <p id="creditos"><?= number_format($creditos, 2) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Dados iniciais
const motoristaId = <?php echo json_encode($id_motorista); ?>;
let currentLat = <?php echo json_encode((float)$motorista['lat']); ?>;
let currentLng = <?php echo json_encode((float)$motorista['lng']); ?>;
let map, marker;

// Inicializar mapa
function initMap() {
    map = L.map('map').setView([currentLat, currentLng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    marker = L.marker([currentLat, currentLng]).addTo(map)
        .bindPopup("Você está aqui").openPopup();
}

// Atualizar marcador
function updateMarker(lat, lng) {
    currentLat = lat;
    currentLng = lng;
    marker.setLatLng([lat, lng]);
    map.panTo([lat, lng]);
}

// Buscar localização (AJAX)
async function fetchLocation() {
    try {
        const res = await fetch('get_location.php?id=' + motoristaId);
        if (!res.ok) throw new Error('Erro ao buscar localização');
        const data = await res.json();
        if (data.lat && data.lng) updateMarker(parseFloat(data.lat), parseFloat(data.lng));
    } catch (err) {
        console.error(err);
    }
}

// Buscar estatísticas (AJAX)
async function fetchStats() {
    try {
        const res = await fetch('fetch_stats.php?id=' + motoristaId);
        if (!res.ok) throw new Error('Erro ao buscar estatísticas');
        const data = await res.json();
        document.getElementById('ganhosHoje').textContent = (data.ganhosHoje ?? '0.00') + ' MT';
        document.getElementById('viagensConcluidas').textContent = (data.viagensConcluidas ?? '0');
        document.getElementById('creditos').textContent = (data.creditos ?? '0.00');
    } catch (err) {
        console.error(err);
    }
}

// Toggle Online/Offline
document.addEventListener('DOMContentLoaded', () => {
    initMap();
    fetchStats();
    setTimeout(() => map.invalidateSize(), 300);

    setInterval(fetchLocation, 10000);
    setInterval(fetchStats, 15000);

    const btn = document.getElementById('toggleOnlineBtn');
    btn.addEventListener('click', async () => {
        try {
            const res = await fetch('toggle_online.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: motoristaId })
            });
            const data = await res.json();
            if (data.success) {
                btn.textContent = data.online ? 'Online' : 'Offline';
                btn.classList.toggle('online-true', data.online);
                btn.classList.toggle('online-false', !data.online);
            } else {
                alert('Erro: ' + (data.message || 'Não foi possível alterar o estado'));
            }
        } catch (err) {
            console.error(err);
            alert('Erro na requisição');
        }
    });
});
</script>

</body>
</html>
