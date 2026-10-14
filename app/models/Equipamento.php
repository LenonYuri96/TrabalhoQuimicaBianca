<?php
require_once __DIR__ . '/../config/Database.php';

class Equipamento
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll()
    {
        $stmt = $this->db->query("SELECT * FROM equipamentos ORDER BY proxima_manutencao ASC");
        return $stmt->fetchAll();
    }

    public function create($data)
    {
        // Cálculo automático da próxima data com base na periodicidade (PDF 2)
        $prox = date('Y-m-d', strtotime("+{$data['frequencia_meses']} months", strtotime($data['ultima_manutencao'])));

        $sql = "INSERT INTO equipamentos (nome, codigo_patrimonio, frequencia_meses, ultima_manutencao, proxima_manutencao, observacao) 
                VALUES (:nome, :cod, :freq, :ult, :prox, :obs)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nome' => $data['nome'],
            ':cod' => $data['codigo_patrimonio'],
            ':freq' => $data['frequencia_meses'],
            ':ult' => $data['ultima_manutencao'],
            ':prox' => $prox,
            ':obs' => $data['observacao']
        ]);
    }

    // ------------------------------------------------------------------
    // Contagens para o Dashboard
    // ------------------------------------------------------------------
    public function countTotal()
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM equipamentos")->fetchColumn();
    }

    public function countAtrasadas()
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM equipamentos 
             WHERE proxima_manutencao IS NOT NULL AND proxima_manutencao < CURDATE()"
        )->fetchColumn();
    }

    // ------------------------------------------------------------------
    // Lista os equipamentos mais críticos (próximos do vencimento)
    // ------------------------------------------------------------------
    public function getCriticos($limit = 5)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM equipamentos 
             WHERE proxima_manutencao IS NOT NULL 
             ORDER BY proxima_manutencao ASC 
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ------------------------------------------------------------------
    // Identifica o status igual à planilha do inventário
    // ------------------------------------------------------------------
    public static function calcularStatus($proxima_manutencao)
    {
        if (empty($proxima_manutencao)) {
            return ['status' => 'NÃO INFORMADO', 'classe' => 'bg-secondary text-white'];
        }
        $hoje = date('Y-m-d');
        if ($proxima_manutencao < $hoje) {
            return ['status' => 'ATRASADA', 'classe' => 'bg-danger text-white'];
        }
        // Alerta se vence nos próximos 30 dias
        $em30dias = date('Y-m-d', strtotime('+30 days'));
        if ($proxima_manutencao <= $em30dias) {
            return ['status' => 'PRÓXIMA', 'classe' => 'bg-warning text-dark'];
        }
        return ['status' => 'EM DIA', 'classe' => 'bg-success text-white'];
    }
}