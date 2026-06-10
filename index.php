<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comparador de iPhones - Grupo 9</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root { --apple-gray: #f5f5f7; }
        body { 
            background-color: var(--apple-gray); 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .product-card { 
            border: none; 
            border-radius: 20px; 
            transition: transform 0.3s ease;
            background: white;
        }
        .product-card:hover { transform: translateY(-10px); }
        
        .img-container {
            height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .img-comparar { 
            max-height: 100%;
            max-width: 100%;
            object-fit: contain; 
        }

        .spec-box {
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            margin-top: 15px;
        }
        .spec-label { font-weight: 700; color: #86868b; font-size: 0.75rem; text-transform: uppercase; }
        .spec-value { font-weight: 500; color: #1d1d1f; margin-bottom: 8px; border-bottom: 1px solid #eee; padding-bottom: 4px; }
        .price-tag { color: #0071e3; font-weight: 700; font-size: 1.25rem; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark mb-5 shadow-sm">
    <div class="container">
        <span class="navbar-brand mb-0 h1"><i class="bi bi-apple"></i> ADS - Comparador de Produtos</span>
    </div>
</nav>

<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h1 class="fw-bold mb-0">Sua Comparação</h1>
            <p class="text-muted">Análise técnica dos modelos selecionados</p>
        </div>
        <div>
            <button class="btn btn-dark rounded-pill px-4" onclick="compartilhar()">
                <i class="bi bi-share-fill me-2"></i>Compartilhar
            </button>
        </div>
    </div>

    <div id="matriz-comparacao" class="row g-4 mb-5">
        <div class="col-12 text-center p-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-3 text-muted">Acessando banco de dados...</p>
        </div>
    </div>

    <hr class="my-5">

    <div class="mb-4">
        <h2 class="fw-bold">Catálogo de iPhones</h2>
        <p class="text-muted">Escolha até 3 modelos para comparar</p>
    </div>
    
    <div id="catalogo-produtos" class="row g-4">
        <!-- Catálogo será carregado aqui -->
    </div>
</div>

<footer class="text-center py-4 border-top bg-white mt-auto">
    <small class="text-muted">Laboratório de Inovação - Grupo 9: Luiza, Cadu e Nahiara</small>
</footer>

<script>
// Variável global para armazenar os IDs da comparação atual
let idsNaComparacao = [];

async function carregarTabela() {
    try {
        // Verifica se existem IDs na URL (para o compartilhamento)
        const urlParams = new URLSearchParams(window.location.search);
        const idsCompartilhados = urlParams.get('ids');
        
        const url = idsCompartilhados ? `acoes.php?acao=consultar&ids=${idsCompartilhados}` : 'acoes.php?acao=consultar';
        const res = await fetch(url);
        const produtos = await res.json();
        
        const container = document.getElementById('matriz-comparacao');
        idsNaComparacao = produtos.map(p => p.id);
        
        if (!produtos || produtos.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="bi bi-plus-circle text-muted" style="font-size: 3rem;"></i>
                    <h4 class="mt-3 text-muted">Nenhum produto selecionado</h4>
                    <p>Escolha modelos no catálogo abaixo para iniciar a comparação.</p>
                </div>`;
            return;
        }

        let html = '';
        produtos.forEach(p => {
            let specs = {};
            try { specs = typeof p.especificacoes === 'string' ? JSON.parse(p.especificacoes) : p.especificacoes; } 
            catch (e) { specs = { "Info": "Padrão Apple" }; }

            html += `
                <div class="col-md-4">
                    <div class="card product-card shadow-sm h-100">
                        <div class="p-3 d-flex justify-content-end">
                            <button class="btn-close" onclick="excluir(${p.id})" title="Remover"></button>
                        </div>
                        <div class="img-container">
                            <img src="${p.imagem}" class="img-comparar" alt="${p.nome}">
                        </div>
                        <div class="card-body px-4 pb-4">
                            <h3 class="h5 fw-bold text-center mb-1">${p.nome}</h3>
                            <p class="price-tag text-center mb-3">R$ ${p.preco}</p>
                            <div class="spec-box">
                                ${Object.keys(specs).map(key => `
                                    <div class="spec-item">
                                        <div class="spec-label">${key}</div>
                                        <div class="spec-value">${specs[key]}</div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                </div>`;
        });
        container.innerHTML = html;
        
    } catch (error) {
        console.error("Erro:", error);
    }
}

async function carregarCatalogo() {
    try {
        const res = await fetch('acoes.php?acao=catalogo');
        const produtos = await res.json();
        const container = document.getElementById('catalogo-produtos');
        
        let html = '';
        produtos.forEach(p => {
            html += `
                <div class="col-md-3">
                    <div class="card h-100 border-0 shadow-sm rounded-4">
                        <img src="${p.imagem}" class="card-img-top p-4" style="height: 150px; object-fit: contain;">
                        <div class="card-body text-center">
                            <h6 class="fw-bold">${p.nome}</h6>
                            <button class="btn btn-sm btn-outline-primary rounded-pill w-100 mt-2" onclick="incluir(${p.id})">
                                + Comparar
                            </button>
                        </div>
                    </div>
                </div>`;
        });
        container.innerHTML = html;
    } catch (e) { console.error(e); }
}

async function incluir(id) {
    const res = await fetch(`acoes.php?acao=incluir&id=${id}`);
    const dados = await res.json();
    
    if (dados.error) {
        alert("⚠️ " + dados.error);
    } else {
        carregarTabela();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

async function excluir(id) {
    await fetch(`acoes.php?acao=excluir&id=${id}`);
    carregarTabela();
}

function compartilhar() {
    if (idsNaComparacao.length === 0) {
        alert("Adicione produtos primeiro para compartilhar!");
        return;
    }

    // Criamos o link com os IDs atuais: index.php?ids=1,2,3
    const base = window.location.origin + window.location.pathname;
    const linkFinal = `${base}?ids=${idsNaComparacao.join(',')}`;
    
    navigator.clipboard.writeText(linkFinal).then(() => {
        alert("🚀 Link de comparação gerado e copiado! \n\n" + linkFinal);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    carregarTabela();
    carregarCatalogo();
});
</script>

</body>
</html>