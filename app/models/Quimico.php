<?php
require_once __DIR__ . '/../config/Database.php';

class Quimico
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll()
    {
        $stmt = $this->db->query("SELECT * FROM quimicos ORDER BY nome ASC");
        return $stmt->fetchAll();
    }

    public function create($data)
    {
        $sql = "INSERT INTO quimicos (nome, quantidade, validade, tipo_armazenamento, periculosidade, estado) 
                VALUES (:nome, :quantidade, :validade, :tipo_armazenamento, :periculosidade, :estado)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nome' => $data['nome'],
            ':quantidade' => $data['quantidade'],
            ':validade' => !empty($data['validade']) ? $data['validade'] : null,
            ':tipo_armazenamento' => $data['tipo_armazenamento'],
            ':periculosidade' => $data['periculosidade'],
            ':estado' => $data['estado']
        ]);
    }

    // ------------------------------------------------------------------
    // Contagens para o Dashboard
    // ------------------------------------------------------------------
    public function countTotal()
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM quimicos")->fetchColumn();
    }

    public function countVencidos()
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM quimicos 
             WHERE validade IS NOT NULL AND validade < CURDATE()"
        )->fetchColumn();
    }

    public function countValidos()
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM quimicos 
             WHERE validade IS NOT NULL AND validade >= CURDATE()"
        )->fetchColumn();
    }

    // ------------------------------------------------------------------
    // Identifica o status igual à planilha do inventário
    // ------------------------------------------------------------------
    public static function calcularStatus($validade)
    {
        if (empty($validade)) {
            return ['status' => 'NÃO INFORMADO', 'classe' => 'bg-secondary text-white'];
        }
        $hoje = date('Y-m-d');
        if ($validade < $hoje) {
            return ['status' => 'VENCIDO', 'classe' => 'bg-danger text-white'];
        }
        return ['status' => 'VÁLIDO', 'classe' => 'bg-success text-white'];
    }
}