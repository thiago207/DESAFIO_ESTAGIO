<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loja extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->library('template');
		
		// Verificar se o usuário está logado e é do tipo Loja (2)
		if (!$this->session->userdata('id_usuario') || $this->session->userdata('tipo_acesso') != '2') {
			redirect(base_url());
		}
	}

	public function index(){
		$dados = [
			'title' => 'Meus Produtos'
		];
		$this->template->load('lojaPaginaPrincipal', $dados);
	}

	/**
	 * Página de vendas
	 */
	public function vendas(){
		$dados = [
			'title' => 'Minhas Vendas'
		];
		$this->template->load('vendas', $dados);
	}

	/**
	 * AJAX - Listar vendas da loja
	 */
	public function ajax_listarVendas(){
		$id_usuario_loja = $this->session->userdata('id_usuario');
		
		// Buscar todas as vendas de produtos desta loja
		$this->db->select('v.id_venda, v.data_venda, c.id_carrinho, u.nome_usuario as nome_cliente');
		$this->db->from('venda v');
		$this->db->join('carrinho c', 'c.id_carrinho = v.id_carrinho');
		$this->db->join('usuario u', 'u.id_usuario = c.id_usuario');
		$this->db->join('carrinho_item ci', 'ci.id_carrinho = c.id_carrinho');
		$this->db->join('produto p', 'p.id_produto = ci.id_produto');
		$this->db->where('p.id_usuario_loja', $id_usuario_loja);
		$this->db->group_by('v.id_venda');
		$this->db->order_by('v.data_venda', 'DESC');
		$vendas = $this->db->get()->result_array();
		
		// Para cada venda, buscar apenas os itens desta loja
		$vendas_processadas = [];
		foreach ($vendas as $venda) {
			// Buscar itens do carrinho desta venda que pertencem a esta loja
			$this->db->select('ci.quantidade, p.nome as nome_produto, p.preco');
			$this->db->from('carrinho_item ci');
			$this->db->join('produto p', 'p.id_produto = ci.id_produto');
			$this->db->where('ci.id_carrinho', $venda['id_carrinho']);
			$this->db->where('p.id_usuario_loja', $id_usuario_loja);
			$itens = $this->db->get()->result_array();
			
			// Calcular total
			$total = 0;
			foreach ($itens as $item) {
				$total += $item['quantidade'] * $item['preco'];
			}
			
			$vendas_processadas[] = [
				'id_venda' => $venda['id_venda'],
				'data_venda' => $venda['data_venda'],
				'nome_cliente' => $venda['nome_cliente'],
				'itens' => $itens,
				'total' => $total
			];
		}
		
		echo json_encode($vendas_processadas);
	}
}