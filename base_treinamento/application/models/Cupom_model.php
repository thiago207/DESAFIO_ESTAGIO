<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cupom_model extends CI_Model {
	
	/**
	 * Listar cupons da loja
	 */
	public function listarCuponsLoja($id_usuario_loja){
		$this->db->select('*');
		$this->db->from('cupom');
		$this->db->where('id_usuario_loja', $id_usuario_loja);
		$this->db->order_by('data_criacao', 'DESC');
		return $this->db->get()->result_array();
	}

	/**
	 * Cadastrar novo cupom
	 */
	public function cadastrarCupom($dados){
		$dados['id_usuario_loja'] = $this->session->userdata('id_usuario');
		$dados['usados'] = 0;
		$dados['data_criacao'] = date('Y-m-d H:i:s');
		
		$this->db->insert('cupom', $dados);
		return ($this->db->affected_rows() > 0);
	}

	/**
	 * Buscar cupom por ID
	 */
	public function buscarCupomPorId($id_cupom){
		$this->db->select('*');
		$this->db->from('cupom');
		$this->db->where('id_cupom', $id_cupom);
		return $this->db->get()->row_array();
	}

	/**
	 * Buscar cupom por código
	 */
	public function buscarCupomPorCodigo($codigo){
		$this->db->select('*');
		$this->db->from('cupom');
		$this->db->where('nome', $codigo);
		return $this->db->get()->row_array();
	}

	/**
	 * Editar cupom
	 */
	public function editarCupom($id_cupom, $dados){
		$this->db->where('id_cupom', $id_cupom);
		$this->db->where('id_usuario_loja', $this->session->userdata('id_usuario'));
		$this->db->update('cupom', $dados);
		return ($this->db->affected_rows() > 0);
	}

	/**
	 * Deletar cupom
	 */
	public function deletarCupom($id_cupom){
		// Verificar se cupom foi usado
		$this->db->select('id_cupom_uso');
		$this->db->from('cupom_uso');
		$this->db->where('id_cupom', $id_cupom);
		$this->db->limit(1);
		$usado = $this->db->get()->row_array();
		
		if ($usado) {
			return false; // Não pode deletar cupom já usado
		}
		
		$this->db->where('id_cupom', $id_cupom);
		$this->db->where('id_usuario_loja', $this->session->userdata('id_usuario'));
		$this->db->delete('cupom');
		return ($this->db->affected_rows() > 0);
	}

	/**
	 * Ativar/Desativar cupom
	 */
	public function ativarDesativar($id_cupom, $ativo){
		$this->db->where('id_cupom', $id_cupom);
		$this->db->where('id_usuario_loja', $this->session->userdata('id_usuario'));
		$this->db->update('cupom', ['ativo' => $ativo]);
		return ($this->db->affected_rows() > 0);
	}

	/**
	 * Validar cupom (MÉTODO PRINCIPAL)
	 */
	public function validarCupom($codigo, $valor_carrinho, $id_loja, $id_usuario){
		$resultado = [
			'valido' => false,
			'mensagem' => '',
			'cupom' => null,
			'valor_desconto' => 0
		];

		// 1. Buscar cupom
		$cupom = $this->buscarCupomPorCodigo($codigo);
		
		if (!$cupom) {
			$resultado['mensagem'] = 'Cupom não encontrado';
			return $resultado;
		}

		// 2. Verificar se é da loja correta
		if ($cupom['id_usuario_loja'] != $id_loja) {
			$resultado['mensagem'] = 'Este cupom não é válido para produtos desta loja';
			return $resultado;
		}

		// 3. Verificar se está ativo
		if ($cupom['ativo'] != 1) {
			$resultado['mensagem'] = 'Cupom desativado';
			return $resultado;
		}

		// 4. Verificar validade
		if ($cupom['data_validade'] && strtotime($cupom['data_validade']) < time()) {
			$resultado['mensagem'] = 'Cupom expirado';
			return $resultado;
		}

		// 5. Verificar estoque
		if ($cupom['usados'] >= $cupom['estoque']) {
			$resultado['mensagem'] = 'Cupom esgotado';
			return $resultado;
		}

		// 6. Verificar valor mínimo
		if ($valor_carrinho < $cupom['valor_minimo']) {
			$resultado['mensagem'] = 'Valor mínimo do carrinho: R$ ' . number_format($cupom['valor_minimo'], 2, ',', '.');
			return $resultado;
		}

		// 7. Verificar se cliente já usou (se houver restrição)
		if ($cupom['um_uso_por_cliente'] == 1) {
			if ($this->clienteJaUsou($cupom['id_cupom'], $id_usuario)) {
				$resultado['mensagem'] = 'Você já utilizou este cupom';
				return $resultado;
			}
		}

		// 8. Calcular desconto
		$valor_desconto = 0;
		if ($cupom['tipo'] == '%') {
			// Desconto percentual
			$valor_desconto = ($valor_carrinho * $cupom['desconto']) / 100;
		} else {
			// Desconto fixo
			$valor_desconto = $cupom['desconto'];
			
			// Não pode descontar mais que o valor do carrinho
			if ($valor_desconto > $valor_carrinho) {
				$valor_desconto = $valor_carrinho;
			}
		}

		// Cupom válido!
		$resultado['valido'] = true;
		$resultado['mensagem'] = 'Cupom aplicado com sucesso!';
		$resultado['cupom'] = $cupom;
		$resultado['valor_desconto'] = $valor_desconto;

		return $resultado;
	}

	/**
	 * Verificar se cliente já usou o cupom
	 */
	public function clienteJaUsou($id_cupom, $id_usuario){
		$this->db->select('id_cupom_uso');
		$this->db->from('cupom_uso');
		$this->db->where('id_cupom', $id_cupom);
		$this->db->where('id_usuario', $id_usuario);
		$this->db->limit(1);
		$resultado = $this->db->get()->row_array();
		
		return ($resultado != null);
	}

	/**
	 * Registrar uso do cupom
	 */
	public function registrarUso($id_cupom, $id_usuario, $id_venda, $valor_desconto){
		$dados = [
			'id_cupom' => $id_cupom,
			'id_usuario' => $id_usuario,
			'id_venda' => $id_venda,
			'valor_desconto' => $valor_desconto,
			'data_uso' => date('Y-m-d H:i:s')
		];
		
		$this->db->insert('cupom_uso', $dados);
		
		// Incrementar contador de usos
		$this->incrementarUsado($id_cupom);
		
		return ($this->db->affected_rows() > 0);
	}

	/**
	 * Incrementar contador de usos
	 */
	public function incrementarUsado($id_cupom){
		$this->db->set('usados', 'usados + 1', FALSE);
		$this->db->where('id_cupom', $id_cupom);
		$this->db->update('cupom');
	}

	/**
	 * Verificar se cupom foi usado
	 */
	public function cupomFoiUsado($id_cupom){
		$this->db->select('id_cupom_uso');
		$this->db->from('cupom_uso');
		$this->db->where('id_cupom', $id_cupom);
		$this->db->limit(1);
		$resultado = $this->db->get()->row_array();
		return ($resultado != null);
	}
}