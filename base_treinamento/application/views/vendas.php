<style>
    .vendas-container {
        max-width: 1400px;
        margin: 20px auto;
    }
    .venda-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        background-color: white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .venda-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 2px solid #eee;
    }
    .venda-numero {
        font-size: 18px;
        font-weight: bold;
        color: #333;
    }
    .venda-data {
        color: #666;
        font-size: 14px;
    }
    .venda-cliente {
        color: #555;
        font-size: 14px;
        margin-top: 5px;
    }
    .venda-item {
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .venda-item:last-child {
        border-bottom: none;
    }
    .venda-total {
        font-size: 20px;
        font-weight: bold;
        color: #27ae60;
        text-align: right;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 2px solid #eee;
    }
</style>

<div class="container vendas-container">
    <div class="page-header">
        <h1><span class="glyphicon glyphicon-usd"></span> Minhas Vendas</h1>
    </div>

    <div id="alerta"></div>

    <!-- LISTA DE VENDAS -->
    <div id="listaVendas">
        <div class="text-center">
            <i class="glyphicon glyphicon-refresh glyphicon-spin"></i> Carregando vendas...
        </div>
    </div>
</div>

<script>
    // Carregar vendas ao iniciar
    $(document).ready(function() {
        carregarVendas();
    });

    // Carregar lista de vendas
    function carregarVendas() {
        $.ajax({
            url: "<?=base_url('loja/ajax_listarVendas')?>",
            type: "GET",
            dataType: "json",
            cache: false,
            success: function(vendas) {
                exibirVendas(vendas);
            },
            error: function() {
                $('#listaVendas').html('<div class="alert alert-danger">Erro ao carregar vendas</div>');
            }
        });
    }

    // Exibir vendas na tela
    function exibirVendas(vendas) {
        if (vendas.length === 0) {
            $('#listaVendas').html(`
                <div class="alert alert-info text-center">
                    <h3><span class="glyphicon glyphicon-info-sign"></span> Nenhuma venda realizada ainda</h3>
                    <p>Quando seus clientes finalizarem compras, as vendas aparecerão aqui!</p>
                </div>
            `);
            return;
        }

        let html = '';
        vendas.forEach(function(venda) {
            html += `
                <div class="venda-card">
                    <div class="venda-header">
                        <div>
                            <div class="venda-numero">
                                Venda #${venda.id_venda}
                            </div>
                            <div class="venda-data">
                                <span class="glyphicon glyphicon-calendar"></span> 
                                ${formatarData(venda.data_venda)}
                            </div>
                            <div class="venda-cliente">
                                <span class="glyphicon glyphicon-user"></span> 
                                Cliente: ${venda.nome_cliente}
                            </div>
                        </div>
                        <div>
                            <span class="label label-success">Finalizada</span>
                        </div>
                    </div>
                    
                    <div class="venda-itens">
                        <h4>Produtos Vendidos:</h4>
            `;

            // Listar itens da venda
            venda.itens.forEach(function(item) {
                let subtotal = item.quantidade * item.preco;
                html += `
                    <div class="venda-item">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>${item.nome_produto}</strong>
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
                    <div class="venda-total">
                        Total da Venda: R$ ${parseFloat(venda.total).toFixed(2).replace('.', ',')}
                    </div>
                </div>
            `;
        });

        $('#listaVendas').html(html);
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