-- Criação da Base de Dados
CREATE DATABASE IF NOT EXISTS lab_control CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE lab_control;

-- --------------------------------------------------------
-- 1. Tabela: Químicos e Reagentes (Referente ao Inventário)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS quimicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    quantidade DECIMAL(10, 2) NOT NULL DEFAULT 1.00,
    validade DATE NULL,
    -- Aceita NULL quando for 'N/A'
    tipo_armazenamento VARCHAR(150) NULL,
    periculosidade VARCHAR(150) NULL,
    estado ENUM('Pó', 'Liquido', 'Granulo') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB;

-- --------------------------------------------------------
-- 2. Tabela: Equipamentos e Manutenção (Referente à Lista de Preventivas)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS equipamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    codigo_patrimonio VARCHAR(50) NULL,
    -- Ex: 'COD:NO299A'
    frequencia_meses INT NULL,
    -- Periodicidade (1, 6, 12 meses ou NULL p/ inspeções eventuais/diárias)
    ultima_manutencao DATE NULL,
    proxima_manutencao DATE NULL,
    observacao VARCHAR(255) NULL,
    -- Ex: 'inspeção antes do uso', 'troca de eletrodo'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB;

-- --------------------------------------------------------
-- 3. Tabela: Registro de Ocorrências (Referente ao Formulário Gráfico)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS ocorrencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    relator_nome VARCHAR(150) NOT NULL,
    -- Nome de quem está relatando
    local_ocorrido VARCHAR(150) NOT NULL,
    -- Onde foi o ocorrido
    data_hora DATETIME NOT NULL,
    -- Data/Hora
    categoria ENUM('Vidraria', 'Equipamento', 'Produto') NOT NULL,
    item_nome VARCHAR(150) NOT NULL,
    -- Nome do item envolvido
    descricao TEXT NOT NULL,
    -- Descrição do fato/observação
    status ENUM('Pendente', 'Em Análise', 'Resolvido') NOT NULL DEFAULT 'Pendente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB;