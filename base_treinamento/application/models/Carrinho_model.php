<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Carrinho_model extends CI_Model {
	
	public function buscarOuCriarCarrinho($id_usuario){
		$this->db->select('id_carrinho');
		$this->db->from('carrinho');
		$this->db->where('id_usuario', $id_usuario);
		$this->db->where('id_carrinho NOT IN (SELECT id_carrinho FROM venda)', NULL, FALSE);
		$carrinho = $this->db->get()->row_array();
		
		if ($carrinho) {
			return $carrinho['id_carrinho'];
		}
		
		$dados = ['id_usuario' => $id_usuario];
		$this->db->insert('carrinho', $dados);
		return $this->db->insert_id();
	}

	public function adicionarProduto($id_carrinho, $id_produto, $quantidade){
		$this->db->select('id_carrinho_item, quantidade');
		$this->db->from('carrinho_item');
		$this->db->where('id_carrinho', $id_carrinho);
		$this->db->where('id_produto', $id_produto);
		$item = $this->db->get()->row_array();
		
		if ($item) {
			$nova_quantidade = $item['quantidade'] + $quantidade;
			$this->db->where('id_carrinho_item', $item['id_carrinho_item']);
			$this->db->update('carrinho_item', ['quantidade' => $nova_quantidade]);
		} else {
			$dados = [
				'id_carrinho' => $id_carrinho,
				'id_produto' => $id_produto,
				'quantidade' => $quantidade
			];
			$this->db->insert('carrinho_item', $dados);
		}
		
		return true;
	}

	public function listarItensCarrinho($id_carrinho){
		$this->db->select('ci.id_carrinho_item, ci.id_produto, ci.quantidade, p.nome, p.preco, p.estoque, p.id_usuario_loja as id_loja, u.nome_usuario as nome_loja');
		$this->db->from('carrinho_item ci');
		$this->db->join('produto p', 'p.id_produto = ci.id_produto');
		$this->db->join('usuario u', 'u.id_usuario = p.id_usuario_loja');
		$this->db->where('ci.id_carrinho', $id_carrinho);
		return $this->db->get()->result_array();
	}

	public function contarItensCarrinho($id_carrinho){
		$this->db->select('SUM(quantidade) as total');
		$this->db->from('carrinho_item');
		$this->db->where('id_carrinho', $id_carrinho);
		$resultado = $this->db->get()->row_array();
		return $resultado['total'] ?? 0;
	}

	public function atualizarQuantidade($id_carrinho_item, $quantidade){
		if ($quantidade <= 0) {
			return false;
		}
		
		$this->db->where('id_carrinho_item', $id_carrinho_item);
		$this->db->update('carrinho_item', ['quantidade' => $quantidade]);
		
		return ($this->db->affected_rows() > 0);
	}

	public function removerItem($id_carrinho_item){
		$this->db->where('id_carrinho_item', $id_carrinho_item);
		$this->db->delete('carrinho_item');
		
		return ($this->db->affected_rows() > 0);
	}

	public function calcularTotal($id_carrinho){
		$this->db->select('SUM(ci.quantidade * p.preco) as total');
		$this->db->from('carrinho_item ci');
		$this->db->join('produto p', 'p.id_produto = ci.id_produto');
		$this->db->where('ci.id_carrinho', $id_carrinho);
		$resultado = $this->db->get()->row_array();
		return $resultado['total'] ?? 0;
	}

	public function finalizarCompra($id_carrinho){
		$this->db->trans_start();
		
		$itens = $this->listarItensCarrinho($id_carrinho);
		
		foreach ($itens as $item) {
			if ($item['quantidade'] > $item['estoque']) {
				$this->db->trans_rollback();
				return [
					'sucesso' => false,
					'mensagem' => "Produto '{$item['nome']}' não tem estoque suficiente. Disponível: {$item['estoque']}"
				];
			}
		}
		
		foreach ($itens as $item) {
			$novo_estoque = $item['estoque'] - $item['quantidade'];
			$this->db->where('id_produto', $item['id_produto']);
			$this->db->update('produto', ['estoque' => $novo_estoque]);
		}
		
		$dados_venda = [
			'id_carrinho' => $id_carrinho,
			'data_venda' => date('Y-m-d H:i:s')
		];
		$this->db->insert('venda', $dados_venda);
		$id_venda = $this->db->insert_id();
		
		$this->db->trans_complete();
		
		if ($this->db->trans_status() === FALSE) {
			return [
				'sucesso' => false,
				'mensagem' => 'Erro ao processar a compra'
			];
		}
		
		return [
			'sucesso' => true,
			'mensagem' => 'Compra realizada com sucesso!',
			'id_venda' => $id_venda
		];
	}
}