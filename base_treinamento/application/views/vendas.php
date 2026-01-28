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
                <div class="text-center" style="padding: 60px 20px;">
                    <h2 style="color: #999;">
                        <span class="glyphicon glyphicon-shopping-cart" style="font-size: 60px; display: block; margin-bottom: 20px;"></span>
                        Nenhuma venda realizada ainda
                    </h2>
                    <p style="font-size: 16px; color: #666; margin: 20px 0;">
                        Aguarde clientes comprarem seus produtos!
                    </p>
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
                            <strong>Venda #${venda.id_venda}</strong><br>
                            <small><span class="glyphicon glyphicon-calendar"></span> ${formatarData(venda.data_venda)}</small><br>
                            <small><span class="glyphicon glyphicon-user"></span> Cliente: ${venda.nome_cliente}</small>
                        </div>
                        <div>
                            <span class="label label-success">Finalizada</span>
                        </div>
                    </div>
                    
                    <div class="venda-produtos">
                        <h4>Produtos Vendidos:</h4>
            `;
            
            venda.produtos.forEach(function(produto) {
                let subtotal = produto.quantidade * produto.preco;
                html += `
                    <div class="venda-item">
                        <div>
                            <strong>${produto.nome_produto}</strong><br>
                            <small>R$ ${parseFloat(produto.preco).toFixed(2).replace('.', ',')} × ${produto.quantidade}</small>
                        </div>
                        <div class="text-right">
                            <strong>R$ ${subtotal.toFixed(2).replace('.', ',')}</strong>
                        </div>
                    </div>
                `;
            });
            
            html += `</div>`;
            
            // Mostrar resumo da venda
            html += `<div class="venda-total">`;
            
            // Subtotal
            html += `
                <div style="display: flex; justify-content: space-between; padding: 8px 0;">
                    <span>Subtotal:</span>
                    <span>R$ ${parseFloat(venda.subtotal).toFixed(2).replace('.', ',')}</span>
                </div>
            `;
            
            // Cupom (se foi usado)
            if (venda.valor_desconto > 0 && venda.cupom) {
                let cupom_texto = venda.cupom.tipo == '%' 
                    ? venda.cupom.desconto + '%' 
                    : 'R$ ' + parseFloat(venda.cupom.desconto).toFixed(2).replace('.', ',');
                
                html += `
                    <div style="display: flex; justify-content: space-between; padding: 8px 0; color: #27ae60; font-weight: 500; border-top: 1px dashed #ddd; border-bottom: 1px dashed #ddd; margin: 5px 0;">
                        <span>
                            <span class="glyphicon glyphicon-tags"></span> 
                            Cupom: <strong>${venda.cupom.nome}</strong> (${cupom_texto})
                        </span>
                        <span>-R$ ${parseFloat(venda.valor_desconto).toFixed(2).replace('.', ',')}</span>
                    </div>
                `;
            }
            
            // Total
            html += `
                <div style="display: flex; justify-content: space-between; font-size: 20px; font-weight: bold; color: #27ae60; border-top: 2px solid #eee; padding-top: 10px; margin-top: 5px;">
                    <span>Total da Venda:</span>
                    <span>R$ ${parseFloat(venda.total).toFixed(2).replace('.', ',')}</span>
                </div>
            `;
            
            html += `</div></div>`;
        });

        $('#listaVendas').html(html);
    }

    // Formatar data (adicione se não existir)
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