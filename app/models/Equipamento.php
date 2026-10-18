<?php
// Carrega a conexão com suporte a case-sensitive (Linux/Windows)
require_once __DIR__ . '/../config/Database.php';

class Equipamento
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    // ------------------------------------------------------------------
    // Consultas Gerais e Filtros
    // ------------------------------------------------------------------

    /**
     * Retorna todos os itens ou filtrados por tipo ('equipamento' ou 'vidraria')
     */
    public function getAll($tipo = null)
    {
        if ($tipo === 'equipamento') {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM equipamentos 
                 WHERE tipo = 'equipamento' 
                 ORDER BY proxima_manutencao ASC, nome ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($tipo === 'vidraria') {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM equipamentos 
                 WHERE tipo = 'vidraria' 
                 ORDER BY nome ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $stmt = $this->pdo->query("SELECT * FROM equipamentos ORDER BY tipo ASC, nome ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Localiza um equipamento ou vidraria pelo ID
     */
    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM equipamentos WHERE id = :id");
        $stmt->execute([':id' => (int) $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ------------------------------------------------------------------
    // Criação, Edição e Exclusão
    // ------------------------------------------------------------------

    /**
     * Cria um novo registro (Equipamento ou Vidraria)
     */
    public function create($data)
    {
        $tipo = $data['tipo'] ?? 'equipamento';

        // 1. Tratamento específico para Vidrarias
        if ($tipo === 'vidraria') {
            $qtdTotal = max(1, (int) ($data['quantidade_total'] ?? 1));

            $sql = "INSERT INTO equipamentos 
                    (tipo, nome, capacidade_volume, quantidade_total, quantidade_disponivel, quantidade_quebrada, quantidade_defeituosa, observacao) 
                    VALUES 
                    ('vidraria', :nome, :capacidade, :qtd_total, :qtd_disp, 0, 0, :obs)";

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':nome' => trim($data['nome']),
                ':capacidade' => !empty($data['capacidade_volume']) ? trim($data['capacidade_volume']) : null,
                ':qtd_total' => $qtdTotal,
                ':qtd_disp' => $qtdTotal,
                ':obs' => !empty($data['observacao']) ? trim($data['observacao']) : null
            ]);
        }

        // 2. Tratamento para Equipamentos (Manutenção Preventiva)
        $freq = !empty($data['frequencia_meses']) ? (int) $data['frequencia_meses'] : null;
        $ult = !empty($data['ultima_manutencao']) ? $data['ultima_manutencao'] : null;
        $prox = !empty($data['proxima_manutencao']) ? $data['proxima_manutencao'] : null;

        // Se a próxima data não for informada e houver periodicidade, calcula automaticamente
        if (empty($prox) && !empty($ult) && !empty($freq) && $freq > 0) {
            $prox = date('Y-m-d', strtotime("+{$freq} months", strtotime($ult)));
        }

        $sql = "INSERT INTO equipamentos 
                (tipo, nome, codigo_patrimonio, frequencia_meses, ultima_manutencao, proxima_manutencao, quantidade_total, quantidade_disponivel, observacao) 
                VALUES 
                ('equipamento', :nome, :cod, :freq, :ult, :prox, 1, 1, :obs)";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nome' => trim($data['nome']),
            ':cod' => !empty($data['codigo_patrimonio']) ? trim($data['codigo_patrimonio']) : null,
            ':freq' => $freq,
            ':ult' => $ult,
            ':prox' => $prox,
            ':obs' => !empty($data['observacao']) ? trim($data['observacao']) : null
        ]);
    }

    /**
     * Atualiza dados de um equipamento ou vidraria
     */
    public function update($id, $data)
    {
        $tipo = $data['tipo'] ?? 'equipamento';

        if ($tipo === 'vidraria') {
            $sql = "UPDATE equipamentos 
                    SET nome = :nome,
                        capacidade_volume = :capacidade,
                        observacao = :obs
                    WHERE id = :id AND tipo = 'vidraria'";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':id' => (int) $id,
                ':nome' => trim($data['nome']),
                ':capacidade' => !empty($data['capacidade_volume']) ? trim($data['capacidade_volume']) : null,
                ':obs' => !empty($data['observacao']) ? trim($data['observacao']) : null
            ]);
        }

        // Equipamento
        $freq = !empty($data['frequencia_meses']) ? (int) $data['frequencia_meses'] : null;
        $ult = !empty($data['ultima_manutencao']) ? $data['ultima_manutencao'] : null;
        $prox = !empty($data['proxima_manutencao']) ? $data['proxima_manutencao'] : null;

        if (empty($prox) && !empty($ult) && !empty($freq) && $freq > 0) {
            $prox = date('Y-m-d', strtotime("+{$freq} months", strtotime($ult)));
        }

        $sql = "UPDATE equipamentos 
                SET nome = :nome,
                    codigo_patrimonio = :cod,
                    frequencia_meses = :freq,
                    ultima_manutencao = :ult,
                    proxima_manutencao = :prox,
                    observacao = :obs
                WHERE id = :id AND tipo = 'equipamento'";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id' => (int) $id,
            ':nome' => trim($data['nome']),
            ':cod' => !empty($data['codigo_patrimonio']) ? trim($data['codigo_patrimonio']) : null,
            ':freq' => $freq,
            ':ult' => $ult,
            ':prox' => $prox,
            ':obs' => !empty($data['observacao']) ? trim($data['observacao']) : null
        ]);
    }

    /**
     * Exclui um item
     */
    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM equipamentos WHERE id = :id");
        return $stmt->execute([':id' => (int) $id]);
    }

    // ------------------------------------------------------------------
    // Métodos Específicos para Vidrarias & Avarias
    // ------------------------------------------------------------------

    /**
     * Consolida totais estatísticos do acervo de vidrarias
     */
    public function getVidrariasResumo()
    {
        $sql = "SELECT 
                    COALESCE(SUM(quantidade_total), 0) AS total_vidros,
                    COALESCE(SUM(quantidade_disponivel), 0) AS total_disponivel,
                    COALESCE(SUM(quantidade_quebrada), 0) AS total_quebrados,
                    COALESCE(SUM(quantidade_defeituosa), 0) AS total_defeitos
                FROM equipamentos 
                WHERE tipo = 'vidraria'";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Registra quebra ou defeito (trinca/fissura), abatendo do saldo disponível
     */
    public function registrarAvariaVidraria($id, $tipoAvaria, $qtd = 1)
    {
        $qtd = max(1, (int) $qtd);
        $campo = ($tipoAvaria === 'quebra') ? 'quantidade_quebrada' : 'quantidade_defeituosa';

        $sql = "UPDATE equipamentos 
                SET $campo = $campo + :qtd, 
                    quantidade_disponivel = GREATEST(0, quantidade_disponivel - :qtd) 
                WHERE id = :id AND tipo = 'vidraria'";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':qtd' => $qtd,
            ':id' => (int) $id
        ]);
    }

    /**
     * Registra compra/reposição de novas unidades de vidraria ao estoque
     */
    public function reporEstoqueVidraria($id, $qtdAdicionada = 1)
    {
        $qtdAdicionada = max(1, (int) $qtdAdicionada);
        $sql = "UPDATE equipamentos 
                SET quantidade_total = quantidade_total + :qtd, 
                    quantidade_disponivel = quantidade_disponivel + :qtd 
                WHERE id = :id AND tipo = 'vidraria'";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':qtd' => $qtdAdicionada,
            ':id' => (int) $id
        ]);
    }

    // ------------------------------------------------------------------
    // Contadores e Indicadores para Dashboard & Manutenção
    // ------------------------------------------------------------------

    /**
     * Contagem total de aparelhos/equipamentos
     */
    public function countTotal()
    {
        return (int) $this->pdo->query(
            "SELECT COUNT(*) FROM equipamentos WHERE tipo = 'equipamento'"
        )->fetchColumn();
    }

    /**
     * Contagem de manutenções vencidas
     */
    public function countAtrasadas()
    {
        $stmt = $this->pdo->query(
            "SELECT COUNT(*) FROM equipamentos 
             WHERE tipo = 'equipamento' 
               AND proxima_manutencao IS NOT NULL 
               AND proxima_manutencao < CURDATE()"
        );
        return (int) $stmt->fetchColumn();
    }

    /**
     * Lista os equipamentos com manutenção mais próxima ou vencida
     */
    public function getCriticos($limit = 5)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM equipamentos 
             WHERE tipo = 'equipamento' AND proxima_manutencao IS NOT NULL 
             ORDER BY proxima_manutencao ASC 
             LIMIT :lim"
        );
        $stmt->bindValue(':lim', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ------------------------------------------------------------------
    // Helpers Visuais de Status
    // ------------------------------------------------------------------

    /**
     * Identifica e formata o status com cor para Badges do Bootstrap
     */
    public static function calcularStatus($proxima_manutencao)
    {
        if (empty($proxima_manutencao)) {
            return ['status' => 'INSPEÇÃO MANUAL', 'classe' => 'bg-secondary text-white'];
        }

        $hoje = date('Y-m-d');
        if ($proxima_manutencao < $hoje) {
            return ['status' => 'REVISÃO ATRASADA', 'classe' => 'bg-danger text-white'];
        }

        // Alerta preventivo se vence nos próximos 30 dias
        $em30dias = date('Y-m-d', strtotime('+30 days'));
        if ($proxima_manutencao <= $em30dias) {
            return ['status' => 'PRÓXIMA DO VENCIMENTO', 'classe' => 'bg-warning text-dark'];
        }

        return ['status' => 'EM DIA', 'classe' => 'bg-success text-white'];
    }
}