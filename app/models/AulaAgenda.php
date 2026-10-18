<?php
require_once __DIR__ . '/../config/Database.php';
class AulaAgenda
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function getPorDataOuFiltro($data = null, $materia = null)
    {
        $sql = "SELECT a.*, p.titulo as pratica_titulo 
                FROM aulas_agenda a
                LEFT JOIN praticas p ON a.pratica_id = p.id
                WHERE 1=1";
        $params = [];
        if (!empty($data)) {
            $sql .= " AND a.data_aula = :data";
            $params[':data'] = $data;
        }
        if (!empty($materia)) {
            $sql .= " AND a.materia LIKE :materia";
            $params[':materia'] = "%$materia%";
        }
        $sql .= " ORDER BY a.data_aula ASC, a.horario ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($dados)
    {
        $sql = "INSERT INTO aulas_agenda (data_aula, horario, materia, turma, professor, pratica_id, observacoes_adicionais) 
                VALUES (:data_aula, :horario, :materia, :turma, :professor, :pratica_id, :observacoes_adicionais)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':data_aula' => $dados['data_aula'],
            ':horario' => $dados['horario'],
            ':materia' => $dados['materia'],
            ':turma' => $dados['turma'],
            ':professor' => $dados['professor'],
            ':pratica_id' => !empty($dados['pratica_id']) ? $dados['pratica_id'] : null,
            ':observacoes_adicionais' => $dados['observacoes_adicionais'] ?? null
        ]);
    }
}