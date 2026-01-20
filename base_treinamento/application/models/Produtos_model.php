<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Produtos_model extends CI_Model {
	
	/**
	 * Listar produtos disponíveis COM FILTROS
	 */
	public function listarProdutosDisponiveisComFiltros($filtros = []){
		$this->db->select('p.id_produto, p.nome, p.descricao, p.preco, p.estoque, u.nome_usuario as nome_loja, u.id_usuario as id_loja');
		$this->db->from('produto p');
		$this->db->join('usuario u', 'u.id_usuario = p.id_usuario_loja');
		
		// Aplicar filtros dinamicamente
		
		// Filtro: Nome do produto
		if (!empty($filtros['nome'])) {
			$this->db->like('p.nome', $filtros['nome']);
		}
		
		// Filtro: Loja específica
		if (!empty($filtros['id_loja'])) {
			$this->db->where('p.id_usuario_loja', $filtros['id_loja']);
		}
		
		// Filtro: Categoria
		if (!empty($filtros['id_categoria'])) {
			$this->db->join('produto_categoria pc', 'pc.id_produto = p.id_produto');
			$this->db->where('pc.id_categoria', $filtros['id_categoria']);
		}
		
		// Filtro: Preço mínimo
		if (!empty($filtros['preco_min']) && $filtros['preco_min'] > 0) {
			$this->db->where('p.preco >=', $filtros['preco_min']);
		}
		
		// Filtro: Preço máximo
		if (!empty($filtros['preco_max']) && $filtros['preco_max'] > 0) {
			$this->db->where('p.preco <=', $filtros['preco_max']);
		}
		
		// Filtro: Apenas com estoque
		if (isset($filtros['apenas_estoque']) && $filtros['apenas_estoque'] == '1') {
			$this->db->where('p.estoque >', 0);
		}
		
		$this->db->order_by('p.nome', 'ASC');
		return $this->db->get()->result_array();
	}

	/**
	 * Listar produtos disponíveis (método antigo sem filtros)
	 */
	public function listarProdutosDisponiveis(){
		$this->db->select('p.id_produto, p.nome, p.descricao, p.preco, p.estoque, u.nome_usuario as nome_loja');
		$this->db->from('produto p');
		$this->db->join('usuario u', 'u.id_usuario = p.id_usuario_loja');
		$this->db->where('p.estoque >', 0);
		$this->db->order_by('p.nome', 'ASC');
		return $this->db->get()->result_array();
	}

	/**
	 * Listar produtos da loja COM FILTROS
	 */
	public function listarProdutosLojaComFiltros($id_usuario_loja, $filtros = []){
		$this->db->select('p.id_produto, p.nome, p.descricao, p.preco, p.custo, p.estoque');
		$this->db->from('produto p');
		$this->db->where('p.id_usuario_loja', $id_usuario_loja);
		
		// Filtro: Nome do produto
		if (!empty($filtros['nome'])) {
			$this->db->like('p.nome', $filtros['nome']);
		}
		
		// Filtro: Categoria
		if (!empty($filtros['id_categoria'])) {
			$this->db->join('produto_categoria pc', 'pc.id_produto = p.id_produto');
			$this->db->where('pc.id_categoria', $filtros['id_categoria']);
		}
		
		// Filtro: Preço mínimo
		if (!empty($filtros['preco_min']) && $filtros['preco_min'] > 0) {
			$this->db->where('p.preco >=', $filtros['preco_min']);
		}
		
		// Filtro: Preço máximo
		if (!empty($filtros['preco_max']) && $filtros['preco_max'] > 0) {
			$this->db->where('p.preco <=', $filtros['preco_max']);
		}
		
		// Filtro: Apenas com estoque
		if (isset($filtros['apenas_estoque']) && $filtros['apenas_estoque'] == '1') {
			$this->db->where('p.estoque >', 0);
		}
		
		$this->db->order_by('p.nome', 'ASC');
		return $this->db->get()->result_array();
	}

	/**
	 * Listar produtos da loja (método antigo sem filtros)
	 */
	public function listarProdutosLoja($id_usuario_loja){
		$this->db->select('*');
		$this->db->from('produto');
		$this->db->where('id_usuario_loja', $id_usuario_loja);
		$this->db->order_by('nome', 'ASC');
		return $this->db->get()->result_array();
	}

	/**
	 * Buscar produto por ID
	 */
	public function buscarProdutoPorId($id_produto){
		$this->db->select('*');
		$this->db->from('produto');
		$this->db->where('id_produto', $id_produto);
		return $this->db->get()->row_array();
	}

	/**
	 * Cadastrar produto
	 */
	public function cadastrarProduto($dados){
		$dados['id_usuario_loja'] = $this->session->userdata('id_usuario');
		$this->db->insert('produto', $dados);
		return ($this->db->affected_rows() > 0);
	}

	/**
	 * Editar produto
	 */
	public function editarProduto($id_produto, $dados){
		$this->db->where('id_produto', $id_produto);
		$this->db->where('id_usuario_loja', $this->session->userdata('id_usuario'));
		$this->db->update('produto', $dados);
		return ($this->db->affected_rows() > 0);
	}

	/**
	 * Deletar produto
	 */
	public function deletarProduto($id_produto){
		$this->db->where('id_produto', $id_produto);
		$this->db->where('id_usuario_loja', $this->session->userdata('id_usuario'));
		$this->db->delete('produto');
		return ($this->db->affected_rows() > 0);
	}

	/**
	 * Verificar se produto foi vendido
	 */
	public function produtoFoiVendido($id_produto){
		$this->db->select('ci.id_carrinho_item');
		$this->db->from('carrinho_item ci');
		$this->db->join('venda v', 'v.id_carrinho = ci.id_carrinho');
		$this->db->where('ci.id_produto', $id_produto);
		$this->db->limit(1);
		$resultado = $this->db->get()->row_array();
		return ($resultado != null);
	}
}