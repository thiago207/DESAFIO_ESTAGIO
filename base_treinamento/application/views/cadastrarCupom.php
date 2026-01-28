<style>
    .cadastro-cupom-container {
        max-width: 900px;
        margin: 40px auto;
    }
    .info-box {
        background-color: #e7f3fe;
        border-left: 4px solid #2196F3;
        padding: 15px;
        margin-bottom: 20px;
    }
</style>

<div class="container cadastro-cupom-container">
    <div class="page-header">
        <h1><span class="glyphicon glyphicon-plus"></span> Criar Novo Cupom</h1>
    </div>

    <div id="alerta"></div>

    <div class="info-box">
        <strong><span class="glyphicon glyphicon-info-sign"></span> Dica:</strong>
        Cupons são uma ótima forma de atrair clientes e aumentar suas vendas!
        Configure o desconto, valor mínimo e quantidade de usos.
    </div>

    <div class="panel panel-primary">
        <div class="panel-body">
            <form id="formCadastro">
                
                <!-- CÓDIGO DO CUPOM -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nome"><span class="glyphicon glyphicon-tag"></span> Código do Cupom *</label>
                            <input class="form-control input-lg" placeholder="Ex: CUPOM15, PROMO50" id="nome" type="text" maxlength="30" required autofocus style="text-transform: uppercase;">
                            <small class="text-muted">Use letras maiúsculas e números. Será exibido aos clientes.</small>
                        </div>
                    </div>
                </div>

                <!-- TIPO E VALOR DO DESCONTO -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><span class="glyphicon glyphicon-usd"></span> Tipo de Desconto *</label>
                            <div>
                                <label class="radio-inline">
                                    <input type="radio" name="tipo" value="%" checked> <strong>Percentual (%)</strong> - Ex: 15% de desconto
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="tipo" value="$"> <strong>Valor Fixo (R$)</strong> - Ex: R$ 50 de desconto
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="desconto"><span class="glyphicon glyphicon-gift"></span> Valor do Desconto *</label>
                            <input class="form-control input-lg" placeholder="Ex: 15 ou 50.00" id="desconto" type="number" step="0.01" min="0.01" required>
                            <small class="text-muted" id="dica_desconto">Informe apenas o número (sem % ou R$)</small>
                        </div>
                    </div>
                </div>

                <!-- VALOR MÍNIMO E QUANTIDADE -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="valor_minimo"><span class="glyphicon glyphicon-shopping-cart"></span> Valor Mínimo do Carrinho (R$) *</label>
                            <input class="form-control input-lg" placeholder="Ex: 100.00" id="valor_minimo" type="number" step="0.01" min="0" required>
                            <small class="text-muted">Valor mínimo de compra para usar o cupom</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="estoque"><span class="glyphicon glyphicon-list-alt"></span> Quantidade de Usos *</label>
                            <input class="form-control input-lg" placeholder="Ex: 50" id="estoque" type="number" min="1" required>
                            <small class="text-muted">Quantas vezes o cupom pode ser usado no total</small>
                        </div>
                    </div>
                </div>

                <!-- DATA DE VALIDADE -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="data_validade"><span class="glyphicon glyphicon-calendar"></span> Data de Validade (Opcional)</label>
                            <input class="form-control" id="data_validade" type="datetime-local">
                            <small class="text-muted">Deixe em branco se não quiser limite de data</small>
                        </div>
                    </div>
                </div>

                <!-- RESTRIÇÕES -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Restrições:</label>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="um_uso_por_cliente"> 
                                    <strong>Um uso por cliente</strong> - Cada cliente só pode usar este cupom uma vez
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- EXEMPLO VISUAL -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <h4><span class="glyphicon glyphicon-eye-open"></span> Exemplo de como o cupom aparecerá:</h4>
                            <div style="background: white; padding: 15px; border-radius: 5px; margin-top: 10px; border: 2px dashed #2196F3;">
                                <div style="font-size: 24px; font-weight: bold; font-family: 'Courier New', monospace;" id="preview_codigo">CUPOM15</div>
                                <div style="margin-top: 5px;">
                                    <span class="label label-primary" style="font-size: 14px;" id="preview_desconto">15% de desconto</span>
                                </div>
                                <div style="margin-top: 10px; color: #666;">
                                    Válido para compras acima de <strong id="preview_minimo">R$ 100,00</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BOTÕES -->
                <div class="text-right">
                    <a href="<?=base_url('cupons')?>" class="btn btn-default btn-lg">
                        <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                    </a>
                    <button type="submit" class="btn btn-success btn-lg" id="btnCadastrar">
                        <span class="glyphicon glyphicon-floppy-disk"></span> Criar Cupom
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Preview do cupom em tempo real
    $('#nome').on('input', function() {
        let codigo = $(this).val().toUpperCase() || 'CUPOM15';
        $('#preview_codigo').text(codigo);
    });

    $('input[name="tipo"], #desconto').on('input change', function() {
        let tipo = $('input[name="tipo"]:checked').val();
        let valor = $('#desconto').val() || '15';
        
        if (tipo == '%') {
            $('#preview_desconto').text(valor + '% de desconto');
            $('#dica_desconto').text('Informe o percentual sem o símbolo %');
        } else {
            $('#preview_desconto').text('R$ ' + parseFloat(valor).toFixed(2).replace('.', ',') + ' de desconto');
            $('#dica_desconto').text('Informe o valor em reais');
        }
    });

    $('#valor_minimo').on('input', function() {
        let valor = $(this).val() || '100';
        $('#preview_minimo').text('R$ ' + parseFloat(valor).toFixed(2).replace('.', ','));
    });

    // Submit do formulário
    $('#formCadastro').on('submit', function(e) {
        e.preventDefault();
        
        // Validar código do cupom
        let codigo = $('#nome').val().toUpperCase();
        if (codigo.length < 3) {
            exibirAviso('O código do cupom deve ter no mínimo 3 caracteres', 'alerta', 'ERRO');
            return;
        }

        let dados = {
            nome: codigo,
            tipo: $('input[name="tipo"]:checked').val(),
            desconto: $('#desconto').val(),
            valor_minimo: $('#valor_minimo').val(),
            estoque: $('#estoque').val(),
            data_validade: $('#data_validade').val(),
            um_uso_por_cliente: $('#um_uso_por_cliente').is(':checked') ? 1 : 0
        };

        // Desabilitar botão durante envio
        $('#btnCadastrar').prop('disabled', true).html('<i class="glyphicon glyphicon-refresh glyphicon-spin"></i> Criando cupom...');

        $.ajax({
            url: "<?=base_url('cupons/ajax_cadastrar')?>",
            type: "POST",
            dataType: "json",
            data: dados,
            cache: false,
            success: function(data) {
                if (data.sucesso) {
                    exibirAviso(data.mensagem, 'alerta', 'SUCESSO');
                    
                    // Limpar formulário
                    $('#formCadastro')[0].reset();
                    $('#preview_codigo').text('CUPOM15');
                    $('#preview_desconto').text('15% de desconto');
                    $('#preview_minimo').text('R$ 100,00');
                    
                    // Reabilitar botão
                    $('#btnCadastrar').prop('disabled', false).html('<span class="glyphicon glyphicon-floppy-disk"></span> Criar Cupom');
                    
                    // Redirecionar após 2 segundos
                    setTimeout(function() {
                        window.location.href = "<?=base_url('cupons')?>";
                    }, 2000);
                } else {
                    exibirAviso(data.mensagem, 'alerta', 'ERRO');
                    $('#btnCadastrar').prop('disabled', false).html('<span class="glyphicon glyphicon-floppy-disk"></span> Criar Cupom');
                }
            },
            error: function() {
                exibirAviso('Aconteceu um erro em nosso servidor', 'alerta');
                $('#btnCadastrar').prop('disabled', false).html('<span class="glyphicon glyphicon-floppy-disk"></span> Criar Cupom');
            }
        });
    });
</script>