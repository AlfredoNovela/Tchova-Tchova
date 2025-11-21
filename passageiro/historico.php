<?php
session_start();
require "../config.php";

// Verifica se o usuário está logado e é do tipo passageiro
if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "passageiro") {
    header("Location: ../index.php");
    exit;
}

$id = intval($_SESSION['id']);

// =========================
// FILTROS
// =========================
$estado = $_GET['estado'] ?? "";
$data_inicio = $_GET['data_inicio'] ?? "";
$data_fim = $_GET['data_fim'] ?? "";

// PAGINAÇÃO
$pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$limite = 10;
$offset = ($pagina - 1) * $limite;

// =========================
// BUSCA DE VIAGENS
// =========================
// Construção da query dinâmica com filtros
$query = "SELECT * FROM viagens WHERE id_passageiro = :id";
$params = [":id" => $id];

// Filtro por estado
if ($estado !== "") {
    $query .= " AND estado = :estado";
    $params[":estado"] = $estado;
}

// Filtro por datas
if ($data_inicio !== "") {
    $query .= " AND DATE(data_hora) >= :inicio";
    $params[":inicio"] = $data_inicio;
}
if ($data_fim !== "") {
    $query .= " AND DATE(data_hora) <= :fim";
    $params[":fim"] = $data_fim;
}

// Executa a query sem ORDER BY nem LIMIT, porque vamos ordenar manualmente no PHP
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$viagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =========================
// ALGORITMO DE ORDENACAO - BUBBLE SORT
// Ordena as viagens por data_hora (mais recente primeiro)
// =========================

// $n recebe o número de viagens
$n = count($viagens);

// Loop externo percorre todas as "passadas" do Bubble Sort
for ($i = 0; $i < $n - 1; $i++) {

    // Loop interno percorre os elementos ainda não ordenados
    for ($j = 0; $j < $n - $i - 1; $j++) {

        // Compara timestamps das viagens consecutivas
        // Se a viagem atual for mais antiga que a próxima, troca
        if (strtotime($viagens[$j]['data_hora']) < strtotime($viagens[$j + 1]['data_hora'])) {

            // Guarda temporariamente a viagem atual
            $temp = $viagens[$j];

            // Coloca a próxima viagem na posição atual
            $viagens[$j] = $viagens[$j + 1];

            // Coloca a viagem atual na posição da próxima
            $viagens[$j + 1] = $temp;
        }
    }
}

/*
Explicação Bubble Sort:

1. Compara cada par de elementos consecutivos no array.
2. O elemento "maior" (mais recente) sobe para o início do array a cada passagem.
3. Repete até que todo o array esteja ordenado do mais recente para o mais antigo.
4. Complexidade: O(n^2), adequado para arrays pequenos (como histórico de viagens).
*/

// =========================
// PAGINAÇÃO MANUAL
// =========================
$total = count($viagens); // total de viagens após ordenação
$viagens = array_slice($viagens, $offset, $limite); // pega apenas as viagens da página atual

// =========================
// FUNÇÃO PARA PEGAR MOTORISTA
// =========================
function getMotorista($pdo, $id_motorista) {
    if (!$id_motorista) return '-';
    $stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
    $stmt->execute([$id_motorista]);
    $row = $stmt->fetch();
    return $row ? $row['nome'] : '-';
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Histórico de Viagens</title>
<link rel="stylesheet" href="../assets/css/dashboard.css">
<style>
/* Estilos específicos da página */
.table-wrapper { margin-top: 25px; background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 14px rgba(0,0,0,0.07); }
.table-filters { background: #fff; padding: 20px; border-radius: 14px; margin-bottom: 20px; box-shadow:0 4px 14px rgba(0,0,0,.07);}
.table-filters form { display:flex; gap:20px; flex-wrap:wrap; align-items:flex-end; }
.table-filters select, .table-filters input { padding:10px; border-radius:6px; border:1px solid #ccc; font-size:14px; }
.btn { padding:10px 16px; background:#1B4FA0; color:#fff; border-radius:8px; border:none; cursor:pointer; text-decoration:none; font-weight:600; }
.btn:hover { background:#0F3A7A; }
.btn.ghost { background:transparent; color:#1B4FA0; border:2px solid #1B4FA0; }
.btn.ghost:hover { background:#1B4FA0; color:#fff; }
table { width:100%; border-collapse:collapse; }
table thead { background:#1B4FA0; color:#fff; }
table th, table td { padding:12px; text-align:left; font-size:14px; }
tbody tr:nth-child(odd) { background:#f7f9ff; }
.pagination { margin-top:20px; text-align:center; }
.pagination a { margin:0 5px; padding:8px 12px; background:#1B4FA0; color:#fff; border-radius:6px; text-decoration:none; }
.pagination a.active { background:#0A2E63; }
</style>
</head>
<body>

<div class="sidebar">
    <div class="brand">
        <a href="dashboard.php"> <!-- Torna a logo clicável -->
            <img src="../assets/img/logo.png" class="brand-logo" alt="dashboard">
        </a>
        <h2>Tchova-Tchova</h2>
    </div>

    <div class="profile-box">
        <div class="profile-img">
            <img src="../assets/img/user.png" alt="Passageiro" class="car-photo">
        </div>
        <h3>Passageiro</h3>
        
    </div>

    <nav>
        <a href="solicitar_viagem.php">📍 Solicitar Viagem</a>
        <a href="historico.php">🕒 Histórico de Viagens</a>
        <a href="../logout.php" class="logout">↩ Sair</a>
    </nav>
</div>

<div class="main">
    <header>
        <h1>Histórico de Viagens</h1>
        <p>Veja todas as viagens feitas no aplicativo.</p>
    </header>

    <!-- FILTROS -->
    <div class="table-filters">
        <form method="GET">
            <div>
                <label>Estado:</label><br>
                <select name="estado">
                    <option value="">Todos</option>
                    <option value="pendente" <?= $estado=="pendente"?"selected":"" ?>>Pendente</option>
                    <option value="aceita" <?= $estado=="aceita"?"selected":"" ?>>Aceita</option>
                    <option value="finalizada" <?= $estado=="finalizada"?"selected":"" ?>>Finalizada</option>
                </select>
            </div>

            <div>
                <label>Data início:</label><br>
                <input type="date" name="data_inicio" value="<?= $data_inicio ?>">
            </div>

            <div>
                <label>Data fim:</label><br>
                <input type="date" name="data_fim" value="<?= $data_fim ?>">
            </div>

            <button class="btn" type="submit">Filtrar</button>
            <a class="btn ghost" href="historico.php">Limpar</a>
        </form>
    </div>

    <!-- TABELA -->
    <div class="table-wrapper">
        <?php if (count($viagens) === 0): ?>
            <div style="padding:25px; text-align:center; color:#555;">Nenhuma viagem encontrada.</div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Origem</th>
                    <th>Destino</th>
                    <th>Motorista</th>
                    <th>Estado</th>
                    <th>Data/Hora</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($viagens as $v): ?>
                <tr>
                    <td><?= $v['id'] ?></td>
                    <td><?= htmlspecialchars($v['origem']) ?></td>
                    <td><?= htmlspecialchars($v['destino']) ?></td>
                    <td><?= htmlspecialchars(getMotorista($pdo, $v['id_motorista'])) ?></td>
                    <td><?= htmlspecialchars($v['estado']) ?></td>
                    <td><?= htmlspecialchars($v['data_hora']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- PAGINAÇÃO -->
    <div class="pagination">
        <?php
        $total_paginas = ceil($total / $limite);
        for ($i = 1; $i <= $total_paginas; $i++):
        ?>
            <a href="?<?= http_build_query(array_merge($_GET, ["pagina"=>$i])) ?>"
               class="<?= ($i==$pagina) ? "active" : "" ?>">
               <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>

</div>

</body>
</html>
