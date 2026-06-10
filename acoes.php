<?php
session_start();
$sessao_id = session_id(); // Identificador único do navegador atual [cite: 30]
$pdo = new PDO("mysql:host=localhost;dbname=comparador_produtos", "root", "");

$acao = $_GET['acao'] ?? '';

// FUNCIONALIDADE 1: Consultar Comparação
if ($acao == 'consultar') {
    $ids = $_GET['ids'] ?? '';
    
    if (!empty($ids)) {
        // Se houver IDs na URL (Compartilhamento), busca apenas esses
        $ids_array = explode(',', $ids);
        $placeholders = implode(',', array_fill(0, count($ids_array), '?'));
        $sql = "SELECT * FROM produtos WHERE id IN ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($ids_array);
    } else {
        // Caso contrário, busca os itens da sessão atual
        $sql = "SELECT p.* FROM comparacoes c JOIN produtos p ON c.produto_id = p.id WHERE c.sessao_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sessao_id]);
    }
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

// FUNCIONALIDADE EXTRA: Listar Catálogo Completo
if ($acao == 'catalogo') {
    $sql = "SELECT * FROM produtos";
    $stmt = $pdo->query($sql);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

// FUNCIONALIDADE 2: Incluir na Comparação
if ($acao == 'incluir') {
    $prod_id = $_GET['id'];
    
    // RN-05: Validar limite de 3 [cite: 26]
    $check = $pdo->prepare("SELECT count(*) FROM comparacoes WHERE sessao_id = ?");
    $check->execute([$sessao_id]);
    if ($check->fetchColumn() >= 3) {
        die(json_encode(['error' => 'Limite de 3 atingido']));
    }

    // RN-06: Prevenção de Duplicidade
    $dup_check = $pdo->prepare("SELECT count(*) FROM comparacoes WHERE sessao_id = ? AND produto_id = ?");
    $dup_check->execute([$sessao_id, $prod_id]);
    if ($dup_check->fetchColumn() > 0) {
        die(json_encode(['error' => 'Este produto já está na lista de comparação']));
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