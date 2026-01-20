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
	 * Página de pedidos
	 */
	public function pedidos(){
		$dados = [
			'title' => 'Meus Pedidos'
		];
		$this->template->load('pedidos', $dados);
	}

	/**
	 * AJAX - Listar lojas
	 */
	public function ajax_listarLojas(){
		$this->db->select('id_usuario, nome_usuario');
		$this->db->from('usuario');
		$this->db->where('tipo_acesso', '2'); // Apenas lojas
		$this->db->order_by('nome_usuario', 'ASC');
		$lojas = $this->db->get()->result_array();
		echo json_encode($lojas);
	}

	/**
	 * AJAX - Listar categorias
	 */
	public function ajax_listarCategorias(){
		$this->db->select('id_categoria, nome');
		$this->db->from('categoria');
		$this->db->order_by('nome', 'ASC');
		$categorias = $this->db->get()->result_array();
		echo json_encode($categorias);
	}

	/**
	 * AJAX - Listar pedidos do cliente
	 */
	public function ajax_listarPedidos(){
		$id_usuario = $this->session->userdata('id_usuario');
		
		$this->db->select('v.id_venda, v.data_venda, c.id_carrinho');
		$this->db->from('venda v');
		$this->db->join('carrinho c', 'c.id_carrinho = v.id_carrinho');
		$this->db->where('c.id_usuario', $id_usuario);
		$this->db->order_by('v.data_venda', 'DESC');
		$vendas = $this->db->get()->result_array();
		
		$pedidos = [];
		foreach ($vendas as $venda) {
			$this->db->select('ci.quantidade, p.nome as nome_produto, p.preco, u.nome_usuario as nome_loja');
			$this->db->from('carrinho_item ci');
			$this->db->join('produto p', 'p.id_produto = ci.id_produto');
			$this->db->join('usuario u', 'u.id_usuario = p.id_usuario_loja');
			$this->db->where('ci.id_carrinho', $venda['id_carrinho']);
			$itens = $this->db->get()->result_array();
			
			$total = 0;
			foreach ($itens as $item) {
				$total += $item['quantidade'] * $item['preco'];
			}
			
			$pedidos[] = [
				'id_venda' => $venda['id_venda'],
				'data_venda' => $venda['data_venda'],
				'itens' => $itens,
				'total' => $total
			];
		}
		
		echo json_encode($pedidos);
	}

	/**
	 * AJAX - Listar todos os produtos disponíveis COM FILTROS
	 */
	public function ajax_listarProdutos(){
		// Capturar filtros
		$filtros = [
			'nome' => $this->input->get('nome'),
			'id_loja' => $this->input->get('id_loja'),
			'id_categoria' => $this->input->get('id_categoria'),
			'preco_min' => $this->input->get('preco_min'),
			'preco_max' => $this->input->get('preco_max'),
			'apenas_estoque' => $this->input->get('apenas_estoque')
		];

		$produtos = $this->produtos_model->listarProdutosDisponiveisComFiltros($filtros);
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

		if (!$id_produto || !$quantidade || $quantidade <= 0) {
			$resultado['mensagem'] = "Dados inválidos";
			echo json_encode($resultado);
			return;
		}

		$produto = $this->produtos_model->buscarProdutoPorId($id_produto);
		if (!$produto || $produto['estoque'] < $quantidade) {
			$resultado['mensagem'] = "Estoque insuficiente";
			echo json_encode($resultado);
			return;
		}

		$id_carrinho = $this->carrinho_model->buscarOuCriarCarrinho($id_usuario);
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
		
		echo json_encode([
			'itens' => $itens,
			'total' => $total
		]);
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
		echo json_encode($resultado);
	}
}