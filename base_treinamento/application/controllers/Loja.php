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
    
    $this->db->select('v.id_venda, v.data_venda, v.valor_desconto, v.id_cupom, c.id_carrinho, u.nome_usuario as nome_cliente');
    $this->db->from('venda v');
    $this->db->join('carrinho c', 'c.id_carrinho = v.id_carrinho');
    $this->db->join('usuario u', 'u.id_usuario = c.id_usuario');
    
    // Filtrar vendas de produtos desta loja
    $this->db->where('v.id_carrinho IN (
        SELECT DISTINCT ci.id_carrinho 
        FROM carrinho_item ci 
        JOIN produto p ON p.id_produto = ci.id_produto 
        WHERE p.id_usuario_loja = ' . $id_usuario_loja . '
    )', NULL, FALSE);
    
    $this->db->order_by('v.data_venda', 'DESC');
    $vendas = $this->db->get()->result_array();
    
    $vendas_formatadas = [];
    foreach ($vendas as $venda) {
        // Buscar produtos vendidos DESTA LOJA
        $this->db->select('ci.quantidade, p.nome as nome_produto, p.preco');
        $this->db->from('carrinho_item ci');
        $this->db->join('produto p', 'p.id_produto = ci.id_produto');
        $this->db->where('ci.id_carrinho', $venda['id_carrinho']);
        $this->db->where('p.id_usuario_loja', $id_usuario_loja);
        $produtos = $this->db->get()->result_array();
        
        // Calcular subtotal
        $subtotal = 0;
        foreach ($produtos as $produto) {
            $subtotal += $produto['quantidade'] * $produto['preco'];
        }
        
        // Buscar informações do cupom (se foi usado)
        $cupom_info = null;
        if ($venda['id_cupom']) {
            $this->db->select('nome, tipo, desconto');
            $this->db->from('cupom');
            $this->db->where('id_cupom', $venda['id_cupom']);
            $cupom_info = $this->db->get()->row_array();
        }
        
        $valor_desconto = $venda['valor_desconto'] ?? 0;
        $total = $subtotal - $valor_desconto;
        
        $vendas_formatadas[] = [
            'id_venda' => $venda['id_venda'],
            'data_venda' => $venda['data_venda'],
            'nome_cliente' => $venda['nome_cliente'],
            'produtos' => $produtos,
            'subtotal' => $subtotal,
            'valor_desconto' => $valor_desconto,
            'cupom' => $cupom_info,
            'total' => $total
        ];
    }
    
    echo json_encode($vendas_formatadas);
}
}