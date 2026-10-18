<?php
require_once __DIR__ . '/../config/Database.php';
class Pratica
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function getAll()
    {
        return $this->pdo->query("SELECT * FROM praticas ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM praticas WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getItens($praticaId)
    {
        $sql = "SELECT pi.*, 
                CASE 
                    WHEN pi.tipo_item = 'quimico' THEN q.nome
                    WHEN pi.tipo_item IN ('equipamento', 'vidraria') THEN e.nome
                END as item_nome,
                CASE 
                    WHEN pi.tipo_item IN ('equipamento', 'vidraria') THEN e.capacidade_volume
                    ELSE NULL
                END as capacidade_volume
                FROM pratica_itens pi
                LEFT JOIN quimicos q ON pi.tipo_item = 'quimico' AND pi.item_id = q.id
                LEFT JOIN equipamentos e ON pi.tipo_item IN ('equipamento', 'vidraria') AND pi.item_id = e.id
                WHERE pi.pratica_id = :pratica_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':pratica_id' => $praticaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($dados, $itens = [])
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("INSERT INTO praticas (titulo, materia_curso, objetivo, procedimento) VALUES (:titulo, :materia_curso, :objetivo, :procedimento)");
            $stmt->execute([
                ':titulo' => $dados['titulo'],
                ':materia_curso' => $dados['materia_curso'],
                ':objetivo' => $dados['objetivo'],
                ':procedimento' => $dados['procedimento']
            ]);
            $praticaId = $this->pdo->lastInsertId();

            if (!empty($itens)) {
                $stmtItem = $this->pdo->prepare("INSERT INTO pratica_itens (pratica_id, tipo_item, item_id, quantidade_necessaria, observacao) VALUES (:pratica_id, :tipo_item, :item_id, :quantidade_necessaria, :observacao)");
                foreach ($itens as $item) {
                    if (!empty($item['item_id'])) {
                        $stmtItem->execute([
                            ':pratica_id' => $praticaId,
                            ':tipo_item' => $item['tipo_item'],
                            ':item_id' => $item['item_id'],
                            ':quantidade_necessaria' => $item['quantidade_necessaria'] ?? '1 un',
                            ':observacao' => $item['observacao'] ?? ''
                        ]);
                    }
                }
            }
            $this->pdo->commit();
            return $praticaId;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function getPraticasPorProduto($tipoItem, $itemId)
    {
        $sql = "SELECT p.*, pi.quantidade_necessaria 
                FROM praticas p 
                INNER JOIN pratica_itens pi ON p.id = pi.pratica_id 
                WHERE pi.tipo_item = :tipo AND pi.item_id = :item_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tipo' => $tipoItem, ':item_id' => $itemId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}