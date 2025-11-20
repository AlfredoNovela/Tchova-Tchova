<?php
session_start();
require "../config.php";
header('Content-Type: application/json');

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "passageiro") {
    echo json_encode(['ok'=>false,'erro'=>'Acesso negado']); exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok'=>false,'erro'=>'Método inválido']); exit;
}

$id_passageiro = intval($_SESSION['id']);
$origem = isset($_POST['origem']) ? trim($_POST['origem']) : 'Minha localização';
$destino = isset($_POST['destino']) ? trim($_POST['destino']) : 'Destino';
$lat_origem = isset($_POST['lat_origem']) ? floatval($_POST['lat_origem']) : null;
$lng_origem = isset($_POST['lng_origem']) ? floatval($_POST['lng_origem']) : null;
$lat_destino = isset($_POST['lat_destino']) ? floatval($_POST['lat_destino']) : null;
$lng_destino = isset($_POST['lng_destino']) ? floatval($_POST['lng_destino']) : null;

if ($lat_origem === null || $lng_origem === null || $lat_destino === null || $lng_destino === null) {
    echo json_encode(['ok'=>false,'erro'=>'Coordenadas inválidas']); exit;
}

// Função Haversine para distância em km
function haversine($lat1,$lng1,$lat2,$lng2){
    $R = 6371;
    $dLat = deg2rad($lat2-$lat1);
    $dLng = deg2rad($lng2-$lng1);
    $a = sin($dLat/2)**2 + cos(deg2rad($lat1))*cos(deg2rad($lat2))*sin($dLng/2)**2;
    $c = 2*atan2(sqrt($a), sqrt(1-$a));
    return $R*$c;
}

// Calcula distância e valor
$dist = haversine($lat_origem, $lng_origem, $lat_destino, $lng_destino);
$valor = max(94, round($dist*5,2)); // mínimo 94 MZN
$tempo_estimado = round($dist/30*60); // minutos, assumindo velocidade média 30km/h

try {
    $stmt = $pdo->prepare("INSERT INTO viagens 
        (id_passageiro, origem, destino, lat_origem, lng_origem, lat_destino, lng_destino, estado, valor, tempo_estimado, data_hora, status)
        VALUES (:id_passageiro, :origem, :destino, :lat_origem, :lng_origem, :lat_destino, :lng_destino, :estado, :valor, :tempo_estimado, NOW(), :status)");

    $stmt->execute([
        ':id_passageiro' => $id_passageiro,
        ':origem' => $origem,
        ':destino' => $destino,
        ':lat_origem' => $lat_origem,
        ':lng_origem' => $lng_origem,
        ':lat_destino' => $lat_destino,
        ':lng_destino' => $lng_destino,
        ':estado' => 'pendente',
        ':valor' => $valor,
        ':tempo_estimado' => $tempo_estimado,
        ':status' => 'pendente'
    ]);

    echo json_encode(['ok'=>true, 'id'=>$pdo->lastInsertId()]);

} catch (Exception $e){
    echo json_encode(['ok'=>false,'erro'=>$e->getMessage()]);
}
