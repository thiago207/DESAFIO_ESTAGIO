-- Criar tabela cupom_uso (se não existir)
CREATE TABLE IF NOT EXISTS cupom_uso (
    id_cupom_uso INT PRIMARY KEY AUTO_INCREMENT,
    id_cupom INT NOT NULL,
    id_usuario INT NOT NULL,
    id_venda INT NOT NULL,
    valor_desconto DECIMAL(10,2) NOT NULL,
    data_uso DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cupom) REFERENCES cupom(id_cupom),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_venda) REFERENCES venda(id_venda)
);

-- Cupons de teste para a loja com id_usuario_loja = 2
INSERT INTO cupom (id_usuario_loja, nome, desconto, tipo, valor_minimo, estoque, usados, ativo, um_uso_por_cliente) 
VALUES (2, 'CUPOM15', 15, '%', 100.00, 50, 0, 1, 0);

INSERT INTO cupom (id_usuario_loja, nome, desconto, tipo, valor_minimo, estoque, usados, ativo, um_uso_por_cliente) 
VALUES (2, 'PROMO50', 50, '$', 200.00, 20, 0, 1, 1);