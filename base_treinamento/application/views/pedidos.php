<style>
        .subtotal-linha, .total-linha, .cupom-linha {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
    }

    .cupom-linha {
        color: #27ae60;
        font-weight: 500;
        border-top: 1px dashed #ddd;
        border-bottom: 1px dashed #ddd;
        margin: 5px 0;
    }

    .total-linha {
        font-size: 20px;
        font-weight: bold;
        color: #27ae60;
        border-top: 2px solid #eee;
        padding-top: 10px;
        margin-top: 5px;
    }
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

    // Formatar data
    function formatarData(dataString) {
        let data = new Date(dataString);
        let dia = String(data.getDate()).padStart(2, '0');
        let mes = String(data.getMonth() + 1).padStart(2, '0');
        let ano = data.getFullYear();
        let horas = String(data.getHours()).padStart(2, '0');
        let minutos = String(data.getMinutes()).padStart(2, '0');
        return `${dia}/${mes}/${ano} às ${horas}:${minutos}`;
    }

    // Exibir pedidos na tela
    function exibirPedidos(pedidos) {
        if (pedidos.length === 0) {
            $('#listaPedidos').html(`
                <div class="text-center" style="padding: 60px 20px;">
                    <h2 style="color: #999;">
                        <span class="glyphicon glyphicon-inbox" style="font-size: 60px; display: block; margin-bottom: 20px;"></span>
                        Você ainda não fez nenhum pedido
                    </h2>
                    <p style="font-size: 16px; color: #666; margin: 20px 0;">
                        Comece adicionando produtos ao carrinho!
                    </p>
                    <a href="<?=base_url()?>" class="btn btn-primary btn-lg">
                        <span class="glyphicon glyphicon-shopping-cart"></span> Ver Produtos
                    </a>
                </div>
            `);
            return;
        }

        let html = '';
        pedidos.forEach(function(pedido) {
            let status = 'success';
            let status_texto = 'Finalizado';
            
            html += `
                <div class="pedido-card">
                    <div class="pedido-header">
                        <div>
                            <strong>Pedido #${pedido.id_venda}</strong><br>
                            <small><span class="glyphicon glyphicon-calendar"></span> ${formatarData(pedido.data_venda)}</small>
                        </div>
                        <div>
                            <span class="label label-${status}">${status_texto}</span>
                        </div>
                    </div>
                    
                    <div class="pedido-itens">
                        <h4>Itens do Pedido:</h4>
            `;
            
            pedido.itens.forEach(function(item) {
                let subtotal = item.quantidade * item.preco;
                html += `
                    <div class="pedido-item">
                        <div>
                            <strong>${item.nome_produto}</strong><br>
                            <small class="text-muted">Loja: ${item.nome_loja}</small><br>
                            <small>R$ ${parseFloat(item.preco).toFixed(2).replace('.', ',')} × ${item.quantidade}</small>
                        </div>
                        <div class="text-right">
                            <strong>R$ ${subtotal.toFixed(2).replace('.', ',')}</strong>
                        </div>
                    </div>
                `;
            });
            
            html += `</div>`;
            
            // Mostrar resumo do pedido
            html += `<div class="pedido-total">`;
            
            // Subtotal
            html += `
                <div class="subtotal-linha">
                    <span>Subtotal:</span>
                    <span>R$ ${parseFloat(pedido.subtotal).toFixed(2).replace('.', ',')}</span>
                </div>
            `;
            
            // Cupom (se foi usado)
            if (pedido.valor_desconto > 0 && pedido.cupom) {
                let cupom_texto = pedido.cupom.tipo == '%' 
                    ? pedido.cupom.desconto + '%' 
                    : 'R$ ' + parseFloat(pedido.cupom.desconto).toFixed(2).replace('.', ',');
                
                html += `
                    <div class="cupom-linha">
                        <span>
                            <span class="glyphicon glyphicon-tags"></span> 
                            Cupom: <strong>${pedido.cupom.nome}</strong> (${cupom_texto})
                        </span>
                        <span>-R$ ${parseFloat(pedido.valor_desconto).toFixed(2).replace('.', ',')}</span>
                    </div>
                `;
            }
            
            // Total
            html += `
                <div class="total-linha">
                    <span>Total:</span>
                    <span>R$ ${parseFloat(pedido.total).toFixed(2).replace('.', ',')}</span>
                </div>
            `;
            
            html += `</div></div>`;
        });

        $('#listaPedidos').html(html);
    }
</script>