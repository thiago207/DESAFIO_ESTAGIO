<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Produtos_model extends CI_Model {
	
	/**
	 * Cadastrar um novo produto no banco de dados
	 */
	public function cadastrarProduto($dados){
		// Adiciona o ID da loja (usuário logado)
		$dados['id_usuario_loja'] = $this->session->userdata('id_usuario');
		
		if ($this->db->insert('produto', $dados)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Listar todos os produtos de uma loja específica
	 */
	public function listarProdutosLoja($id_usuario_loja){
		$this->db->select('id_produto, nome, custo, preco, estoque, descricao');
		$this->db->from('produto');
		$this->db->where('id_usuario_loja', $id_usuario_loja);
		$this->db->order_by('nome', 'ASC');
		return $this->db->get()->result_array();
	}

	/**
	 * Buscar um produto pelo ID
	 */
	public function buscarProdutoPorId($id_produto){
		$this->db->select('*');
		$this->db->from('produto');
		$this->db->where('id_produto', $id_produto);
		return $this->db->get()->row_array();
	}

	/**
	 * Editar um produto existente
	 */
	public function editarProduto($id_produto, $dados){
		$this->db->where('id_produto', $id_produto);
		$this->db->update('produto', $dados);
		
		if ($this->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Verificar se o produto já foi vendido
	 */
	public function produtoFoiVendido($id_produto){
		$this->db->select('ci.id_carrinho_item');
		$this->db->from('carrinho_item ci');
		$this->db->join('venda v', 'v.id_carrinho = ci.id_carrinho');
		$this->db->where('ci.id_produto', $id_produto);
		$this->db->limit(1);
		
		$resultado = $this->db->get()->row_array();
		
		// Se encontrou algum registro, o produto foi vendido
		return ($resultado != null);
	}

	/**
	 * Deletar um produto (apenas se não foi vendido)
	 */
	public function deletarProduto($id_produto){
		// Verificar se o produto foi vendido
		if ($this->produtoFoiVendido($id_produto)) {
			return false; // Não pode deletar
		}
		
		$this->db->where('id_produto', $id_produto);
		$this->db->delete('produto');
		
		if ($this->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Listar todos os produtos disponíveis para clientes comprarem
	 */
	public function listarProdutosDisponiveis(){
		$this->db->select('p.id_produto, p.nome, p.preco, p.estoque, p.descricao, u.nome_usuario as nome_loja');
		$this->db->from('produto p');
		$this->db->join('usuario u', 'u.id_usuario = p.id_usuario_loja');
		$this->db->where('p.estoque >', 0); // Apenas produtos com estoque
		$this->db->order_by('p.nome', 'ASC');
		return $this->db->get()->result_array();
	}
}