<style>
    .pedidos-container {
        max-width: 1400px;
        margin: 20px auto;
    }
    .pedido-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        background-color: white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .pedido-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 2px solid #eee;
    }
    .pedido-numero {
        font-size: 18px;
        font-weight: bold;
        color: #333;
    }
    .pedido-data {
        color: #666;
        font-size: 14px;
    }
    .pedido-item {
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .pedido-item:last-child {
        border-bottom: none;
    }
    .pedido-total {
        font-size: 20px;
        font-weight: bold;
        color: #27ae60;
        text-align: right;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 2px solid #eee;
    }
    .label-status {
        font-size: 12px;
        padding: 5px 10px;
    }
</style>

<div class="container pedidos-container">
    <div class="page-header">
        <h1><span class="glyphicon glyphicon-list-alt"></span> Meus Pedidos</h1>
    </div>

    <div id="alerta"></div>

    <!-- LISTA DE PEDIDOS -->
    <div id="listaPedidos">
        <div class="text-center">
            <i class="glyphicon glyphicon-refresh glyphicon-spin"></i> Carregando pedidos...
        </div>
    </div>
</div>

<script>
    // Carregar pedidos ao iniciar
    $(document).ready(function() {
        carregarPedidos();
    });

    // Carregar lista de pedidos
    function carregarPedidos() {
        $.ajax({
            url: "<?=base_url('cliente/ajax_listarPedidos')?>",
            type: "GET",
            dataType: "json",
            cache: false,
            success: function(pedidos) {
                exibirPedidos(pedidos);
            },
            error: function() {
                $('#listaPedidos').html('<div class="alert alert-danger">Erro ao carregar pedidos</div>');
            }
        });
    }

    // Exibir pedidos na tela
    function exibirPedidos(pedidos) {
        if (pedidos.length === 0) {
            $('#listaPedidos').html(`
                <div class="alert alert-info text-center">
                    <h3><span class="glyphicon glyphicon-info-sign"></span> Você não tem pedidos</h3>
                    <p>Quando você finalizar uma compra, seus pedidos aparecerão aqui!</p>
                    <a href="<?=base_url('cliente')?>" class="btn btn-primary">
                        <span class="glyphicon glyphicon-shopping-cart"></span> Ir para Produtos
                    </a>
                </div>
            `);
            return;
        }

        let html = '';
        pedidos.forEach(function(pedido) {
            html += `
                <div class="pedido-card">
                    <div class="pedido-header">
                        <div>
                            <div class="pedido-numero">
                                Pedido #${pedido.id_venda}
                            </div>
                            <div class="pedido-data">
                                <span class="glyphicon glyphicon-calendar"></span> 
                                ${formatarData(pedido.data_venda)}
                            </div>
                        </div>
                        <div>
                            <span class="label label-success label-status">Finalizado</span>
                        </div>
                    </div>
                    
                    <div class="pedido-itens">
                        <h4>Itens do Pedido:</h4>
            `;

            // Listar itens do pedido
            pedido.itens.forEach(function(item) {
                let subtotal = item.quantidade * item.preco;
                html += `
                    <div class="pedido-item">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>${item.nome_produto}</strong><br>
                                <small class="text-muted">Loja: ${item.nome_loja}</small>
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="label label-default">${item.quantidade}x</span>
                            </div>
                            <div class="col-md-2 text-center">
                                R$ ${parseFloat(item.preco).toFixed(2).replace('.', ',')}
                            </div>
                            <div class="col-md-2 text-right">
                                <strong>R$ ${subtotal.toFixed(2).replace('.', ',')}</strong>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += `
                    </div>
                    <div class="pedido-total">
                        Total: R$ ${parseFloat(pedido.total).toFixed(2).replace('.', ',')}
                    </div>
                </div>
            `;
        });

        $('#listaPedidos').html(html);
    }

    // Formatar data brasileira
    function formatarData(dataString) {
        let data = new Date(dataString);
        let dia = String(data.getDate()).padStart(2, '0');
        let mes = String(data.getMonth() + 1).padStart(2, '0');
        let ano = data.getFullYear();
        let horas = String(data.getHours()).padStart(2, '0');
        let minutos = String(data.getMinutes()).padStart(2, '0');
        
        return `${dia}/${mes}/${ano} às ${horas}:${minutos}`;
    }
</script>