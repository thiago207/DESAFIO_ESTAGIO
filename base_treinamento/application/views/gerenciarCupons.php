<style>
    .cupons-container {
        max-width: 1400px;
        margin: 20px auto;
    }
    .cupom-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 15px;
        background-color: white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    .cupom-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .cupom-card.inativo {
        opacity: 0.6;
        background-color: #f9f9f9;
    }
    .cupom-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    .cupom-codigo {
        font-size: 24px;
        font-weight: bold;
        color: #2c3e50;
        font-family: 'Courier New', monospace;
    }
    .cupom-info {
        margin: 10px 0;
        color: #666;
    }
    .cupom-acoes {
        margin-top: 10px;
    }
    .label-desconto {
        font-size: 18px;
        padding: 8px 15px;
    }
</style>

<div class="container cupons-container">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h1><span class="glyphicon glyphicon-tags"></span> Meus Cupons</h1>
        <a href="<?=base_url('cupons/cadastrar')?>" class="btn btn-primary btn-lg">
            <span class="glyphicon glyphicon-plus"></span> Criar Novo Cupom
        </a>
    </div>

    <div id="alerta"></div>

    <!-- PAINEL DE EDIÇÃO -->
    <div class="panel panel-warning" id="painelEdicao" style="display:none;">
        <div class="panel-heading">
            <h3 class="panel-title">
                <span class="glyphicon glyphicon-pencil"></span> Editar Cupom
            </h3>
        </div>
        <div class="panel-body">
            <form id="formEdicao">
                <input type="hidden" id="id_cupom" value="">

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="nome_edit"><span class="glyphicon glyphicon-tag"></span> Código do Cupom *</label>
                            <input class="form-control" placeholder="Ex: CUPOM15" id="nome_edit" type="text" maxlength="30" required style="text-transform: uppercase;">
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label><span class="glyphicon glyphicon-usd"></span> Tipo de Desconto *</label>
                            <div>
                                <label class="radio-inline">
                                    <input type="radio" name="tipo_edit" value="%" checked> Percentual (%)
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="tipo_edit" value="$"> Valor Fixo (R$)
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="desconto_edit"><span class="glyphicon glyphicon-gift"></span> Valor do Desconto *</label>
                            <input class="form-control" placeholder="Ex: 15 ou 50.00" id="desconto_edit" type="number" step="0.01" min="0.01" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="valor_minimo_edit"><span class="glyphicon glyphicon-shopping-cart"></span> Valor Mínimo (R$) *</label>
                            <input class="form-control" placeholder="Ex: 100.00" id="valor_minimo_edit" type="number" step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="estoque_edit"><span class="glyphicon glyphicon-list-alt"></span> Quantidade de Usos *</label>
                            <input class="form-control" placeholder="Ex: 50" id="estoque_edit" type="number" min="1" required>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="data_validade_edit"><span class="glyphicon glyphicon-calendar"></span> Data de Validade</label>
                            <input class="form-control" id="data_validade_edit" type="datetime-local">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="um_uso_por_cliente_edit"> Um uso por cliente
                            </label>
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

    <!-- LISTA DE CUPONS -->
    <div class="panel panel-default">
        <div class="panel-body">
            <div id="listaCupons">
                <div class="text-center">
                    <i class="glyphicon glyphicon-refresh glyphicon-spin"></i> Carregando cupons...
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Carregar cupons ao iniciar
    $(document).ready(function() {
        carregarCupons();
    });

    // Carregar lista de cupons
    function carregarCupons() {
        $.ajax({
            url: "<?=base_url('cupons/ajax_listar')?>",
            type: "GET",
            dataType: "json",
            cache: false,
            success: function(cupons) {
                exibirCupons(cupons);
            },
            error: function() {
                $('#listaCupons').html('<div class="alert alert-danger">Erro ao carregar cupons</div>');
            }
        });
    }

    // Exibir cupons na tela
    function exibirCupons(cupons) {
        if (cupons.length === 0) {
            $('#listaCupons').html(`
                <div class="text-center" style="padding: 60px 20px;">
                    <h2 style="color: #999;">
                        <span class="glyphicon glyphicon-tags" style="font-size: 60px; display: block; margin-bottom: 20px;"></span>
                        Nenhum cupom cadastrado ainda
                    </h2>
                    <p style="font-size: 16px; color: #666; margin: 20px 0;">
                        Crie cupons de desconto para atrair mais clientes!
                    </p>
                    <a href="<?=base_url('cupons/cadastrar')?>" class="btn btn-primary btn-lg">
                        <span class="glyphicon glyphicon-plus"></span> Criar Primeiro Cupom
                    </a>
                </div>
            `);
            return;
        }

        let html = '';
        cupons.forEach(function(cupom) {
            let desconto_texto = cupom.tipo == '%' ? cupom.desconto + '%' : 'R$ ' + parseFloat(cupom.desconto).toFixed(2);
            let disponivel = cupom.estoque - cupom.usados;
            let status_class = cupom.ativo == 1 ? '' : 'inativo';
            let status_label = cupom.ativo == 1 ? 'success' : 'default';
            let status_texto = cupom.ativo == 1 ? 'Ativo' : 'Inativo';
            let btn_status_class = cupom.ativo == 1 ? 'warning' : 'success';
            let btn_status_texto = cupom.ativo == 1 ? 'Desativar' : 'Ativar';
            let btn_status_icon = cupom.ativo == 1 ? 'ban-circle' : 'ok-circle';

            html += `
                <div class="cupom-card ${status_class}" id="cupom_${cupom.id_cupom}">
                    <div class="cupom-header">
                        <div>
                            <div class="cupom-codigo">${cupom.nome}</div>
                            <span class="label label-${status_label}">${status_texto}</span>
                        </div>
                        <div>
                            <span class="label label-primary label-desconto">
                                <span class="glyphicon glyphicon-gift"></span> ${desconto_texto}
                            </span>
                        </div>
                    </div>
                    
                    <div class="cupom-info">
                        <div><strong>Valor mínimo:</strong> R$ ${parseFloat(cupom.valor_minimo).toFixed(2).replace('.', ',')}</div>
                        <div><strong>Usos:</strong> ${cupom.usados} / ${cupom.estoque} 
                            <span class="label label-info">${disponivel} disponível${disponivel != 1 ? 'is' : ''}</span>
                        </div>
                        ${cupom.data_validade ? '<div><strong>Validade:</strong> ' + formatarData(cupom.data_validade) + '</div>' : ''}
                        ${cupom.um_uso_por_cliente == 1 ? '<div><span class="glyphicon glyphicon-user"></span> <em>Um uso por cliente</em></div>' : ''}
                    </div>
                    
                    <div class="cupom-acoes">
                        <button class="btn btn-sm btn-primary" onclick="editarCupom(${cupom.id_cupom})">
                            <span class="glyphicon glyphicon-pencil"></span> Editar
                        </button>
                        <button class="btn btn-sm btn-${btn_status_class}" onclick="ativarDesativar(${cupom.id_cupom}, ${cupom.ativo == 1 ? 0 : 1})">
                            <span class="glyphicon glyphicon-${btn_status_icon}"></span> ${btn_status_texto}
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="confirmarDeletar(${cupom.id_cupom}, '${cupom.nome.replace(/'/g, "\\'")}')">
                            <span class="glyphicon glyphicon-trash"></span> Deletar
                        </button>
                    </div>
                </div>
            `;
        });

        $('#listaCupons').html(html);
    }

    // Formatar data
    function formatarData(dataString) {
        let data = new Date(dataString);
        let dia = String(data.getDate()).padStart(2, '0');
        let mes = String(data.getMonth() + 1).padStart(2, '0');
        let ano = data.getFullYear();
        return `${dia}/${mes}/${ano}`;
    }

    // Editar cupom
    function editarCupom(id_cupom) {
        $.ajax({
            url: "<?=base_url('cupons/ajax_buscar')?>",
            type: "POST",
            dataType: "json",
            data: { id_cupom: id_cupom },
            cache: false,
            success: function(cupom) {
                if (cupom) {
                    $('#id_cupom').val(cupom.id_cupom);
                    $('#nome_edit').val(cupom.nome);
                    $('input[name="tipo_edit"][value="' + cupom.tipo + '"]').prop('checked', true);
                    $('#desconto_edit').val(cupom.desconto);
                    $('#valor_minimo_edit').val(cupom.valor_minimo);
                    $('#estoque_edit').val(cupom.estoque);
                    
                    if (cupom.data_validade) {
                        let data = new Date(cupom.data_validade);
                        let dataFormatada = data.toISOString().slice(0, 16);
                        $('#data_validade_edit').val(dataFormatada);
                    } else {
                        $('#data_validade_edit').val('');
                    }
                    
                    $('#um_uso_por_cliente_edit').prop('checked', cupom.um_uso_por_cliente == 1);
                    
                    $('#painelEdicao').slideDown();
                    $('html, body').animate({ scrollTop: $('#painelEdicao').offset().top - 20 }, 500);
                }
            }
        });
    }

    // Cancelar edição
    function cancelarEdicao() {
        $('#formEdicao')[0].reset();
        $('#id_cupom').val('');
        $('#painelEdicao').slideUp();
    }

    // Submit do formulário de edição
    $('#formEdicao').on('submit', function(e) {
        e.preventDefault();
        
        let dados = {
            id_cupom: $('#id_cupom').val(),
            nome: $('#nome_edit').val().toUpperCase(),
            tipo: $('input[name="tipo_edit"]:checked').val(),
            desconto: $('#desconto_edit').val(),
            valor_minimo: $('#valor_minimo_edit').val(),
            estoque: $('#estoque_edit').val(),
            data_validade: $('#data_validade_edit').val(),
            um_uso_por_cliente: $('#um_uso_por_cliente_edit').is(':checked') ? 1 : 0
        };

        $.ajax({
            url: "<?=base_url('cupons/ajax_editar')?>",
            type: "POST",
            dataType: "json",
            data: dados,
            cache: false,
            success: function(data) {
                if (data.sucesso) {
                    exibirAviso(data.mensagem, 'alerta', 'SUCESSO');
                    cancelarEdicao();
                    carregarCupons();
                } else {
                    exibirAviso(data.mensagem, 'alerta', 'ERRO');
                }
            }
        });
    });

    // Ativar/Desativar cupom
    function ativarDesativar(id_cupom, ativo) {
        $.ajax({
            url: "<?=base_url('cupons/ajax_ativarDesativar')?>",
            type: "POST",
            dataType: "json",
            data: { id_cupom: id_cupom, ativo: ativo },
            cache: false,
            success: function(data) {
                if (data.sucesso) {
                    exibirAviso(data.mensagem, 'alerta', 'SUCESSO');
                    carregarCupons();
                } else {
                    exibirAviso(data.mensagem, 'alerta', 'ERRO');
                }
            }
        });
    }

    // Confirmar deleção
    function confirmarDeletar(id_cupom, nome_cupom) {
        if (confirm('Tem certeza que deseja deletar o cupom "' + nome_cupom + '"?\n\nObs: Cupons já utilizados não podem ser deletados.')) {
            deletarCupom(id_cupom);
        }
    }

    // Deletar cupom
    function deletarCupom(id_cupom) {
        $.ajax({
            url: "<?=base_url('cupons/ajax_deletar')?>",
            type: "POST",
            dataType: "json",
            data: { id_cupom: id_cupom },
            cache: false,
            success: function(data) {
                if (data.sucesso) {
                    exibirAviso(data.mensagem, 'alerta', 'SUCESSO');
                    $('#cupom_' + id_cupom).fadeOut(400, function() {
                        $(this).remove();
                        if ($('.cupom-card').length === 0) {
                            carregarCupons();
                        }
                    });
                } else {
                    exibirAviso(data.mensagem, 'alerta', 'ERRO');
                }
            }
        });
    }
</script>