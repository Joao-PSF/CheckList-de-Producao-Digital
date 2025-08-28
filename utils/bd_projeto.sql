-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 28/08/2025 às 04:38
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
-- Banco de dados: `bd_projeto`
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

-- --------------------------------------------------------

--
-- Estrutura para tabela `almoxarifados`
--

CREATE TABLE `almoxarifados` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL COMMENT 'nome do almoxarifado',
  `ativo` tinyint(1) NOT NULL COMMENT 'ativo/inativo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Locais do estoque';

-- --------------------------------------------------------

--
-- Estrutura para tabela `estoque_movimentos`
--

CREATE TABLE `estoque_movimentos` (
  `id` int(11) NOT NULL,
  `data` date NOT NULL,
  `tipo_movimentacao` int(11) NOT NULL,
  `almoxarifado` int(11) NOT NULL,
  `item` int(11) NOT NULL,
  `quantidade` decimal(18,4) NOT NULL,
  `custo_medio` decimal(18,6) NOT NULL,
  `doc_ref` varchar(60) DEFAULT NULL,
  `observacao` varchar(500) NOT NULL,
  `criado_por` int(11) NOT NULL,
  `criado_em` datetime NOT NULL,
  `os` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro de cada movimentacao no estoque';

-- --------------------------------------------------------

--
-- Estrutura para tabela `estoque_movimentos_tipos`
--

CREATE TABLE `estoque_movimentos_tipos` (
  `id` int(11) NOT NULL,
  `tipo` varchar(120) NOT NULL,
  `direcao` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tipos de movimentacao possiveis no estoque';

-- --------------------------------------------------------

--
-- Estrutura para tabela `estoque_saldo`
--

CREATE TABLE `estoque_saldo` (
  `id` int(11) NOT NULL,
  `item` int(11) NOT NULL,
  `almoxarifado` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `custo_medio` int(11) NOT NULL,
  `data` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `itens`
--

CREATE TABLE `itens` (
  `id` int(11) NOT NULL,
  `item` varchar(250) NOT NULL COMMENT 'nome do material',
  `unidade_padrao` int(11) DEFAULT NULL COMMENT 'unidade de medida padrão (chave)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Cadastro dos materiais/itens que o estoque controla.';

-- --------------------------------------------------------

--
-- Estrutura para tabela `servicos_os`
--

CREATE TABLE `servicos_os` (
  `id` int(11) NOT NULL,
  `servico_tipo_id` int(11) NOT NULL COMMENT 'Tipo de Serviço',
  `nome_cliente` varchar(250) DEFAULT NULL COMMENT 'Nome do Cliente',
  `numero_cliente` varchar(250) DEFAULT NULL COMMENT 'Contato do Cliente',
  `criado_por` int(11) NOT NULL COMMENT 'Matricula do Gerador da OS',
  `criado_em` date NOT NULL,
  `servico_responsavel_id` int(11) DEFAULT NULL COMMENT 'Responsaveis pela execução do serviço',
  `servico_obs_id` int(11) DEFAULT NULL COMMENT 'Observações do Serviço',
  `servico_etapas_id` int(11) DEFAULT NULL COMMENT 'Etapas do Serviço',
  `data_programada` date DEFAULT NULL COMMENT 'Data Programada para Encerramento',
  `anexos_id` int(11) DEFAULT NULL COMMENT 'Anexos',
  `status` varchar(7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tabela de Serviços/Tarefas';

-- --------------------------------------------------------

--
-- Estrutura para tabela `servicos_tipos`
--

CREATE TABLE `servicos_tipos` (
  `id` int(11) NOT NULL,
  `tipo` varchar(250) NOT NULL COMMENT 'Tipo de Serviço',
  `status` varchar(7) NOT NULL COMMENT 'Ativo/Inativo?'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tipos de Serviços';

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

-- --------------------------------------------------------

--
-- Estrutura para tabela `servico_observacao`
--

CREATE TABLE `servico_observacao` (
  `id` int(11) NOT NULL,
  `observacao` text NOT NULL COMMENT 'Observação da Ordem de Serviço',
  `criado_por` int(11) NOT NULL COMMENT 'Matricula de quem escreveu a observação',
  `criado_em` date NOT NULL,
  `servico_id_os` int(11) NOT NULL COMMENT 'ID da Ordem de Serviço',
  `status` varchar(7) NOT NULL COMMENT 'Ativo/Inativo?'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Observações da Ordem de Serviço';

-- --------------------------------------------------------

--
-- Estrutura para tabela `servico_responsavel`
--

CREATE TABLE `servico_responsavel` (
  `id` int(11) NOT NULL,
  `responsavel` int(11) NOT NULL COMMENT 'Matricula do Responsavel',
  `servico_os_id` int(11) NOT NULL COMMENT 'ID da Orden de Serviço',
  `status` varchar(7) NOT NULL COMMENT 'Ativo/Inativo?'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Responsavel pela Ordem de Serviço';

-- --------------------------------------------------------

--
-- Estrutura para tabela `unidades`
--

CREATE TABLE `unidades` (
  `id` int(11) NOT NULL,
  `unidade` varchar(150) NOT NULL COMMENT 'abreviacao',
  `nome` varchar(150) NOT NULL COMMENT 'nome'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Lista de unidades de medida usadas';

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `matricula` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `senha` varchar(255) NOT NULL,
  `nivel` tinyint(4) NOT NULL,
  `status` varchar(7) NOT NULL,
  `datadecadastro` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `acessosniveis`
--
ALTER TABLE `acessosniveis`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `almoxarifados`
--
ALTER TABLE `almoxarifados`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `estoque_movimentos`
--
ALTER TABLE `estoque_movimentos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `estoque_movimentos_tipos`
--
ALTER TABLE `estoque_movimentos_tipos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `estoque_saldo`
--
ALTER TABLE `estoque_saldo`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `itens`
--
ALTER TABLE `itens`
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
-- Índices de tabela `servico_observacao`
--
ALTER TABLE `servico_observacao`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `servico_responsavel`
--
ALTER TABLE `servico_responsavel`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `unidades`
--
ALTER TABLE `unidades`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `almoxarifados`
--
ALTER TABLE `almoxarifados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `estoque_movimentos`
--
ALTER TABLE `estoque_movimentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `estoque_movimentos_tipos`
--
ALTER TABLE `estoque_movimentos_tipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `estoque_saldo`
--
ALTER TABLE `estoque_saldo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `itens`
--
ALTER TABLE `itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `servicos_os`
--
ALTER TABLE `servicos_os`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `servicos_tipos`
--
ALTER TABLE `servicos_tipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `servico_etapas`
--
ALTER TABLE `servico_etapas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `servico_observacao`
--
ALTER TABLE `servico_observacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `servico_responsavel`
--
ALTER TABLE `servico_responsavel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `unidades`
--
ALTER TABLE `unidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
