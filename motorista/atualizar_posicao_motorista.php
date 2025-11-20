<?php
session_start();
require "../config.php";
header("Content-Type: application/json");

if(!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "motorista"){
    echo json_encode(["ok"=>false,"erro"=>"Acesso negado"]);
    exit;
}

$id_motorista = intval($_SESSION["id"]);
$lat = isset($_POST['lat']) ? floatval($_POST['lat']) : null;
$lng = isset($_POST['lng']) ? floatval($_POST['lng']) : null;

if($lat===null || $lng===null){
    echo json_encode(["ok"=>false,"erro"=>"Lat/Lng inválidos"]);
    exit;
}

try{
    $stmt = $pdo->prepare("UPDATE motoristas SET lat=?, lng=? WHERE id=?");
    $stmt->execute([$lat,$lng,$id_motorista]);
    echo json_encode(["ok"=>true]);
}catch(Exception $e){
    error_log($e->getMessage());
    echo json_encode(["ok"=>false,"erro"=>"Erro ao atualizar posição"]);
}
