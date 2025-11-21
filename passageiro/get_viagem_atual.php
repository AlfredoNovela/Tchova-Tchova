<?php
session_start();
require "../config.php";
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "passageiro") {
    echo json_encode(['ok' => false, 'erro' => 'Acesso negado']);
    exit;
}

$id_passageiro = intval($_SESSION['id']);

/*
  Seleciona a viagem mais recente do passageiro que esteja em pendente/aceita/andamento.
  Faz LEFT JOIN tanto em usuarios (onde pode estar o nome do motorista) quanto em motoristas
  (onde estão marca/modelo/matricula/coords). Algumas colunas podem ser null dependendo de
  onde as informações foram gravadas — o front deve tratar isso.
*/
$sql = "
    SELECT
        v.*,
        u.id   AS usuario_motorista_id,
        u.nome AS nome_motorista,
        u.email AS email_motorista,
        m.id   AS motorista_id,
        m.marca,
        m.modelo,
        m.matricula,
        m.lat  AS lat_motorista,
        m.lng  AS lng_motorista,
        m.foto_veiculo
    FROM viagens v
    LEFT JOIN usuarios u ON u.id = v.id_motorista
    LEFT JOIN motoristas m ON m.id = v.id_motorista
    WHERE v.id_passageiro = ?
      AND v.estado IN ('pendente','aceita','andamento')
    ORDER BY v.data_hora DESC
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_passageiro]);
$viagem = $stmt->fetch(PDO::FETCH_ASSOC);

// Normaliza/assegura alguns campos (opcional)
if ($viagem) {
    // Forçar tipos numéricos se existirem
    $viagem['lat_motorista'] = isset($viagem['lat_motorista']) ? (float)$viagem['lat_motorista'] : null;
    $viagem['lng_motorista'] = isset($viagem['lng_motorista']) ? (float)$viagem['lng_motorista'] : null;
    $viagem['lat_origem'] = isset($viagem['lat_origem']) ? (float)$viagem['lat_origem'] : null;
    $viagem['lng_origem'] = isset($viagem['lng_origem']) ? (float)$viagem['lng_origem'] : null;
    $viagem['lat_destino'] = isset($viagem['lat_destino']) ? (float)$viagem['lat_destino'] : null;
    $viagem['lng_destino'] = isset($viagem['lng_destino']) ? (float)$viagem['lng_destino'] : null;
}

echo json_encode([
    'ok' => true,
    'viagem' => $viagem
], JSON_UNESCAPED_UNICODE);
