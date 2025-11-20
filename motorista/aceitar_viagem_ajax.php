<?php
session_start();
require "../config.php";
if(!isset($_SESSION['id']) || $_SESSION['tipo']!=='motorista') exit;
$id_motorista = $_SESSION['id'];
$id_viagem = $_GET['id'];
$pdo->prepare("UPDATE viagens SET id_motorista=?, estado='aceita' WHERE id=? AND estado='pendente'")->execute([$id_motorista,$id_viagem]);
