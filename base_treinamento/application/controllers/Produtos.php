<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Produtos extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();
		$this->load->library('template');
		$this->load->model('produtos_model');
		
		// Verificar se o usuário está logado e é do tipo Loja (2)
		if (!$this->session->userdata('id_usuario') || $this->session->userdata('tipo_acesso') != '2') {
			redirect(base_url());
		}
	}

	/**
	 * Página principal - não é mais necessária pois tudo está em lojaPaginaPrincipal
	 */
	public function index(){
		redirect(base_url('loja'));
	}

	/**
	 * Página de cadastro de produto
	 */
	public function cadastrar(){
		$dados = [
			'title' => 'Cadastrar Produto'
		];
		$this->template->load('cadastrarProduto', $dados);
	}

	/**
	 * AJAX - Listar produtos da loja
	 */
	public function ajax_listar(){
		$id_usuario_loja = $this->session->userdata('id_usuario');
		$produtos = $this->produtos_model->listarProdutosLoja($id_usuario_loja);
		echo json_encode($produtos);
	}

	/**
	 * AJAX - Buscar um produto específico
	 */
	public function ajax_buscar(){
		$id_produto = $this->input->post('id_produto');
		$produto = $this->produtos_model->buscarProdutoPorId($id_produto);
		
		// Verificar se o produto pertence à loja logada
		if ($produto && $produto['id_usuario_loja'] == $this->session->userdata('id_usuario')) {
			echo json_encode($produto);
		} else {
			echo json_encode(null);
		}
	}

	/**
	 * AJAX - Cadastrar produto
	 */
	public function ajax_cadastrar(){
		$dados = $this->input->post();
		$resultado['sucesso'] = false;
		$resultado['mensagem'] = "";

		// Validações básicas
		if (empty($dados['nome'])) {
			$resultado['mensagem'] = "Nome do produto é obrigatório";
		} elseif (empty($dados['preco']) || $dados['preco'] <= 0) {
			$resultado['mensagem'] = "Preço inválido";
		} elseif (empty($dados['estoque']) || $dados['estoque'] < 0) {
			$resultado['mensagem'] = "Estoque inválido";
		} else {
			$resultado['sucesso'] = $this->produtos_model->cadastrarProduto($dados);
			if ($resultado['sucesso']) {
				$resultado['mensagem'] = "Produto cadastrado com sucesso!";
			} else {
				$resultado['mensagem'] = "Erro ao cadastrar produto";
			}
		}

		echo json_encode($resultado);
	}

	/**
	 * AJAX - Editar produto
	 */
	public function ajax_editar(){
		$dados = $this->input->post();
		$id_produto = $dados['id_produto'];
		unset($dados['id_produto']); // Remover o ID dos dados a serem atualizados

		$resultado['sucesso'] = false;
		$resultado['mensagem'] = "";

		// Validações básicas
		if (empty($dados['nome'])) {
			$resultado['mensagem'] = "Nome do produto é obrigatório";
		} elseif (empty($dados['preco']) || $dados['preco'] <= 0) {
			$resultado['mensagem'] = "Preço inválido";
		} elseif (!isset($dados['estoque']) || $dados['estoque'] < 0) {
			$resultado['mensagem'] = "Estoque inválido";
		} else {
			$resultado['sucesso'] = $this->produtos_model->editarProduto($id_produto, $dados);
			if ($resultado['sucesso']) {
				$resultado['mensagem'] = "Produto atualizado com sucesso!";
			} else {
				$resultado['mensagem'] = "Nenhuma alteração foi feita";
			}
		}

		echo json_encode($resultado);
	}

	/**
	 * AJAX - Deletar produto
	 */
	public function ajax_deletar(){
		$id_produto = $this->input->post('id_produto');
		$resultado['sucesso'] = false;
		$resultado['mensagem'] = "";

		// Verificar se o produto foi vendido
		if ($this->produtos_model->produtoFoiVendido($id_produto)) {
			$resultado['mensagem'] = "Não é possível deletar este produto pois ele já foi vendido";
		} else {
			$resultado['sucesso'] = $this->produtos_model->deletarProduto($id_produto);
			if ($resultado['sucesso']) {
				$resultado['mensagem'] = "Produto deletado com sucesso!";
			} else {
				$resultado['mensagem'] = "Erro ao deletar produto";
			}
		}

		echo json_encode($resultado);
	}
}