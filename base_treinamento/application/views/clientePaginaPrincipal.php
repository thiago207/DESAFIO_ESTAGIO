<style>
    .produtos-container {
        max-width: 1400px;
        margin: 20px auto;
    }
    .painel-filtros {
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .produto-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        background-color: white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    .produto-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .produto-nome {
        font-size: 20px;
        font-weight: bold;
        color: #333;
        margin-bottom: 10px;
    }
    .produto-loja {
        color: #666;
        font-size: 14px;
        margin-bottom: 10px;
    }
    .produto-preco {
        font-size: 24px;
        color: #27ae60;
        font-weight: bold;
        margin: 10px 0;
    }
    .produto-estoque {
        color: #888;
        font-size: 14px;
        margin-bottom: 15px;
    }
    .produto-descricao {
        color: #555;
        margin: 10px 0;
        line-height: 1.5;
    }
    .quantidade-input {
        width: 80px;
        display: inline-block;
        margin-right: 10px;
    }
    .carrinho-item {
        border-bottom: 1px solid #eee;
        padding: 15px 0;
    }
    .carrinho-item:last-child {
        border-bottom: none;
    }
    .carrinho-total {
        font-size: 24px;
        font-weight: bold;
        color: #27ae60;
        text-align: right;
        margin-top: 20px;
    }
    .filtro-group {
        margin-bottom: 15px;
    }
</style>

<div class="container produtos-container">
    <div class="page-header">
        <h1><span class="glyphicon glyphicon-shopping-cart"></span> Produtos Disponíveis</h1>
    </div>

    <div id="alerta"></div>

    <!-- PAINEL DE FILTROS -->
    <div class="painel-filtros">
        <h4><span class="glyphicon glyphicon-filter"></span> Filtros de Busca</h4>
        <hr>
        
        <div class="row">
            <div class="col-md-3">
                <div class="filtro-group">
                    <label for="filtro_nome">🔍 Nome do Produto:</label>
                    <input type="text" class="form-control" id="filtro_nome" placeholder="Buscar produto...">
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="filtro-group">
                    <label for="filtro_loja">🏪 Loja:</label>
                    <select class="form-control" id="filtro_loja">
                        <option value="">Todas as Lojas</option>
                    </select>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="filtro-group">
                    <label for="filtro_categoria">📂 Categoria:</label>
                    <select class="form-control" id="filtro_categoria">
                        <option value="">Todas as Categorias</option>
                    </select>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="filtro-group">
                    <label>&nbsp;</label>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="filtro_estoque" checked> ✅ Apenas com Estoque
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-3">
                <div class="filtro-group">
                    <label for="filtro_preco_min">💰 Preço Mínimo:</label>
                    <input type="number" class="form-control" id="filtro_preco_min" placeholder="0.00" step="0.01" min="0">
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="filtro-group">
                    <label for="filtro_preco_max">💰 Preço Máximo:</label>
                    <input type="number" class="form-control" id="filtro_preco_max" placeholder="9999.99" step="0.01" min="0">
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="filtro-group">
                    <label>&nbsp;</label>
                    <div>
                        <button class="btn btn-primary" onclick="aplicarFiltros()">
                            <span class="glyphicon glyphicon-search"></span> Buscar
                        </button>
                        <button class="btn btn-default" onclick="limparFiltros()">
                            <span class="glyphicon glyphicon-refresh"></span> Limpar Filtros
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- LISTA DE PRODUTOS -->
    <div id="listaProdutos">
        <div class="text-center">
            <i class="glyphicon glyphicon-refresh glyphicon-spin"></i> Carregando produtos...
        </div>
    </div>
</div>

<!-- MODAL DO CARRINHO -->
<div class="modal fade" id="modalCarrinho" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><span class="glyphicon glyphicon-shopping-cart"></span> Meu Carrinho</h4>
            </div>
            <div class="modal-body">
                <div id="alertaCarrinho"></div>
                <div id="itensCarrinho">
                    <div class="text-center">
                        <i class="glyphicon glyphicon-refresh glyphicon-spin"></i> Carregando...
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Continuar Comprando</button>
                <button type="button" class="btn btn-success btn-lg" onclick="finalizarCompra()" id="btnFinalizar">
                    <span class="glyphicon glyphicon-ok"></span> Finalizar Compra
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Objeto para armazenar os produtos em memória
    let produtosData = [];

    // Carregar ao iniciar
    $(document).ready(function() {
        carregarLojas();
        carregarCategorias();
        carregarProdutos();
        atualizarBadgeCarrinho();
        
        // Adicionar evento de clique no ícone do carrinho
        $('#icone_carrinho').parent().click(function() {
            abrirCarrinho();
        });
        
        // Buscar ao pressionar Enter nos campos
        $('#filtro_nome, #filtro_preco_min, #filtro_preco_max').on('keypress', function(e) {
            if (e.which === 13) {
                aplicarFiltros();
            }
        });
    });

    // Carregar lista de lojas
    function carregarLojas() {
        $.ajax({
            url: "<?=base_url('cliente/ajax_listarLojas')?>",
            type: "GET",
            dataType: "json",
            cache: false,
            success: function(lojas) {
                let html = '<option value="">Todas as Lojas</option>';
                lojas.forEach(function(loja) {
                    html += `<option value="${loja.id_usuario}">${loja.nome_usuario}</option>`;
                });
                $('#filtro_loja').html(html);
            }
        });
    }

    // Carregar lista de categorias
    function carregarCategorias() {
        $.ajax({
            url: "<?=base_url('cliente/ajax_listarCategorias')?>",
            type: "GET",
            dataType: "json",
            cache: false,
            success: function(categorias) {
                let html = '<option value="">Todas as Categorias</option>';
                categorias.forEach(function(cat) {
                    html += `<option value="${cat.id_categoria}">${cat.nome}</option>`;
                });
                $('#filtro_categoria').html(html);
            }
        });
    }

    // Aplicar filtros
    function aplicarFiltros() {
        carregarProdutos();
    }

    // Limpar filtros
    function limparFiltros() {
        $('#filtro_nome').val('');
        $('#filtro_loja').val('');
        $('#filtro_categoria').val('');
        $('#filtro_preco_min').val('');
        $('#filtro_preco_max').val('');
        $('#filtro_estoque').prop('checked', true);
        carregarProdutos();
    }

    // Carregar lista de produtos com filtros
    function carregarProdutos() {
        let filtros = {
            nome: $('#filtro_nome').val(),
            id_loja: $('#filtro_loja').val(),
            id_categoria: $('#filtro_categoria').val(),
            preco_min: $('#filtro_preco_min').val(),
            preco_max: $('#filtro_preco_max').val(),
            apenas_estoque: $('#filtro_estoque').is(':checked') ? 1 : 0
        };

        $.ajax({
            url: "<?=base_url('cliente/ajax_listarProdutos')?>",
            type: "GET",
            dataType: "json",
            data: filtros,
            cache: false,
            success: function(produtos) {
                produtosData = produtos;
                exibirProdutos(produtos);
            },
            error: function() {
                $('#listaProdutos').html('<div class="alert alert-danger">Erro ao carregar produtos</div>');
            }
        });
    }

    // Exibir produtos na tela
    function exibirProdutos(produtos) {
        if (produtos.length === 0) {
            $('#listaProdutos').html(`
                <div class="alert alert-warning text-center">
                    <h4><span class="glyphicon glyphicon-search"></span> Nenhum produto encontrado</h4>
                    <p>Tente ajustar os filtros de busca ou limpar todos os filtros.</p>
                </div>
            `);
            return;
        }

        let html = '<div class="alert alert-success"><strong>' + produtos.length + '</strong> produto(s) encontrado(s)</div>';
        
        produtos.forEach(function(produto) {
            html += `
                <div class="produto-card" id="produto_${produto.id_produto}">
                    <div class="produto-nome">${produto.nome}</div>
                    <div class="produto-loja">
                        <span class="glyphicon glyphicon-home"></span> Vendido por: ${produto.nome_loja}
                    </div>
                    ${produto.descricao ? '<div class="produto-descricao">' + produto.descricao + '</div>' : ''}
                    <div class="produto-preco">R$ ${parseFloat(produto.preco).toFixed(2).replace('.', ',')}</div>
                    <div class="produto-estoque" id="estoque_${produto.id_produto}">
                        <span class="glyphicon glyphicon-list-alt"></span> 
                        <span id="estoque_numero_${produto.id_produto}">${produto.estoque}</span> 
                        ${produto.estoque == 1 ? 'unidade' : 'unidades'} disponível
                    </div>
                    <div>
                        <label for="qtd_${produto.id_produto}">Quantidade:</label>
                        <input type="number" 
                               class="form-control quantidade-input" 
                               id="qtd_${produto.id_produto}" 
                               min="1" 
                               max="${produto.estoque}" 
                               value="1">
                        <button class="btn btn-success" onclick="adicionarAoCarrinho(${produto.id_produto}, ${produto.estoque})">
                            <span class="glyphicon glyphicon-plus"></span> Adicionar ao Carrinho
                        </button>
                    </div>
                </div>
            `;
        });

        $('#listaProdutos').html(html);
    }

    // Adicionar produto ao carrinho
    function adicionarAoCarrinho(id_produto, estoque_disponivel) {
        let quantidade = parseInt($('#qtd_' + id_produto).val());

        if (!quantidade || quantidade <= 0) {
            exibirAviso('Quantidade inválida', 'alerta');
            return;
        }

        if (quantidade > estoque_disponivel) {
            exibirAviso('Quantidade maior que o estoque disponível', 'alerta');
            return;
        }

        $.ajax({
            url: "<?=base_url('cliente/ajax_adicionarCarrinho')?>",
            type: "POST",
            dataType: "json",
            data: {
                id_produto: id_produto,
                quantidade: quantidade
            },
            cache: false,
            success: function(data) {
                if (data.sucesso) {
                    exibirAviso(data.mensagem, 'alerta', 'SUCESSO');
                    $('#quantidade_carrinho').text(data.quantidade_carrinho);
                    atualizarEstoqueNaTela(id_produto, quantidade);
                    $('#qtd_' + id_produto).val(1);
                } else {
                    exibirAviso(data.mensagem, 'alerta', 'ERRO');
                }
            },
            error: function() {
                exibirAviso('Erro ao adicionar ao carrinho', 'alerta');
            }
        });
    }

    // Atualizar estoque na tela em tempo real
    function atualizarEstoqueNaTela(id_produto, quantidade_adicionada) {
        let produto = produtosData.find(p => p.id_produto == id_produto);
        
        if (produto) {
            let novo_estoque = produto.estoque - quantidade_adicionada;
            produto.estoque = novo_estoque;
            
            $('#estoque_' + id_produto).html(`
                <span class="glyphicon glyphicon-list-alt"></span> 
                <span id="estoque_numero_${id_produto}">${novo_estoque}</span> 
                ${novo_estoque == 1 ? 'unidade' : 'unidades'} disponível
            `);
            
            $('#qtd_' + id_produto).attr('max', novo_estoque);
            
            if (novo_estoque <= 0) {
                $('#qtd_' + id_produto).prop('disabled', true);
                $('#produto_' + id_produto + ' button').prop('disabled', true).html('<span class="glyphicon glyphicon-remove"></span> Sem Estoque');
            }
        }
    }

    // Atualizar badge do carrinho
    function atualizarBadgeCarrinho() {
        $.ajax({
            url: "<?=base_url('cliente/ajax_contarCarrinho')?>",
            type: "GET",
            dataType: "json",
            cache: false,
            success: function(data) {
                $('#quantidade_carrinho').text(data.quantidade);
            }
        });
    }

    // Abrir modal do carrinho
    function abrirCarrinho() {
        $('#modalCarrinho').modal('show');
        carregarCarrinho();
    }

    // Carregar itens do carrinho
    function carregarCarrinho() {
        $.ajax({
            url: "<?=base_url('cliente/ajax_listarCarrinho')?>",
            type: "GET",
            dataType: "json",
            cache: false,
            success: function(data) {
                exibirCarrinho(data);
            },
            error: function() {
                $('#itensCarrinho').html('<div class="alert alert-danger">Erro ao carregar carrinho</div>');
            }
        });
    }

    // Exibir itens do carrinho
    function exibirCarrinho(data) {
        let itens = data.itens;
        let total = data.total;

        if (itens.length === 0) {
            $('#itensCarrinho').html(`
                <div class="alert alert-info">
                    <p><strong>Seu carrinho está vazio!</strong></p>
                    <p>Adicione produtos para continuar.</p>
                </div>
            `);
            $('#btnFinalizar').prop('disabled', true);
            return;
        }

        $('#btnFinalizar').prop('disabled', false);

        let html = '';
        itens.forEach(function(item) {
            let subtotal = item.quantidade * item.preco;
            html += `
                <div class="carrinho-item" id="item_${item.id_carrinho_item}">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>${item.nome}</strong><br>
                            <small class="text-muted">Loja: ${item.nome_loja}</small><br>
                            <small>R$ ${parseFloat(item.preco).toFixed(2).replace('.', ',')} cada</small>
                        </div>
                        <div class="col-md-3">
                            <label>Quantidade:</label>
                            <input type="number" 
                                   class="form-control input-sm" 
                                   id="qtd_item_${item.id_carrinho_item}" 
                                   value="${item.quantidade}" 
                                   min="1" 
                                   max="${item.estoque}"
                                   onchange="atualizarQuantidadeItem(${item.id_carrinho_item})">
                            <small class="text-muted">Máx: ${item.estoque}</small>
                        </div>
                        <div class="col-md-2 text-right">
                            <strong>R$ ${subtotal.toFixed(2).replace('.', ',')}</strong>
                        </div>
                        <div class="col-md-1">
                            <button class="btn btn-danger btn-sm" onclick="removerItem(${item.id_carrinho_item})" title="Remover">
                                <span class="glyphicon glyphicon-trash"></span>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        html += `
            <div class="carrinho-total">
                Total: R$ <span id="totalCarrinho">${parseFloat(total).toFixed(2).replace('.', ',')}</span>
            </div>
        `;

        $('#itensCarrinho').html(html);
    }

    // Atualizar quantidade de item
    function atualizarQuantidadeItem(id_carrinho_item) {
        let quantidade = parseInt($('#qtd_item_' + id_carrinho_item).val());

        if (!quantidade || quantidade <= 0) {
            exibirAviso('Quantidade inválida', 'alertaCarrinho');
            return;
        }

        $.ajax({
            url: "<?=base_url('cliente/ajax_atualizarQuantidade')?>",
            type: "POST",
            dataType: "json",
            data: {
                id_carrinho_item: id_carrinho_item,
                quantidade: quantidade
            },
            cache: false,
            success: function(data) {
                if (data.sucesso) {
                    $('#totalCarrinho').text(parseFloat(data.total).toFixed(2).replace('.', ','));
                } else {
                    exibirAviso(data.mensagem, 'alertaCarrinho', 'ERRO');
                }
            },
            error: function() {
                exibirAviso('Erro ao atualizar quantidade', 'alertaCarrinho');
            }
        });
    }

    // Remover item do carrinho
    function removerItem(id_carrinho_item) {
        if (!confirm('Deseja realmente remover este item do carrinho?')) {
            return;
        }

        $.ajax({
            url: "<?=base_url('cliente/ajax_removerItem')?>",
            type: "POST",
            dataType: "json",
            data: {
                id_carrinho_item: id_carrinho_item
            },
            cache: false,
            success: function(data) {
                if (data.sucesso) {
                    exibirAviso(data.mensagem, 'alertaCarrinho', 'SUCESSO');
                    $('#quantidade_carrinho').text(data.quantidade_carrinho);
                    $('#item_' + id_carrinho_item).fadeOut(400, function() {
                        $(this).remove();
                        carregarCarrinho();
                    });
                } else {
                    exibirAviso(data.mensagem, 'alertaCarrinho', 'ERRO');
                }
            },
            error: function() {
                exibirAviso('Erro ao remover item', 'alertaCarrinho');
            }
        });
    }

    // Finalizar compra
    function finalizarCompra() {
        if (!confirm('Deseja finalizar a compra? Esta ação não pode ser desfeita.')) {
            return;
        }

        $('#btnFinalizar').prop('disabled', true).html('<i class="glyphicon glyphicon-refresh glyphicon-spin"></i> Processando...');

        $.ajax({
            url: "<?=base_url('cliente/ajax_finalizarCompra')?>",
            type: "POST",
            dataType: "json",
            cache: false,
            success: function(data) {
                if (data.sucesso) {
                    exibirAviso(data.mensagem, 'alertaCarrinho', 'SUCESSO');
                    $('#quantidade_carrinho').text('0');
                    carregarProdutos();
                    setTimeout(function() {
                        carregarCarrinho();
                        $('#btnFinalizar').prop('disabled', false).html('<span class="glyphicon glyphicon-ok"></span> Finalizar Compra');
                    }, 2000);
                } else {
                    exibirAviso(data.mensagem, 'alertaCarrinho', 'ERRO');
                    $('#btnFinalizar').prop('disabled', false).html('<span class="glyphicon glyphicon-ok"></span> Finalizar Compra');
                }
            },
            error: function() {
                exibirAviso('Erro ao finalizar compra', 'alertaCarrinho');
                $('#btnFinalizar').prop('disabled', false).html('<span class="glyphicon glyphicon-ok"></span> Finalizar Compra');
            }
        });
    }
</script>