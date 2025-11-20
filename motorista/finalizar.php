<?php
session_start();
require "../config.php";

$id_m = $_SESSION["id"];

$q = $pdo->prepare("SELECT * FROM viagens WHERE id_motorista=? AND estado='aceita'");
$q->execute([$id_m]);
$lista = $q->fetchAll();

foreach ($lista as $v) {
    echo "Viagem #".$v["id"];
    echo " <a href='finalizar.php?id=".$v["id"]."'>Finalizar</a><br>";
}

if (isset($_GET["id"])) {
    $pdo->prepare("UPDATE viagens SET estado='concluida' WHERE id=?")
        ->execute([$_GET["id"]]);
    echo "Viagem concluída!";
}
