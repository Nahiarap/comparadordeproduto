<?php
session_start();
$sessao_id = session_id(); // Identificador único do navegador atual [cite: 30]
$pdo = new PDO("mysql:host=localhost;dbname=comparador_produtos", "root", "");

$acao = $_GET['acao'] ?? '';

// FUNCIONALIDADE 1: Consultar Comparação

if ($acao == 'consultar') {
    // Note que tirei o "WHERE sessao_id = ?" para facilitar o teste de vocês agora
    $sql = "SELECT p.* FROM comparacoes c JOIN produtos p ON c.produto_id = p.id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

// FUNCIONALIDADE 2: Incluir na Comparação
if ($acao == 'incluir') {
    $prod_id = $_GET['id'];
    
    // RN-01: Validar limite de 3 [cite: 26]
    $check = $pdo->prepare("SELECT count(*) FROM comparacoes WHERE sessao_id = ?");
    $check->execute([$sessao_id]);
    if ($check->fetchColumn() >= 3) {
        die(json_encode(['error' => 'Limite de 3 atingido']));
    }

    $stmt = $pdo->prepare("INSERT INTO comparacoes (sessao_id, produto_id) VALUES (?, ?)");
    $stmt->execute([$sessao_id, $prod_id]);
    echo json_encode(['success' => true]);
}

// FUNCIONALIDADE 3: Excluir da Comparação [cite: 110]
if ($acao == 'excluir') {
    $prod_id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM comparacoes WHERE sessao_id = ? AND produto_id = ?");
    $stmt->execute([$sessao_id, $prod_id]);
    echo json_encode(['success' => true]);
}
?>