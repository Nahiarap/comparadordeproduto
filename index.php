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
        <button class="btn btn-dark rounded-pill px-4" onclick="compartilhar()">
            <i class="bi bi-share-fill me-2"></i>Compartilhar
        </button>
    </div>

    <div id="matriz-comparacao" class="row g-4">
        <div class="col-12 text-center p-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-3 text-muted">Acessando banco de dados...</p>
        </div>
    </div>
</div>

<footer class="text-center py-4 border-top bg-white mt-auto">
    <small class="text-muted">Laboratório de Inovação - Grupo 9: Luiza, Cadu e Nahiara</small>
</footer>

<script>
async function carregarTabela() {
    try {
        // Busca os dados do seu arquivo acoes.php
        const res = await fetch('acoes.php?acao=consultar');
        const produtos = await res.json();
        
        const container = document.getElementById('matriz-comparacao');
        
        if (!produtos || produtos.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="bi bi-cart-x text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-3 text-muted">Nenhum produto para comparar</h4>
                    <p>Adicione itens do catálogo para visualizar a matriz.</p>
                </div>`;
            return;
        }

        let html = '';
        produtos.forEach(p => {
            // Tratamento do JSON de especificações técnicas
            let specs = {};
            try {
                specs = typeof p.especificacoes === 'string' ? JSON.parse(p.especificacoes) : p.especificacoes;
            } catch (e) { specs = { "Info": "Padrão Apple" }; }

            html += `
                <div class="col-md-4">
                    <div class="card product-card shadow-sm h-100">
                        <div class="p-3 d-flex justify-content-end">
                            <button class="btn-close" onclick="excluir(${p.id})" title="Remover"></button>
                        </div>
                        
                        <div class="img-container">
                            <img src="${p.imagem}" 
                                 class="img-comparar" 
                                 alt="${p.nome}"
                                 onerror="this.src='https://via.placeholder.com/300x300?text=Imagem+Nao+Encontrada'">
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
                            
                            <button class="btn btn-primary w-100 rounded-pill mt-4 fw-bold py-2" onclick="vender(${p.id})">
                                Comprar agora
                            </button>
                        </div>
                    </div>
                </div>`;
        });
        container.innerHTML = html;
        
    } catch (error) {
        console.error("Erro:", error);
        document.getElementById('matriz-comparacao').innerHTML = `
            <div class="alert alert-danger mx-auto" style="max-width: 500px;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Erro de conexão com o Banco de Dados. Verifique o arquivo acoes.php.
            </div>`;
    }
}

async function excluir(id) {
    if(confirm("Tem certeza que deseja remover este iPhone da comparação?")) {
        await fetch(`acoes.php?acao=excluir&id=${id}`);
        carregarTabela(); // Recarrega a tela na hora
    }
}

function compartilhar() {
    // Pegamos o link atual da página
    const linkDaPagina = window.location.href;
    
    // Usamos a API do navegador para copiar
    navigator.clipboard.writeText(linkDaPagina).then(() => {
        // Criamos um alerta visual bonitinho do Bootstrap
        alert("🚀 Link copiado! Agora você pode enviar para seus colegas verem a mesma comparação.");
    }).catch(err => {
        console.error('Erro ao copiar: ', err);
    });
}

function vender(id) {
    alert("Integração: Produto " + id + " enviado para o checkout/carrinho!");
}

// Inicia a busca dos dados assim que a página termina de carregar
document.addEventListener('DOMContentLoaded', carregarTabela);
</script>

</body>
</html>