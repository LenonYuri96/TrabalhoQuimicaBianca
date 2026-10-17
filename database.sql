-- =========================================================================
-- BANCO DE DADOS: lab_control
-- Sistema de Gestão Laboratorial Inteligente (Lab 4.0)
-- =========================================================================
CREATE DATABASE IF NOT EXISTS lab_control CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE lab_control;

-- Desativa checagem temporária de chaves para recriação limpa e ordenada
SET
    FOREIGN_KEY_CHECKS = 0;

-- Remoção prévia das tabelas na ordem inversa de dependência
DROP TABLE IF EXISTS residuos;

DROP TABLE IF EXISTS aulas_agenda;

DROP TABLE IF EXISTS pratica_itens;

DROP TABLE IF EXISTS praticas;

DROP TABLE IF EXISTS ocorrencias;

DROP TABLE IF EXISTS equipamentos;

DROP TABLE IF EXISTS quimicos;

-- --------------------------------------------------------
-- 1. Tabela: Químicos e Reagentes
-- --------------------------------------------------------
CREATE TABLE quimicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    quantidade DECIMAL(10, 2) NOT NULL DEFAULT 1.00,
    validade DATE NULL COMMENT 'NULL quando for N/A',
    tipo_armazenamento VARCHAR(150) NULL COMMENT 'Ex: Armário de Ácidos, Corta-Fogo, Geladeira',
    periculosidade VARCHAR(150) NULL COMMENT 'Ex: Corrosivo, Inflamável, Tóxico',
    estado ENUM('Pó', 'Liquido', 'Granulo') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_quimicos_validade (validade),
    INDEX idx_quimicos_armazenamento (tipo_armazenamento)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. Tabela: Equipamentos & Vidrarias (Estrutura Unificada)
-- --------------------------------------------------------
CREATE TABLE equipamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('equipamento', 'vidraria') NOT NULL DEFAULT 'equipamento',
    nome VARCHAR(150) NOT NULL,
    capacidade_volume VARCHAR(50) NULL COMMENT 'Ex: 250 mL, 500 mL, 1 L (relevante para vidrarias)',
    codigo_patrimonio VARCHAR(50) NULL COMMENT 'Ex: COD:NO299A (apenas para aparelhos)',
    frequencia_meses INT NULL COMMENT 'Periodicidade em meses (1, 6, 12 ou NULL para inspeção manual)',
    ultima_manutencao DATE NULL,
    proxima_manutencao DATE NULL,
    -- Controle de Estoque, Avarias, Quebras e Defeitos:
    quantidade_total INT NOT NULL DEFAULT 1 COMMENT 'Total patrimoniado/comprado',
    quantidade_disponivel INT NOT NULL DEFAULT 1 COMMENT 'Disponíveis e intactas para bancada',
    quantidade_quebrada INT NOT NULL DEFAULT 0 COMMENT 'Quebras totais / inutilizadas',
    quantidade_defeituosa INT NOT NULL DEFAULT 0 COMMENT 'Trincas, fissuras, boca lascada',
    observacao VARCHAR(255) NULL COMMENT 'Cuidados específicos, rotina ou calibração',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_equipamentos_tipo (tipo),
    INDEX idx_equipamentos_proxima (proxima_manutencao)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Tabela: Registro de Ocorrências e Incidentes
-- --------------------------------------------------------
CREATE TABLE ocorrencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    relator_nome VARCHAR(150) NOT NULL,
    local_ocorrido VARCHAR(150) NOT NULL,
    data_hora DATETIME NOT NULL,
    categoria ENUM('Vidraria', 'Equipamento', 'Produto') NOT NULL,
    item_nome VARCHAR(150) NOT NULL,
    turma_disciplina VARCHAR(150) NULL COMMENT 'Turma ou matéria em aula no momento da ocorrência',
    descricao TEXT NOT NULL,
    status ENUM('Pendente', 'Em Análise', 'Resolvido') NOT NULL DEFAULT 'Pendente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ocorrencias_status (status),
    INDEX idx_ocorrencias_categoria (categoria),
    INDEX idx_ocorrencias_turma (turma_disciplina)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Tabela: Roteiros de Práticas de Laboratório
-- --------------------------------------------------------
CREATE TABLE praticas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    materia_curso VARCHAR(150) NOT NULL,
    objetivo TEXT NOT NULL,
    procedimento TEXT NOT NULL COMMENT 'Passo a passo experimental',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_praticas_materia (materia_curso)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. Tabela: Itens Usados na Prática (Reagentes, Vidrarias e Equipamentos)
-- --------------------------------------------------------
CREATE TABLE pratica_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pratica_id INT NOT NULL,
    tipo_item ENUM('quimico', 'equipamento', 'vidraria') NOT NULL,
    item_id INT NOT NULL COMMENT 'ID correspondente na tabela quimicos ou equipamentos',
    quantidade_necessaria VARCHAR(100) NOT NULL COMMENT 'Ex: 50 mL, 2 un, 3 gotas',
    observacao VARCHAR(255) NULL,
    FOREIGN KEY (pratica_id) REFERENCES praticas(id) ON DELETE CASCADE,
    INDEX idx_itens_busca (tipo_item, item_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 6. Tabela: Agenda de Aulas (Materiais por Dia e Matéria)
-- --------------------------------------------------------
CREATE TABLE aulas_agenda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data_aula DATE NOT NULL,
    horario VARCHAR(50) NOT NULL COMMENT 'Ex: 08:00 - 10:00',
    materia VARCHAR(150) NOT NULL,
    turma VARCHAR(100) NOT NULL,
    professor VARCHAR(150) NOT NULL,
    pratica_id INT NULL COMMENT 'Roteiro vinculado para extrair materiais necessários',
    observacoes_adicionais TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pratica_id) REFERENCES praticas(id) ON DELETE
    SET
        NULL,
        INDEX idx_aulas_data (data_aula),
        INDEX idx_aulas_materia (materia)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 7. Tabela: Gestão de Resíduos Químicos & Bombonas
-- --------------------------------------------------------
CREATE TABLE residuos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_bombona VARCHAR(50) NOT NULL COMMENT 'Ex: BOM-ORG-01, BOM-MET-02',
    tipo_residuo VARCHAR(100) NOT NULL COMMENT 'Orgânico Inflamável, Solução Ácida, Metais Pesados',
    local_armazenamento VARCHAR(150) NOT NULL COMMENT 'Bancada de Descarte, Depósito Externo',
    volume_litros DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    capacidade_maxima DECIMAL(10, 2) NOT NULL DEFAULT 20.00,
    status ENUM(
        'Em Coleta',
        'Cheia / Aguardando Coleta',
        'Descartada'
    ) NOT NULL DEFAULT 'Em Coleta',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_residuos_status (status),
    INDEX idx_residuos_tipo (tipo_residuo)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;