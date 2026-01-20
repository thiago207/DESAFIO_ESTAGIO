<style>
    .panel-cadastro {
        max-width: 900px;
        margin: 40px auto;
    }
</style>

<div class="container panel-cadastro">
    <div class="page-header">
        <h1><span class="glyphicon glyphicon-plus"></span> Cadastrar Novo Produto</h1>
    </div>

    <div id="alerta"></div>

    <div class="panel panel-primary">
        <div class="panel-body">
            <form id="formCadastro">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nome"><span class="glyphicon glyphicon-tag"></span> Nome do Produto *</label>
                            <input class="form-control" placeholder="Nome do produto" id="nome" type="text" maxlength="255" required autofocus>
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
                            <textarea class="form-control" placeholder="Descrição do produto (opcional)" id="descricao" rows="4" maxlength="500"></textarea>
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <a href="<?=base_url('loja')?>" class="btn btn-default btn-lg">
                        <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                    </a>
                    <button type="submit" class="btn btn-success btn-lg" id="btnCadastrar">
                        <span class="glyphicon glyphicon-floppy-disk"></span> Cadastrar Produto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
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

    // Submit do formulário
    $('#formCadastro').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarDados()) {
            return;
        }

        let dados = {
            nome: $("#nome").val().trim(),
            custo: $("#custo").val() || 0,
            preco: $("#preco").val(),
            estoque: $("#estoque").val(),
            descricao: $("#descricao").val().trim()
        };

        // Desabilitar botão durante envio
        $('#btnCadastrar').prop('disabled', true).html('<i class="glyphicon glyphicon-refresh glyphicon-spin"></i> Cadastrando...');

        $.ajax({
            url: "<?=base_url('produtos/ajax_cadastrar')?>",
            type: "POST",
            dataType: "json",
            data: dados,
            cache: false,
            success: function(data) {
                if (data.sucesso) {
                    exibirAviso(data.mensagem, 'alerta', 'SUCESSO');
                    
                    // Limpar formulário
                    $('#formCadastro')[0].reset();
                    
                    // Reabilitar botão
                    $('#btnCadastrar').prop('disabled', false).html('<span class="glyphicon glyphicon-floppy-disk"></span> Cadastrar Produto');
                    
                    // Redirecionar após 2 segundos
                    setTimeout(function() {
                        window.location.href = "<?=base_url('loja')?>";
                    }, 2000);
                } else {
                    exibirAviso(data.mensagem, 'alerta', 'ERRO');
                    $('#btnCadastrar').prop('disabled', false).html('<span class="glyphicon glyphicon-floppy-disk"></span> Cadastrar Produto');
                }
            },
            error: function() {
                exibirAviso('Aconteceu um erro em nosso servidor', 'alerta');
                $('#btnCadastrar').prop('disabled', false).html('<span class="glyphicon glyphicon-floppy-disk"></span> Cadastrar Produto');
            }
        });
    });
</script>