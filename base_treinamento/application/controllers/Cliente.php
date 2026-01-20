<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cliente extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->library('template');
		$this->load->model('produtos_model');
		$this->load->model('carrinho_model');
		
		// Verificar se o usuário está logado e é do tipo Cliente (1)
		if (!$this->session->userdata('id_usuario') || $this->session->userdata('tipo_acesso') != '1') {
			redirect(base_url());
		}
	}

	public function index(){
		$dados = [
			'title' => 'Produtos'
		];
		$this->template->load('clientePaginaPrincipal', $dados);
	}

	/**
	 * AJAX - Listar todos os produtos disponíveis
	 */
	public function ajax_listarProdutos(){
		$produtos = $this->produtos_model->listarProdutosDisponiveis();
		echo json_encode($produtos);
	}

	/**
	 * AJAX - Adicionar produto ao carrinho
	 */
	public function ajax_adicionarCarrinho(){
		$id_usuario = $this->session->userdata('id_usuario');
		$id_produto = $this->input->post('id_produto');
		$quantidade = $this->input->post('quantidade');

		$resultado['sucesso'] = false;
		$resultado['mensagem'] = "";

		// Validações
		if (!$id_produto || !$quantidade || $quantidade <= 0) {
			$resultado['mensagem'] = "Dados inválidos";
			echo json_encode($resultado);
			return;
		}

		// Verificar estoque
		$produto = $this->produtos_model->buscarProdutoPorId($id_produto);
		if (!$produto || $produto['estoque'] < $quantidade) {
			$resultado['mensagem'] = "Estoque insuficiente";
			echo json_encode($resultado);
			return;
		}

		// Buscar ou criar carrinho
		$id_carrinho = $this->carrinho_model->buscarOuCriarCarrinho($id_usuario);

		// Adicionar ao carrinho
		$resultado['sucesso'] = $this->carrinho_model->adicionarProduto($id_carrinho, $id_produto, $quantidade);
		
		if ($resultado['sucesso']) {
			$resultado['mensagem'] = "Produto adicionado ao carrinho!";
			$resultado['quantidade_carrinho'] = $this->carrinho_model->contarItensCarrinho($id_carrinho);
		} else {
			$resultado['mensagem'] = "Erro ao adicionar ao carrinho";
		}

		echo json_encode($resultado);
	}

	/**
	 * AJAX - Contar itens no carrinho
	 */
	public function ajax_contarCarrinho(){
		$id_usuario = $this->session->userdata('id_usuario');
		$id_carrinho = $this->carrinho_model->buscarOuCriarCarrinho($id_usuario);
		$quantidade = $this->carrinho_model->contarItensCarrinho($id_carrinho);
		echo json_encode(['quantidade' => $quantidade]);
	}

	/**
	 * AJAX - Listar itens do carrinho
	 */
	public function ajax_listarCarrinho(){
		$id_usuario = $this->session->userdata('id_usuario');
		$id_carrinho = $this->carrinho_model->buscarOuCriarCarrinho($id_usuario);
		$itens = $this->carrinho_model->listarItensCarrinho($id_carrinho);
		$total = $this->carrinho_model->calcularTotal($id_carrinho);
		
		$resultado = [
			'itens' => $itens,
			'total' => $total
		];
		
		echo json_encode($resultado);
	}

	/**
	 * AJAX - Atualizar quantidade de item
	 */
	public function ajax_atualizarQuantidade(){
		$id_carrinho_item = $this->input->post('id_carrinho_item');
		$quantidade = $this->input->post('quantidade');

		$resultado['sucesso'] = false;
		$resultado['mensagem'] = "";

		if ($quantidade <= 0) {
			$resultado['mensagem'] = "Quantidade inválida";
		} else {
			$resultado['sucesso'] = $this->carrinho_model->atualizarQuantidade($id_carrinho_item, $quantidade);
			
			if ($resultado['sucesso']) {
				$resultado['mensagem'] = "Quantidade atualizada!";
				
				// Retornar novo total
				$id_usuario = $this->session->userdata('id_usuario');
				$id_carrinho = $this->carrinho_model->buscarOuCriarCarrinho($id_usuario);
				$resultado['total'] = $this->carrinho_model->calcularTotal($id_carrinho);
			} else {
				$resultado['mensagem'] = "Erro ao atualizar";
			}
		}

		echo json_encode($resultado);
	}

	/**
	 * AJAX - Remover item do carrinho
	 */
	public function ajax_removerItem(){
		$id_carrinho_item = $this->input->post('id_carrinho_item');
		
		$resultado['sucesso'] = $this->carrinho_model->removerItem($id_carrinho_item);
		
		if ($resultado['sucesso']) {
			$resultado['mensagem'] = "Item removido do carrinho!";
			
			// Retornar quantidade atualizada e novo total
			$id_usuario = $this->session->userdata('id_usuario');
			$id_carrinho = $this->carrinho_model->buscarOuCriarCarrinho($id_usuario);
			$resultado['quantidade_carrinho'] = $this->carrinho_model->contarItensCarrinho($id_carrinho);
			$resultado['total'] = $this->carrinho_model->calcularTotal($id_carrinho);
		} else {
			$resultado['mensagem'] = "Erro ao remover item";
		}

		echo json_encode($resultado);
	}

	/**
	 * AJAX - Finalizar compra
	 */
	public function ajax_finalizarCompra(){
		$id_usuario = $this->session->userdata('id_usuario');
		$id_carrinho = $this->carrinho_model->buscarOuCriarCarrinho($id_usuario);
		
		$resultado = $this->carrinho_model->finalizarCompra($id_carrinho);
		
		if ($resultado['sucesso']) {
			// Limpar carrinho
			$this->carrinho_model->limparCarrinho($id_carrinho);
		}
		
		echo json_encode($resultado);
	}
}