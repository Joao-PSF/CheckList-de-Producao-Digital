-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
<<<<<<< HEAD:utils/bd_projeto.sql
-- Tempo de geração: 02/10/2025 às 02:39
=======
-- Tempo de geração: 19/10/2025 às 23:21
>>>>>>> 848f50fec24c67710f333aeebfc4c386e18b58bb:utils/metalma.sql
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `metalma`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `acessosniveis`
--

CREATE TABLE `acessosniveis` (
  `id` int(11) NOT NULL,
  `nivel` tinyint(4) NOT NULL,
  `descricao` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

<<<<<<< HEAD:utils/bd_projeto.sql
--
-- Despejando dados para a tabela `acessosniveis`
--

INSERT INTO `acessosniveis` (`id`, `nivel`, `descricao`) VALUES
(1, 1, 'Admin'),
(2, 2, 'Operador'),
(3, 3, 'Supervisor');

-- --------------------------------------------------------

=======
>>>>>>> 848f50fec24c67710f333aeebfc4c386e18b58bb:utils/metalma.sql
--
-- Despejando dados para a tabela `acessosniveis`
--

INSERT INTO `acessosniveis` (`id`, `nivel`, `descricao`) VALUES
(1, 1, 'Admin'),
(2, 2, 'Operador'),
(3, 3, 'Supervisor');

-- --------------------------------------------------------

--
-- Estrutura para tabela `cadastro_log`
--

CREATE TABLE `cadastro_log` (
  `id` int(11) NOT NULL,
  `acao` varchar(100) NOT NULL COMMENT 'Tipo de ação realizada',
  `usuario_id` int(11) DEFAULT NULL COMMENT 'ID do usuário que executou a ação',
  `usuario_cpf` varchar(11) DEFAULT NULL COMMENT 'CPF do usuário ue executou a ação',
  `usuario_matricula` int(11) DEFAULT NULL COMMENT 'Matrícula do usuário ue executou a ação',
  `descricao` text DEFAULT NULL COMMENT 'Descrição detalhada da ação',
  `dados_antes` text DEFAULT NULL COMMENT 'Todos Dados antes da alteração (JSON)',
  `dados_depois` text DEFAULT NULL COMMENT 'Todos Dados após a alteração (JSON)',
  `status` enum('sucesso','falha','erro') NOT NULL DEFAULT 'sucesso' COMMENT 'Status da operação',
  `mensagem_erro` text DEFAULT NULL COMMENT 'Mensagem de erro, se houver',
  `criado_em` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Data e hora do registro'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Logs relacionados a cadastro de usuários';

-- --------------------------------------------------------

--
-- Estrutura para tabela `login_log`
--

CREATE TABLE `login_log` (
  `id` int(11) NOT NULL,
  `acao` varchar(100) NOT NULL COMMENT 'Tipo de ação realizada',
  `usuario_matricula` int(11) DEFAULT NULL COMMENT 'Matrícula do usuário',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'Endereço IP do usuário',
  `user_agent` varchar(255) DEFAULT NULL COMMENT 'Navegador/dispositivo utilizado',
  `descricao` text DEFAULT NULL COMMENT 'Descrição detalhada da ação',
  `status` enum('sucesso','falha','erro') NOT NULL DEFAULT 'sucesso' COMMENT 'Status da operação',
  `mensagem_erro` text DEFAULT NULL COMMENT 'Mensagem de erro, se houver',
  `criado_em` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Data e hora do registro'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Logs relacionados a login e logout';

-- --------------------------------------------------------

--
-- Estrutura para tabela `servicos_log`
--

CREATE TABLE `servicos_log` (
  `id` int(11) NOT NULL,
  `acao` varchar(100) NOT NULL COMMENT 'Tipo de ação realizada',
  `usuario_cpf` varchar(11) NOT NULL COMMENT 'CPF do usuário ue executou a ação',
  `usuario_matricula` int(11) NOT NULL COMMENT 'Matrícula do usuário ue executou a ação',
  `descricao` text DEFAULT NULL COMMENT 'Descrição detalhada da ação',
  `dados_antes` text DEFAULT NULL COMMENT 'Dados antes da alteração (JSON)',
  `dados_depois` text DEFAULT NULL COMMENT 'Dados após a alteração (JSON)',
  `status` enum('sucesso','falha','erro') NOT NULL DEFAULT 'sucesso' COMMENT 'Status da operação',
  `mensagem_erro` text DEFAULT NULL COMMENT 'Mensagem de erro, se houver',
  `criado_em` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Data e hora do registro'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Logs relacionados a ordens de serviço e etapas';

-- --------------------------------------------------------

--
-- Estrutura para tabela `servicos_os`
--

CREATE TABLE `servicos_os` (
  `id` int(11) NOT NULL,
  `servico_tipo_id` int(11) NOT NULL COMMENT 'Tipo de Serviço',
  `nome_cliente` varchar(250) DEFAULT NULL COMMENT 'Nome do Cliente',
  `numero_cliente` varchar(250) DEFAULT NULL COMMENT 'Contato do Cliente',
  `criado_por_cpf` int(11) NOT NULL COMMENT 'Matricula do Gerador da OS',
  `criado_em` date NOT NULL,
  `data_inicio` date DEFAULT NULL,
  `data_programada` date DEFAULT NULL COMMENT 'Data Programada para Encerramento',
  `data_encerramento` date DEFAULT NULL,
  `situacao` varchar(20) NOT NULL DEFAULT 'ANDAMENTO' COMMENT 'ANDAMENTO/PENDENTE/ENCERRADA',
  `status` varchar(20) NOT NULL COMMENT 'Ativo/Inativo?'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tabela de Serviços/Tarefas';

--
-- Despejando dados para a tabela `servicos_os`
--

INSERT INTO `servicos_os` (`id`, `servico_tipo_id`, `nome_cliente`, `numero_cliente`, `criado_por_cpf`, `criado_em`, `data_inicio`, `data_programada`, `data_encerramento`, `situacao`, `status`) VALUES
(7, 1, 'dasdas', 'dasd', 222222222, '2025-09-26', NULL, '2025-09-27', NULL, 'PENDENTE', 'Ativo'),
(8, 2, 'jhgjhgj', 'ghjgh', 222222222, '2025-09-26', NULL, '2025-09-26', NULL, 'PENDENTE', 'Ativo'),
(9, 2, 'jhgjhgj', 'ghjgh', 222222222, '2025-09-26', NULL, '2025-09-26', NULL, 'PENDENTE', 'Ativo'),
(10, 1, 'bnbvn', 'vbnvbnv', 222222222, '2025-09-26', NULL, '2025-09-26', NULL, 'PENDENTE', 'Ativo'),
(11, 1, 'fgfcv', 'cxvcx', 222222222, '2025-09-26', NULL, '2025-09-26', NULL, 'PENDENTE', 'Ativo'),
(12, 1, 'sdsad', 'asdasd', 222222222, '2025-09-26', NULL, '2025-09-26', NULL, 'PENDENTE', 'Ativo'),
(13, 1, 'teste1', 'teste1', 2147483647, '2025-09-28', NULL, '2025-09-28', NULL, 'PENDENTE', 'Ativo');

-- --------------------------------------------------------

--
-- Estrutura para tabela `servicos_tipos`
--

CREATE TABLE `servicos_tipos` (
  `id` int(11) NOT NULL,
  `tipo` varchar(250) NOT NULL COMMENT 'Tipo de Serviço',
  `status` varchar(7) NOT NULL COMMENT 'Ativo/Inativo?'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tipos de Serviços';

--
-- Despejando dados para a tabela `servicos_tipos`
--

INSERT INTO `servicos_tipos` (`id`, `tipo`, `status`) VALUES
(1, 'Manutenção', 'Ativo'),
(2, 'Outro', 'Ativo');

-- --------------------------------------------------------

--
-- Estrutura para tabela `servico_etapas`
--

CREATE TABLE `servico_etapas` (
  `id` int(11) NOT NULL,
  `etapa` varchar(250) NOT NULL COMMENT 'Nome da Etapa',
  `ordem` int(11) NOT NULL COMMENT 'Ordem da Etapa',
  `execucao` tinyint(4) NOT NULL COMMENT 'Executada?',
  `criada_em` date NOT NULL COMMENT 'Data de Criação da Etapa',
  `executada_em` date DEFAULT NULL COMMENT 'Data de Execução',
  `servico_os_id` int(11) NOT NULL COMMENT 'ID da Ordem de Serviço',
  `status` varchar(7) NOT NULL COMMENT 'Ativo/Inativo?'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `servico_etapas`
--

INSERT INTO `servico_etapas` (`id`, `etapa`, `ordem`, `execucao`, `criada_em`, `executada_em`, `servico_os_id`, `status`) VALUES
(1, 'Vistoria Inicial', 1, 0, '2025-09-18', NULL, 1, 'Ativo'),
(2, 'Execução do Serviço', 1, 0, '2025-09-18', NULL, 2, 'Ativo'),
(3, 'Entrega e Fechamento', 1, 1, '2025-09-13', '2025-09-15', 3, 'Ativo');

-- --------------------------------------------------------

--
-- Estrutura para tabela `servico_etapas_observacao`
--

CREATE TABLE `servico_etapas_observacao` (
  `id` int(11) NOT NULL,
  `observacao` text NOT NULL COMMENT 'Observação da Ordem de Serviço',
  `criado_por` int(11) NOT NULL COMMENT 'Matricula de quem escreveu a observação',
  `criado_em` date NOT NULL,
  `servico_etapa_id` int(11) NOT NULL COMMENT 'ID da Etapa',
  `status` varchar(7) NOT NULL COMMENT 'Ativo/Inativo?'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Observações da Ordem de Serviço';

-- --------------------------------------------------------

--
-- Estrutura para tabela `servico_etapas_responsavel`
--

CREATE TABLE `servico_etapas_responsavel` (
  `id` int(11) NOT NULL,
  `responsavel` varchar(11) NOT NULL COMMENT 'CPF do Responsavel',
  `servico_etapa_id` int(11) NOT NULL,
  `status` varchar(7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `servico_os_responsavel`
--

CREATE TABLE `servico_os_responsavel` (
  `id` int(11) NOT NULL,
  `responsavel` varchar(11) NOT NULL COMMENT 'CPF do Responsavel',
  `servico_os_id` int(11) NOT NULL COMMENT 'ID da OS',
  `status` varchar(7) NOT NULL COMMENT 'Ativo/Inativo?'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Responsavel pela Ordem de Serviço';

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `matricula` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `cpf` varchar(11) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `senha` varchar(255) NOT NULL,
  `nivel` tinyint(4) NOT NULL,
  `status` varchar(7) NOT NULL,
  `datadecadastro` datetime NOT NULL,
  `criado_por_cpf` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `matricula`, `nome`, `cpf`, `email`, `senha`, `nivel`, `status`, `datadecadastro`, `criado_por_cpf`) VALUES
(4, 1234, 'Usuario Supervisor', '12345678901', 'teste@gmail.com', '$2y$10$Y.hMIJAMgsWl/Ip1hzxqF.Sf.IeP0lcHCpoeQiJbPcDuDjrCfua0u', 2, 'Ativo', '2025-08-12 22:08:19', ''),
(9, 123, 'Usuario Operador', '00000000000', 'teste@gmail.com', '$2y$10$Y.hMIJAMgsWl/Ip1hzxqF.Sf.IeP0lcHCpoeQiJbPcDuDjrCfua0u', 1, 'Ativo', '2025-08-12 22:08:19', ''),
(10, 12345, 'Usuario Gerente', '11122233344', 'teste@gmail.com', '$2y$10$Y.hMIJAMgsWl/Ip1hzxqF.Sf.IeP0lcHCpoeQiJbPcDuDjrCfua0u', 3, 'Ativo', '2025-08-12 22:08:19', '');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `acessosniveis`
--
ALTER TABLE `acessosniveis`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `cadastro_log`
--
ALTER TABLE `cadastro_log`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `login_log`
--
ALTER TABLE `login_log`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `servicos_log`
--
ALTER TABLE `servicos_log`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `servicos_os`
--
ALTER TABLE `servicos_os`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `servicos_tipos`
--
ALTER TABLE `servicos_tipos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `servico_etapas`
--
ALTER TABLE `servico_etapas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `servico_etapas_observacao`
--
ALTER TABLE `servico_etapas_observacao`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `servico_etapas_responsavel`
--
ALTER TABLE `servico_etapas_responsavel`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `servico_os_responsavel`
--
ALTER TABLE `servico_os_responsavel`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `acessosniveis`
--
ALTER TABLE `acessosniveis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
<<<<<<< HEAD:utils/bd_projeto.sql
=======

--
-- AUTO_INCREMENT de tabela `cadastro_log`
--
ALTER TABLE `cadastro_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
>>>>>>> 848f50fec24c67710f333aeebfc4c386e18b58bb:utils/metalma.sql

--
-- AUTO_INCREMENT de tabela `login_log`
--
ALTER TABLE `login_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `servicos_log`
--
ALTER TABLE `servicos_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `servicos_os`
--
ALTER TABLE `servicos_os`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `servicos_tipos`
--
ALTER TABLE `servicos_tipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `servico_etapas`
--
ALTER TABLE `servico_etapas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `servico_etapas_observacao`
--
ALTER TABLE `servico_etapas_observacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `servico_etapas_responsavel`
--
ALTER TABLE `servico_etapas_responsavel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `servico_os_responsavel`
--
ALTER TABLE `servico_os_responsavel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
