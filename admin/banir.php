<?php
require "../config.php";

$id = $_GET["id"];

$pdo->prepare("UPDATE usuarios SET banido=1 WHERE id=?")->execute([$id]);

echo "Usuário banido!";
