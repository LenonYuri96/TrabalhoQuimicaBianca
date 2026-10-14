<?php
require_once __DIR__ . '/../config/Database.php';

class Ocorrencia
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll()
    {
        $stmt = $this->db->query("SELECT * FROM ocorrencias ORDER BY data_hora DESC");
        return $stmt->fetchAll();
    }

    public function create($data)
    {
        $sql = "INSERT INTO ocorrencias (relator_nome, local_ocorrido, data_hora, categoria, item_nome, descricao) 
                VALUES (:relator, :local, :data_hora, :categoria, :item_nome, :descricao)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':relator' => $data['relator_nome'],
            ':local' => $data['local_ocorrido'],
            ':data_hora' => $data['data_hora'],
            ':categoria' => $data['categoria'],
            ':item_nome' => $data['item_nome'],
            ':descricao' => $data['descricao']
        ]);
    }

    // ------------------------------------------------------------------
    // Contagem total para o Dashboard
    // ------------------------------------------------------------------
    public function countTotal()
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM ocorrencias")->fetchColumn();
    }

    // ------------------------------------------------------------------
    // Últimas ocorrências registradas
    // ------------------------------------------------------------------
    public function getRecentes($limit = 5)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM ocorrencias 
             ORDER BY data_hora DESC 
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ------------------------------------------------------------------
    // Total de ocorrências por categoria (útil para gráficos)
    // ------------------------------------------------------------------
    public function countPorCategoria()
    {
        $sql = "SELECT categoria, COUNT(*) AS total 
                FROM ocorrencias 
                GROUP BY categoria 
                ORDER BY total DESC";
        return $this->db->query($sql)->fetchAll();
    }

    // ------------------------------------------------------------------
    // Total de ocorrências no mês atual
    // ------------------------------------------------------------------
    public function countMesAtual()
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM ocorrencias 
             WHERE MONTH(data_hora) = MONTH(CURDATE()) 
               AND YEAR(data_hora)  = YEAR(CURDATE())"
        )->fetchColumn();
    }
}