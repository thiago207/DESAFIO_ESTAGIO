<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cupons extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();
		$this->load->library('template');
		$this->load->model('cupom_model');
		
		// Verificar se o usuário está logado e é do tipo Loja (2)
		if (!$this->session->userdata('id_usuario') || $this->session->userdata('tipo_acesso') != '2') {
			redirect(base_url());
		}
	}

	/**
	 * Página principal - listar cupons
	 */
	public function index(){
		$dados = [
			'title' => 'Meus Cupons'
		];
		$this->template->load('gerenciarCupons', $dados);
	}

	/**
	 * Página de cadastro
	 */
	public function cadastrar(){
		$dados = [
			'title' => 'Cadastrar Cupom'
		];
		$this->template->load('cadastrarCupom', $dados);
	}

	/**
	 * AJAX - Listar cupons da loja
	 */
	public function ajax_listar(){
		$id_usuario_loja = $this->session->userdata('id_usuario');
		$cupons = $this->cupom_model->listarCuponsLoja($id_usuario_loja);
		echo json_encode($cupons);
	}

	/**
	 * AJAX - Cadastrar cupom
	 */
	public function ajax_cadastrar(){
		$dados = $this->input->post();
		$resultado['sucesso'] = false;
		$resultado['mensagem'] = "";

		// Validações básicas
		if (empty($dados['nome'])) {
			$resultado['mensagem'] = "Código do cupom é obrigatório";
		} elseif (empty($dados['desconto']) || $dados['desconto'] <= 0) {
			$resultado['mensagem'] = "Valor do desconto inválido";
		} elseif (empty($dados['tipo'])) {
			$resultado['mensagem'] = "Tipo de desconto é obrigatório";
		} elseif (empty($dados['valor_minimo']) || $dados['valor_minimo'] < 0) {
			$resultado['mensagem'] = "Valor mínimo inválido";
		} elseif (empty($dados['estoque']) || $dados['estoque'] <= 0) {
			$resultado['mensagem'] = "Quantidade de usos inválida";
		} else {
			// Verificar se código já existe para esta loja
			$cupom_existente = $this->cupom_model->buscarCupomPorCodigo($dados['nome']);
			
			if ($cupom_existente && $cupom_existente['id_usuario_loja'] == $this->session->userdata('id_usuario')) {
				$resultado['mensagem'] = "Já existe um cupom com este código";
			} else {
				// Processar data de validade
				if (!empty($dados['data_validade'])) {
					$dados['data_validade'] = date('Y-m-d H:i:s', strtotime($dados['data_validade']));
				} else {
					$dados['data_validade'] = null;
				}
				
				// Processar checkbox
				$dados['um_uso_por_cliente'] = isset($dados['um_uso_por_cliente']) ? 1 : 0;
				$dados['ativo'] = 1;
				
				$resultado['sucesso'] = $this->cupom_model->cadastrarCupom($dados);
				
				if ($resultado['sucesso']) {
					$resultado['mensagem'] = "Cupom cadastrado com sucesso!";
				} else {
					$resultado['mensagem'] = "Erro ao cadastrar cupom";
				}
			}
		}

		echo json_encode($resultado);
	}

	/**
	 * AJAX - Buscar cupom específico
	 */
	public function ajax_buscar(){
		$id_cupom = $this->input->post('id_cupom');
		$cupom = $this->cupom_model->buscarCupomPorId($id_cupom);
		
		// Verificar se o cupom pertence à loja logada
		if ($cupom && $cupom['id_usuario_loja'] == $this->session->userdata('id_usuario')) {
			echo json_encode($cupom);
		} else {
			echo json_encode(null);
		}
	}

	/**
	 * AJAX - Editar cupom
	 */
	public function ajax_editar(){
		$dados = $this->input->post();
		$id_cupom = $dados['id_cupom'];
		unset($dados['id_cupom']); // Remover o ID dos dados

		$resultado['sucesso'] = false;
		$resultado['mensagem'] = "";

		// Validações básicas
		if (empty($dados['nome'])) {
			$resultado['mensagem'] = "Código do cupom é obrigatório";
		} elseif (empty($dados['desconto']) || $dados['desconto'] <= 0) {
			$resultado['mensagem'] = "Valor do desconto inválido";
		} elseif (empty($dados['tipo'])) {
			$resultado['mensagem'] = "Tipo de desconto é obrigatório";
		} elseif (empty($dados['valor_minimo']) || $dados['valor_minimo'] < 0) {
			$resultado['mensagem'] = "Valor mínimo inválido";
		} elseif (empty($dados['estoque']) || $dados['estoque'] <= 0) {
			$resultado['mensagem'] = "Quantidade de usos inválida";
		} else {
			// Processar data de validade
			if (!empty($dados['data_validade'])) {
				$dados['data_validade'] = date('Y-m-d H:i:s', strtotime($dados['data_validade']));
			} else {
				$dados['data_validade'] = null;
			}
			
			// Processar checkbox
			$dados['um_uso_por_cliente'] = isset($dados['um_uso_por_cliente']) ? 1 : 0;
			
			$resultado['sucesso'] = $this->cupom_model->editarCupom($id_cupom, $dados);
			
			if ($resultado['sucesso']) {
				$resultado['mensagem'] = "Cupom atualizado com sucesso!";
			} else {
				$resultado['mensagem'] = "Nenhuma alteração foi feita";
			}
		}

		echo json_encode($resultado);
	}

	/**
	 * AJAX - Deletar cupom
	 */
	public function ajax_deletar(){
		$id_cupom = $this->input->post('id_cupom');
		$resultado['sucesso'] = false;
		$resultado['mensagem'] = "";

		// Verificar se cupom foi usado
		if ($this->cupom_model->cupomFoiUsado($id_cupom)) {
			$resultado['mensagem'] = "Não é possível deletar este cupom pois ele já foi utilizado";
		} else {
			$resultado['sucesso'] = $this->cupom_model->deletarCupom($id_cupom);
			
			if ($resultado['sucesso']) {
				$resultado['mensagem'] = "Cupom deletado com sucesso!";
			} else {
				$resultado['mensagem'] = "Erro ao deletar cupom";
			}
		}

		echo json_encode($resultado);
	}

	/**
	 * AJAX - Ativar/Desativar cupom
	 */
	public function ajax_ativarDesativar(){
		$id_cupom = $this->input->post('id_cupom');
		$ativo = $this->input->post('ativo');
		
		$resultado['sucesso'] = $this->cupom_model->ativarDesativar($id_cupom, $ativo);
		
		if ($resultado['sucesso']) {
			$resultado['mensagem'] = $ativo == 1 ? "Cupom ativado!" : "Cupom desativado!";
		} else {
			$resultado['mensagem'] = "Erro ao atualizar status";
		}

		echo json_encode($resultado);
	}
}