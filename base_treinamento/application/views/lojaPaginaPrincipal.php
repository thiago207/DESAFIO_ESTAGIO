<style>
    .panel-produtos {
        max-width: 1400px;
        margin: 20px auto;
    }
    .produto-card {
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 15px;
        background-color: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .produto-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    .produto-nome {
        font-size: 18px;
        font-weight: bold;
        color: #333;
    }
    .produto-info {
        color: #666;
        margin: 5px 0;
    }
    .produto-acoes {
        margin-top: 10px;
    }
    #formProduto {
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    .header-com-botao {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
</style>

<div class="container panel-produtos">
    <div class="header-com-botao">
        <h1><span class="glyphicon glyphicon-list-alt"></span> Meus Produtos</h1>
        <a href="<?=base_url('produtos/cadastrar')?>" class="btn btn-primary btn-lg">
            <span class="glyphicon glyphicon-plus"></span> Cadastrar Novo Produto
        </a>
    </div>

    <div id="alerta"></div>

    <!-- FORMULÁRIO DE EDIÇÃO (só aparece quando clica em Editar) -->
    <div class="panel panel-warning" id="painelEdicao" style="display:none;">
        <div class="panel-heading">
            <h3 class="panel-title">
                <span class="glyphicon glyphicon-pencil"></span> Editar Produto
            </h3>
        </div>
        <div class="panel-body">
            <form id="formProduto">
                <input type="hidden" id="id_produto" value="">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nome"><span class="glyphicon glyphicon-tag"></span> Nome do Produto *</label>
                            <input class="form-control" placeholder="Nome do produto" id="nome" type="text" maxlength="255" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="custo"><span class="glyphicon glyphicon-usd"></span> Custo (R$)</label>
                            <input class="form-control" placeholder="0.00" id="custo" type="number" step="0.01" min="0" value="0">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="preco"><span class="glyphicon glyphicon-usd"></span> Preço de Venda (R$) *</label>
                            <input class="form-control" placeholder="0.00" id="preco" type="number" step="0.01" min="0.01" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="estoque"><span class="glyphicon glyphicon-list-alt"></span> Estoque *</label>
                            <input class="form-control" placeholder="Quantidade em estoque" id="estoque" type="number" min="0" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="descricao"><span class="glyphicon glyphicon-align-left"></span> Descrição</label>
                            <textarea class="form-control" placeholder="Descrição do produto (opcional)" id="descricao" rows="3" maxlength="500"></textarea>
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <button type="button" class="btn btn-default" onclick="cancelarEdicao()">
                        <span class="glyphicon glyphicon-remove"></span> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <span class="glyphicon glyphicon-floppy-disk"></span> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- LISTA DE PRODUTOS -->
    <div class="panel panel-default">
        <div class="panel-body">
            <div id="listaProdutos">
                <div class="text-center">
                    <i class="glyphicon glyphicon-refresh glyphicon-spin"></i> Carregando produtos...
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Carregar produtos ao iniciar a página
    $(document).ready(function() {
        carregarProdutos();
    });

    // Função para carregar lista de produtos
    function carregarProdutos() {
        $.ajax({
            url: "<?=base_url('produtos/ajax_listar')?>",
            type: "GET",
            dataType: "json",
            cache: false,
            success: function(produtos) {
                exibirProdutos(produtos);
            },
            error: function() {
                $('#listaProdutos').html('<div class="alert alert-danger">Erro ao carregar produtos</div>');
            }
        });
    }

    // Função para exibir produtos na tela
    function exibirProdutos(produtos) {
        if (produtos.length === 0) {
            $('#listaProdutos').html(`
                <div class="text-center" style="padding: 80px 20px;">
                    <h2 style="color: #999;">
                        <span class="glyphicon glyphicon-inbox" style="font-size: 60px; display: block; margin-bottom: 20px;"></span>
                        Nenhum produto cadastrado ainda
                    </h2>
                    <p style="font-size: 16px; color: #666; margin: 20px 0;">
                        Comece cadastrando seu primeiro produto para começar a vender!
                    </p>
                    <a href="<?=base_url('produtos/cadastrar')?>" class="btn btn-primary btn-lg">
                        <span class="glyphicon glyphicon-plus"></span> Cadastrar Primeiro Produto
                    </a>
                </div>
            `);
            return;
        }

        let html = '';
        produtos.forEach(function(produto) {
            html += `
                <div class="produto-card" id="produto_${produto.id_produto}">
                    <div class="produto-header">
                        <div>
                            <div class="produto-nome">${produto.nome}</div>
                            <div class="produto-info">
                                <span class="label label-success">Preço: R$ ${parseFloat(produto.preco).toFixed(2).replace('.', ',')}</span>
                                <span class="label label-info">Estoque: ${produto.estoque}</span>
                                ${produto.custo > 0 ? '<span class="label label-default">Custo: R$ ' + parseFloat(produto.custo).toFixed(2).replace('.', ',') + '</span>' : ''}
                            </div>
                        </div>
                        <div class="produto-acoes">
                            <button class="btn btn-sm btn-primary" onclick="editarProduto(${produto.id_produto})" title="Editar">
                                <span class="glyphicon glyphicon-pencil"></span> Editar
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="confirmarDeletar(${produto.id_produto}, '${produto.nome.replace(/'/g, "\\'")}')" title="Deletar">
                                <span class="glyphicon glyphicon-trash"></span> Deletar
                            </button>
                        </div>
                    </div>
                    ${produto.descricao ? '<div class="produto-info"><strong>Descrição:</strong> ' + produto.descricao + '</div>' : ''}
                </div>
            `;
        });

        $('#listaProdutos').html(html);
    }

    // Validar dados do formulário
    function validarDados() {
        let nome = $("#nome").val().trim();
        let preco = parseFloat($("#preco").val());
        let estoque = parseInt($("#estoque").val());

        if (!nome) {
            exibirAviso('Nome do produto é obrigatório', 'alerta');
            return false;
        }

        if (!preco || preco <= 0) {
            exibirAviso('Preço deve ser maior que zero', 'alerta');
            return false;
        }

        if (isNaN(estoque) || estoque < 0) {
            exibirAviso('Estoque inválido', 'alerta');
            return false;
        }

        return true;
    }

    // Submit do formulário (SOMENTE EDIÇÃO)
    $('#formProduto').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarDados()) {
            return;
        }

        let dados = {
            id_produto: $('#id_produto').val(),
            nome: $("#nome").val().trim(),
            custo: $("#custo").val() || 0,
            preco: $("#preco").val(),
            estoque: $("#estoque").val(),
            descricao: $("#descricao").val().trim()
        };

        $.ajax({
            url: "<?=base_url('produtos/ajax_editar')?>",
            type: "POST",
            dataType: "json",
            data: dados,
            cache: false,
            success: function(data) {
                if (data.sucesso) {
                    exibirAviso(data.mensagem, 'alerta', 'SUCESSO');
                    cancelarEdicao();
                    carregarProdutos();
                } else {
                    exibirAviso(data.mensagem, 'alerta', 'ERRO');
                }
            },
            error: function() {
                exibirAviso('Aconteceu um erro em nosso servidor', 'alerta');
            }
        });
    });

    // Função para editar produto
    function editarProduto(id_produto) {
        $.ajax({
            url: "<?=base_url('produtos/ajax_buscar')?>",
            type: "POST",
            dataType: "json",
            data: { id_produto: id_produto },
            cache: false,
            success: function(produto) {
                if (produto) {
                    $('#id_produto').val(produto.id_produto);
                    $('#nome').val(produto.nome);
                    $('#custo').val(produto.custo);
                    $('#preco').val(produto.preco);
                    $('#estoque').val(produto.estoque);
                    $('#descricao').val(produto.descricao);
                    
                    // Mostrar painel de edição
                    $('#painelEdicao').slideDown();
                    
                    // Rolar para o formulário
                    $('html, body').animate({ scrollTop: $('#painelEdicao').offset().top - 20 }, 500);
                }
            },
            error: function() {
                exibirAviso('Erro ao carregar dados do produto', 'alerta');
            }
        });
    }

    // Cancelar edição
    function cancelarEdicao() {
        $('#formProduto')[0].reset();
        $('#id_produto').val('');
        $('#painelEdicao').slideUp();
    }

    // Confirmar deleção
    function confirmarDeletar(id_produto, nome_produto) {
        if (confirm('Tem certeza que deseja deletar o produto "' + nome_produto + '"?\n\nObs: Se o produto já foi vendido, não será possível deletá-lo.')) {
            deletarProduto(id_produto);
        }
    }

    // Deletar produto
    function deletarProduto(id_produto) {
        $.ajax({
            url: "<?=base_url('produtos/ajax_deletar')?>",
            type: "POST",
            dataType: "json",
            data: { id_produto: id_produto },
            cache: false,
            success: function(data) {
                if (data.sucesso) {
                    exibirAviso(data.mensagem, 'alerta', 'SUCESSO');
                    $('#produto_' + id_produto).fadeOut(400, function() {
                        $(this).remove();
                        if ($('.produto-card').length === 0) {
                            carregarProdutos();
                        }
                    });
                } else {
                    exibirAviso(data.mensagem, 'alerta', 'ERRO');
                }
            },
            error: function() {
                exibirAviso('Aconteceu um erro em nosso servidor', 'alerta');
            }
        });
    }
</script>