<?php
// Ativa exibição de erros caso haja falha no banco
ini_set('display_errors', 1);
error_reporting(E_ALL);

class Database
{
    private static $host = 'localhost';
    private static $db_name = 'lab_control';
    private static $username = 'root';
    private static $password = ''; // Se você usa senha no MySQL (ex: root), altere aqui
    private static $conn = null;

    public static function getConnection()
    {
        if (self::$conn === null) {
            try {
                // 1. Conecta ao servidor MySQL e cria o banco se não existir
                $pdoInitial = new PDO(
                    "mysql:host=" . self::$host . ";charset=utf8mb4",
                    self::$username,
                    self::$password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
                $pdoInitial->exec("CREATE DATABASE IF NOT EXISTS `" . self::$db_name . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

                // 2. Conecta ao banco 'lab_control'
                self::$conn = new PDO(
                    "mysql:host=" . self::$host . ";dbname=" . self::$db_name . ";charset=utf8mb4",
                    self::$username,
                    self::$password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true
                    ]
                );

                // 3. Executa a criação segura das tabelas
                self::autoCreateTables(self::$conn);

            } catch (PDOException $e) {
                die("<div style='font-family:sans-serif;padding:30px;background:#fff2f2;border:1px solid #ffb3b3;margin:40px auto;max-width:600px;border-radius:8px;'>
                    <h3 style='color:#c00;'>⚠️ Falha na Conexão com o Banco de Dados</h3>
                    <p>Verifique se o seu <strong>MySQL / MariaDB</strong> (XAMPP, Laragon ou WAMP) está ligado!</p>
                    <p><small style='color:#666;'>Erro técnico: " . htmlspecialchars($e->getMessage()) . "</small></p>
                </div>");
            }
        }
        return self::$conn;
    }

    private static function autoCreateTables(PDO $conn)
    {
        // Lista ordenada de comandos DDL individuais para garantir compatibilidade total
        $tabelas = [
            "CREATE TABLE IF NOT EXISTS usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(150) NOT NULL,
                email VARCHAR(150) NOT NULL UNIQUE,
                senha VARCHAR(255) NOT NULL,
                perfil ENUM('instrutor', 'admin') NOT NULL DEFAULT 'instrutor',
                ativo TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_usuarios_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS quimicos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(150) NOT NULL,
                quantidade DECIMAL(10, 2) NOT NULL DEFAULT 1.00,
                validade DATE NULL,
                tipo_armazenamento VARCHAR(150) NULL,
                periculosidade VARCHAR(150) NULL,
                estado ENUM('Pó', 'Liquido', 'Granulo') NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_quimicos_validade (validade)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS equipamentos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tipo ENUM('equipamento', 'vidraria') NOT NULL DEFAULT 'equipamento',
                nome VARCHAR(150) NOT NULL,
                capacidade_volume VARCHAR(50) NULL,
                codigo_patrimonio VARCHAR(50) NULL,
                frequencia_meses INT NULL,
                ultima_manutencao DATE NULL,
                proxima_manutencao DATE NULL,
                quantidade_total INT NOT NULL DEFAULT 1,
                quantidade_disponivel INT NOT NULL DEFAULT 1,
                quantidade_quebrada INT NOT NULL DEFAULT 0,
                quantidade_defeituosa INT NOT NULL DEFAULT 0,
                observacao VARCHAR(255) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_equipamentos_tipo (tipo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS ocorrencias (
                id INT AUTO_INCREMENT PRIMARY KEY,
                relator_nome VARCHAR(150) NOT NULL,
                local_ocorrido VARCHAR(150) NOT NULL,
                data_hora DATETIME NOT NULL,
                categoria ENUM('Vidraria', 'Equipamento', 'Produto') NOT NULL,
                item_nome VARCHAR(150) NOT NULL,
                turma_disciplina VARCHAR(150) NULL,
                descricao TEXT NOT NULL,
                status ENUM('Pendente', 'Em Análise', 'Resolvido') NOT NULL DEFAULT 'Pendente',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_ocorrencias_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS praticas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                titulo VARCHAR(255) NOT NULL,
                materia_curso VARCHAR(150) NOT NULL,
                objetivo TEXT NOT NULL,
                procedimento TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS pratica_itens (
                id INT AUTO_INCREMENT PRIMARY KEY,
                pratica_id INT NOT NULL,
                tipo_item ENUM('quimico', 'equipamento', 'vidraria') NOT NULL,
                item_id INT NOT NULL,
                quantidade_necessaria VARCHAR(100) NOT NULL,
                observacao VARCHAR(255) NULL,
                FOREIGN KEY (pratica_id) REFERENCES praticas(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS aulas_agenda (
                id INT AUTO_INCREMENT PRIMARY KEY,
                data_aula DATE NOT NULL,
                horario VARCHAR(50) NOT NULL,
                materia VARCHAR(150) NOT NULL,
                turma VARCHAR(100) NOT NULL,
                professor VARCHAR(150) NOT NULL,
                pratica_id INT NULL,
                observacoes_adicionais TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (pratica_id) REFERENCES praticas(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS residuos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                codigo_bombona VARCHAR(50) NOT NULL,
                tipo_residuo VARCHAR(100) NOT NULL,
                local_armazenamento VARCHAR(150) NOT NULL,
                volume_litros DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
                capacidade_maxima DECIMAL(10, 2) NOT NULL DEFAULT 20.00,
                status ENUM('Em Coleta', 'Cheia / Aguardando Coleta', 'Descartada') NOT NULL DEFAULT 'Em Coleta',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        ];

        foreach ($tabelas as $sql) {
            $conn->exec($sql);
        }

        // Insere o instrutor inicial de demonstração se a tabela estiver sem registros
        $stmt = $conn->query("SELECT COUNT(*) FROM usuarios");
        if ($stmt->fetchColumn() == 0) {
            $senhaPadrao = password_hash('admin123', PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO usuarios (nome, email, senha, perfil) VALUES (?, ?, ?, 'instrutor')");
            $insert->execute(['Instrutor FIEMG', 'instrutor@fiemg.com.br', $senhaPadrao]);
        }
    }
}