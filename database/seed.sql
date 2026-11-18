-- StockWeb A3 | Carga fictícia para um banco novo com schema.sql importado.
-- Administrador de demonstração: admin@stockweb.example
-- Senha de demonstração: StockWeb-A3-Demo!2026
-- A credencial é pública e deve ser usada somente no ambiente local da demonstração.

SET NAMES utf8mb4;
START TRANSACTION;

INSERT INTO usuarios (nome, email, senha_hash, perfil)
VALUES ('Administração de Demonstração', 'admin@stockweb.example',
        '$2y$12$DSJfQSTkw79bj8LV8hG4gu3lcl0qwlFZNhtG8LwX5K43KbJh8hQOK', 'ADMIN');

INSERT INTO categorias (id, nome, descricao) VALUES
    (1, 'Periféricos', 'Acessórios para computadores.'),
    (2, 'Redes', 'Equipamentos e acessórios de rede.'),
    (3, 'Armazenamento', 'Unidades de armazenamento de dados.');

INSERT INTO fornecedores (id, nome, cnpj, email) VALUES
    (1, 'Distribuidora Aurora Demo', '00000000000001', 'aurora@fornecedores.example'),
    (2, 'Conecta Estoque Demo', '00000000000002', 'conecta@fornecedores.example'),
    (3, 'Armazém Digital Demo', '00000000000003', 'armazem@fornecedores.example');

INSERT INTO clientes (nome, cpf, email) VALUES
    ('Cliente Demonstração Alfa', '00000000001', 'alfa@clientes.example'),
    ('Cliente Demonstração Beta', '00000000002', 'beta@clientes.example'),
    ('Cliente Demonstração Gama', '00000000003', 'gama@clientes.example');

INSERT INTO produtos (nome, descricao, preco, quantidade, estoque_minimo, categoria_id, fornecedor_id, ativo) VALUES
    ('Teclado USB', 'Teclado com fio para escritório.', 79.90, 24, 5, 1, 1, 1),
    ('Mouse sem fio', 'Mouse compacto com receptor USB.', 59.50, 3, 5, 1, 1, 1),
    ('Cabo de rede CAT6', 'Cabo Ethernet de 2 metros.', 18.75, 0, 10, 2, 2, 1),
    ('Switch de 8 portas', 'Switch Ethernet para rede local.', 169.00, 5, 5, 2, 2, 1),
    ('SSD de 480 GB', 'Unidade SATA de 2,5 polegadas.', 249.90, 12, 4, 3, 3, 1),
    ('Pendrive de 32 GB', 'Unidade USB descontinuada na demonstração.', 34.90, 0, 2, 3, 3, 0);

COMMIT;
