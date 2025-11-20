<?php
session_start();
require "../config.php";
$stmt = $pdo->prepare("SELECT v.*, u.nome as nome_passageiro, u.id as id_passageiro FROM viagens v 
JOIN usuarios u ON u.id = v.id_passageiro 
WHERE v.estado='pendente' ORDER BY v.data_hora ASC LIMIT 1");
$stmt->execute();
$viagem = $stmt->fetch(PDO::FETCH_ASSOC);

// calcular distância e tempo
if($viagem){
    $lat1 = $viagem['lat_origem']; $lng1 = $viagem['lng_origem'];
    $lat2 = -25.960; $lng2 = 32.590; // destino fixo ou geocoding
    $dist = 6371*2*asin(sqrt(pow(sin(($lat2-$lat1)*pi()/360),2)+cos($lat1*pi()/180)*cos($lat2*pi()/180)*pow(sin(($lng2-$lng1)*pi()/360),2)));
    $viagem['distancia'] = round($dist,2);
    $viagem['tempo'] = round($dist/30*60);
}
echo json_encode(['viagem'=>$viagem ?: null]);
