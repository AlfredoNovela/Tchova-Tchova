<?php
session_start();
require "../config.php";

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "passageiro") exit;

$id_passageiro = $_SESSION["id"];
$origem = $_POST['origem'];
$destino = $_POST['destino'];
$lat_origem = $_POST['lat_origem'];
$lng_origem = $_POST['lng_origem'];
$lat_destino = $_POST['lat_destino'];
$lng_destino = $_POST['lng_destino'];

$stmt = $pdo->prepare("INSERT INTO viagens 
    (id_passageiro, origem, destino, lat_origem, lng_origem, lat_destino, lng_destino, status) 
    VALUES (?,?,?,?,?,?,?,'pendente')");
$stmt->execute([$id_passageiro,$origem,$destino,$lat_origem,$lng_origem,$lat_destino,$lng_destino]);

echo "ok";
