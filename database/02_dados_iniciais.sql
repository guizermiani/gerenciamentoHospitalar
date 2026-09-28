-- Dados iniciais do Sistema de Chamados - Hospital
-- Rode DEPOIS de 01_estrutura.sql, com o banco sistema_chamados_hospital selecionado.
-- Prioridades e status já vêm no 01_estrutura.sql.

INSERT INTO setor (nome_setor, descricao) VALUES
('TI', 'Tecnologia da Informação'),
('Recepção', 'Recepção e atendimento ao paciente'),
('Enfermagem', 'Equipe de enfermagem'),
('Laboratório', 'Exames laboratoriais'),
('Manutenção', 'Manutenção predial e de equipamentos');

INSERT INTO categoria_chamado (nome_categoria, descricao) VALUES
('Hardware', 'Computadores, impressoras e periféricos'),
('Software', 'Sistemas e aplicativos'),
('Rede', 'Internet, Wi-Fi e cabeamento'),
('Equipamento médico', 'Equipamentos de uso assistencial'),
('Infraestrutura', 'Elétrica, ar-condicionado e estrutura física');

-- Usuários de teste (senhas em bcrypt, compatíveis com password_verify do PHP):
--   admin@hospital.com      / Admin@123
--   tecnico@hospital.com    / Tecnico@123
--   atendente@hospital.com  / Atendente@123
-- CPFs abaixo são fictícios, só para preencher o campo obrigatório.
INSERT INTO funcionario (nome, cpf, matricula, cargo, telefone, id_setor, email, senha, tipo_usuario) VALUES
('Administrador do Sistema', '000.000.000-01', 'ADM001', 'Administrador', NULL,
  (SELECT id_setor FROM setor WHERE nome_setor = 'TI'),
  'admin@hospital.com', '$2y$10$aBDnGZrb/bcybK7WUlpIQO1NDnDpIIl.S8V43VAr0mbkiguVce4yC', 'admin'),
('Técnico de Suporte', '000.000.000-02', 'TEC001', 'Técnico de TI', NULL,
  (SELECT id_setor FROM setor WHERE nome_setor = 'TI'),
  'tecnico@hospital.com', '$2y$10$LwmGzNvFjDe34dPf5Ib6N.cvdGR81Lkm3iFPmYk9BHRelY/Iqrywm', 'tecnico'),
('Atendente da Recepção', '000.000.000-03', 'ATD001', 'Atendente', NULL,
  (SELECT id_setor FROM setor WHERE nome_setor = 'Recepção'),
  'atendente@hospital.com', '$2y$10$baezkjhJQFMwEd3HquxNC..qKMdzjODO9QHhTNcLjlpMC8iTppKT6', 'atendente');

-- Pacientes fictícios (opcionais ao abrir um chamado)
INSERT INTO paciente (nome) VALUES
('Paciente Exemplo 1'),
('Paciente Exemplo 2'),
('Paciente Exemplo 3');
