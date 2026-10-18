<?php
// ============================================================================
// LABMANAGER - CONTROLADOR DE INTELIGÊNCIA ARTIFICIAL E AUDITORIA LABORATORIAL
// ----------------------------------------------------------------------------
// Este controlador concentra toda a camada de IA (Google Gemini) e o Motor
// Heurístico Pericial de fallback. Funciona 100% offline com o banco de dados
// caso a chave de API não esteja configurada.
// ============================================================================

// Carrega a conexão com suporte a case-sensitive (Linux e Windows)
if (file_exists(__DIR__ . '/../config/Database.php')) {
    require_once __DIR__ . '/../config/Database.php';
} elseif (file_exists(__DIR__ . '/../config/database.php')) {
    require_once __DIR__ . '/../config/Database.php';
} else {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['erro' => 'Arquivo de conexão com o banco de dados não localizado.']);
    exit;
}

class AiController
{
    /**
     * Insira sua chave da API do Google Gemini aqui se desejar.
     * Se deixar em branco, o sistema utilizará o Motor Heurístico Pericial
     * integrado ao banco de dados (100% funcional offline).
     *
     * @var string
     */
    private $apiKey = '';

    /**
     * Instância PDO compartilhada por todos os métodos.
     *
     * @var PDO
     */
    private $pdo;

    /**
     * Endpoint base do modelo Gemini utilizado em todas as chamadas.
     *
     * @var string
     */
    private $geminiModel = 'gemini-2.5-flash';

    // ========================================================================
    // CONSTRUTOR
    // ========================================================================
    public function __construct()
    {
        try {
            $this->pdo = Database::getConnection();
            // Força UTF-8 para evitar problemas com acentuação nas respostas
            $this->pdo->exec("SET NAMES 'utf8mb4'");
        } catch (\Throwable $e) {
            error_log('[LabManager][AiController] Falha ao conectar no banco: ' . $e->getMessage());
            $this->pdo = null;
        }
    }

    // ========================================================================
    // 1. AUDITORIA GERAL DO LABORATÓRIO (INTEGRAÇÃO TOTAL AO BANCO DE DADOS)
    // Realiza uma varredura nas tabelas quimicos, equipamentos, ocorrencias e
    // aulas_agenda. Retorna score de conformidade, riscos e plano de ação.
    // ========================================================================
    public function auditoriaGeral()
    {
        header('Content-Type: application/json; charset=utf-8');

        $hoje = date('Y-m-d');
        $em30Dias = date('Y-m-d', strtotime('+30 days'));

        $quimicosVencidos = [];
        $quimicosVencendo = [];
        $equipamentosAtrasados = [];
        $vidrariasAvariadas = [];
        $ocorrenciasPendentes = [];

        try {
            // 1.1 Químicos Vencidos
            $stmtVencidos = $this->pdo->prepare(
                "SELECT nome, quantidade, periculosidade, validade
                 FROM quimicos
                 WHERE validade IS NOT NULL AND validade < :hoje
                 ORDER BY validade ASC"
            );
            $stmtVencidos->execute([':hoje' => $hoje]);
            $quimicosVencidos = $stmtVencidos->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // 1.2 Químicos Próximos do Vencimento (janela de 30 dias)
            $stmtVencendo = $this->pdo->prepare(
                "SELECT nome, validade
                 FROM quimicos
                 WHERE validade IS NOT NULL AND validade BETWEEN :hoje AND :em30
                 ORDER BY validade ASC"
            );
            $stmtVencendo->execute([':hoje' => $hoje, ':em30' => $em30Dias]);
            $quimicosVencendo = $stmtVencendo->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // 1.3 Equipamentos com Manutenção Preventiva Atrasada
            $stmtEquipAtrasados = $this->pdo->prepare(
                "SELECT nome, codigo_patrimonio, proxima_manutencao
                 FROM equipamentos
                 WHERE tipo = 'equipamento'
                   AND proxima_manutencao IS NOT NULL
                   AND proxima_manutencao < :hoje
                 ORDER BY proxima_manutencao ASC"
            );
            $stmtEquipAtrasados->execute([':hoje' => $hoje]);
            $equipamentosAtrasados = $stmtEquipAtrasados->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // 1.4 Vidrarias Danificadas (Quebras ou Trincas) - top 5 mais críticas
            $stmtVidrarias = $this->pdo->query(
                "SELECT nome, capacidade_volume, quantidade_quebrada,
                        quantidade_defeituosa, quantidade_disponivel
                 FROM equipamentos
                 WHERE tipo = 'vidraria'
                   AND (quantidade_quebrada > 0 OR quantidade_defeituosa > 0)
                 ORDER BY quantidade_quebrada DESC
                 LIMIT 5"
            );
            $vidrariasAvariadas = $stmtVidrarias ? ($stmtVidrarias->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];

            // 1.5 Ocorrências em Aberto
            $stmtOcorrencias = $this->pdo->query(
                "SELECT categoria, item_nome, descricao, status
                 FROM ocorrencias
                 WHERE status != 'Resolvido'
                 ORDER BY id DESC
                 LIMIT 5"
            );
            $ocorrenciasPendentes = $stmtOcorrencias ? ($stmtOcorrencias->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];

        } catch (\PDOException $e) {
            error_log('[LabManager][auditoriaGeral] ' . $e->getMessage());
        }

        $qtdVencidos = count($quimicosVencidos);
        $qtdVencendo = count($quimicosVencendo);
        $qtdEquipAtrasados = count($equipamentosAtrasados);
        $qtdOcorrencias = count($ocorrenciasPendentes);

        $nomesVencidos = !empty($quimicosVencidos)
            ? implode(', ', array_column($quimicosVencidos, 'nome'))
            : 'Nenhum';

        $nomesEquipAtrasados = !empty($equipamentosAtrasados)
            ? implode(', ', array_column($equipamentosAtrasados, 'nome'))
            : 'Nenhum';

        $nomesVidrariasQuebradas = !empty($vidrariasAvariadas)
            ? implode(', ', array_map(function ($v) {
                return "{$v['nome']} ({$v['quantidade_quebrada']} quebradas, {$v['quantidade_defeituosa']} trincadas)";
            }, $vidrariasAvariadas))
            : 'Nenhuma avaria crítica';

        $nomesVencendo = !empty($quimicosVencendo)
            ? implode(', ', array_map(function ($q) {
                return "{$q['nome']} (vence em {$q['validade']})";
            }, $quimicosVencendo))
            : 'Nenhum';

        // Tentativa de consulta via IA Generativa externa
        $resposta = null;
        if (!empty($this->apiKey)) {
            $prompt = "Você é um auditor químico sênior e especialista em conformidade laboratorial (normas ABNT e ISO 17025).
Analise o diagnóstico operacional extraído diretamente do banco de dados:
- Reagentes Vencidos ({$qtdVencidos}): {$nomesVencidos}
- Reagentes Próximos do Vencimento em 30 dias ({$qtdVencendo}): {$nomesVencendo}
- Equipamentos com Preventiva Atrasada ({$qtdEquipAtrasados}): {$nomesEquipAtrasados}
- Vidrarias com Quebras/Defeitos: {$nomesVidrariasQuebradas}
- Ocorrências/Incidentes pendentes: {$qtdOcorrencias}

Responda ESTRITAMENTE em formato JSON com as seguintes chaves:
{
  \"score\": (número inteiro de 0 a 100 indicando a conformidade),
  \"status_geral\": (\"Excelente\", \"Bom\", \"Alerta\" ou \"Crítico\"),
  \"diagnostico\": (resumo executivo de 2 frases citando nominalmente os itens mais críticos),
  \"riscos_principais\": [3 strings objetivas de riscos operacionais/químicos reais],
  \"plano_acao\": [3 ações corretivas prioritárias citando os itens do laboratório]
}";
            $resposta = $this->executarGemini($prompt);
        }

        // Se a chave não estiver preenchida ou a API externa falhar, usa o Motor Heurístico
        if (empty($resposta) || isset($resposta['erro'])) {
            $score = 100
                - ($qtdVencidos * 7)
                - ($qtdEquipAtrasados * 9)
                - ($qtdOcorrencias * 5)
                - ($qtdVencendo * 2);
            $score = max(10, min(100, $score));

            if ($score >= 80) {
                $status = 'Bom';
            } elseif ($score >= 50) {
                $status = 'Alerta';
            } else {
                $status = 'Crítico';
            }

            $diagnostico = "Laboratório em nível operacional {$status} (Índice {$score}/100). ";
            if ($qtdVencidos > 0) {
                $diagnostico .= "Constatado(s) reagente(s) vencido(s): {$nomesVencidos}. ";
            }
            if ($qtdEquipAtrasados > 0) {
                $diagnostico .= "Aparelhos com prazo de calibração/preventiva expirado: {$nomesEquipAtrasados}. ";
            }
            if ($qtdVencidos === 0 && $qtdEquipAtrasados === 0) {
                $diagnostico .= "Os reagentes e equipamentos operam dentro dos prazos de conformidade vigentes. ";
            }
            if ($qtdVencendo > 0) {
                $diagnostico .= "Atenção ao(s) item(ns) com vencimento nos próximos 30 dias: {$nomesVencendo}.";
            }

            $riscos = [];
            if ($qtdVencidos > 0) {
                $riscos[] = "Perda de reprodutibilidade analítica e risco químico pelo uso de: {$nomesVencidos}";
            } else {
                $riscos[] = "Controle de validade em conformidade; atentar para produtos próximos do vencimento";
            }

            if ($qtdEquipAtrasados > 0) {
                $riscos[] = "Risco de descalibração ou falha no sistema de exaustão em: {$nomesEquipAtrasados}";
            } else {
                $riscos[] = "Cronograma de preventivas em dia, minimizando paradas operacionais";
            }

            if (!empty($vidrariasAvariadas)) {
                $riscos[] = "Possibilidade de acidentes com perfurocortantes devido a peças avariadas em estoque";
            } else {
                $riscos[] = "Índice de perda por quebras controlado no acervo de vidrarias";
            }

            $plano = [];
            if ($qtdVencidos > 0) {
                $plano[] = "Segregar e identificar imediatamente {$nomesVencidos} para tratamento ou descarte homologado";
            } else {
                $plano[] = "Realizar inspeção periódica nos armários de reagentes para garantir rastreabilidade";
            }

            if ($qtdEquipAtrasados > 0) {
                $plano[] = "Etiquetar e acionar calibração ou manutenção preventiva para: {$nomesEquipAtrasados}";
            } else {
                $plano[] = "Registrar as checagens diárias de zero na balança analítica antes das práticas";
            }

            $plano[] = "Revisar as fichas de segurança e o uso correto de EPIs antes do início das próximas aulas práticas";

            $resposta = [
                'score' => $score,
                'status_geral' => $status,
                'diagnostico' => trim($diagnostico),
                'riscos_principais' => $riscos,
                'plano_acao' => $plano,
                'estatisticas_banco' => [
                    'vencidos' => $qtdVencidos,
                    'vencendo_30d' => $qtdVencendo,
                    'atrasados' => $qtdEquipAtrasados,
                    'ocorrencias' => $qtdOcorrencias,
                    'vidrarias_avariadas' => count($vidrariasAvariadas)
                ]
            ];
        }

        echo json_encode($resposta, JSON_UNESCAPED_UNICODE);
    }

    // ========================================================================
    // 2. CONSULTA DE FISPQ / FDS INTEGRADA AO BANCO DE DADOS
    // Baseada na ABNT NBR 14725 e cruzada com os dados de armazenamento real.
    // ========================================================================
    public function consultarQuimico()
    {
        header('Content-Type: application/json; charset=utf-8');
        $input = json_decode(file_get_contents('php://input'), true);
        $nome = trim($input['nome'] ?? '');

        if ($nome === '') {
            echo json_encode(['erro' => 'Nome do reagente não informado'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $quimicoDb = null;
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM quimicos WHERE nome LIKE :nome LIMIT 1");
            $stmt->execute([':nome' => "%{$nome}%"]);
            $quimicoDb = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            error_log('[LabManager][consultarQuimico] ' . $e->getMessage());
        }

        $estadoDb = $quimicoDb['estado'] ?? 'Liquido/Pó';
        $armazenamentoDb = $quimicoDb['tipo_armazenamento'] ?? 'Geral';
        $validadeDb = $quimicoDb['validade'] ?? null;
        $isVencido = (!empty($validadeDb) && $validadeDb < date('Y-m-d'));

        $resposta = null;
        if (!empty($this->apiKey)) {
            $prompt = "Você é um perito em segurança química e toxicologia.
Forneça as diretrizes de segurança (FISPQ/FDS - Norma ABNT NBR 14725) para: '{$nome}'.
Estado Físico em estoque: {$estadoDb}. Local de armazenamento: {$armazenamentoDb}.
Responda ESTRITAMENTE em formato JSON com as chaves:
'periculosidade_resumo' (classe de perigo principal com frases GHS),
'epis_obrigatorios' (array de strings com EPIs recomendados),
'incompatibilidades' (com quais famílias químicas não misturar),
'protocolo_descarte' (como neutralizar ou destinar conforme normas ambientais).";

            $resposta = $this->executarGemini($prompt);
        }

        if (empty($resposta) || isset($resposta['erro'])) {
            $nomeLower = mb_strtolower($nome, 'UTF-8');
            $perigo = 'Químico Geral / Cuidado no Manuseio de Bancada';
            $incomp = 'Ácidos concentrados, bases fortes, materiais combustíveis e fontes diretas de calor.';
            $descarte = 'Armazenar em bombona identificada de resíduos químicos para coprocessamento ou incineração homologada.';
            $epis = [
                'Óculos de ampla visão com proteção lateral',
                'Luvas de nitrila',
                'Jaleco 100% algodão de manga longa'
            ];

            if (strpos($nomeLower, 'cloridrico') !== false || strpos($nomeLower, 'clorídrico') !== false || preg_match('/\bhcl\b/', $nomeLower)) {
                $perigo = 'Corrosivo Categoria 1 (H314: Causa queimaduras severas na pele e lesões oculares graves)';
                $incomp = 'Metais oxidáveis, bases concentradas, cianetos, sulfetos e agentes oxidantes fortes (libera cloro gasoso).';
                $descarte = 'Neutralizar lentamente com bicarbonato de sódio em banho de gelo até pH 6-8 ou recolher para tratamento de efluente industrial.';
                $epis = [
                    'Capela de exaustão ligada',
                    'Luvas de borracha nitrílica pesada ou neoprene',
                    'Protetor facial ou óculos de ampla visão',
                    'Jaleco antiácido'
                ];
            } elseif (strpos($nomeLower, 'sodio') !== false || strpos($nomeLower, 'sódio') !== false || strpos($nomeLower, 'naoh') !== false) {
                $perigo = 'Corrosivo e Higroscópico Forte (H314: Dissolução com forte liberação exotérmica de calor)';
                $incomp = 'Ácidos fortes (reação violenta com respingos), alumínio, zinco e solventes clorados.';
                $descarte = 'Neutralizar com ácido clorídrico diluído sob agitação e resfriamento até pH neutro (7,0) antes de destinar a resíduo aquoso alcalino.';
                $epis = [
                    'Óculos de vedação química',
                    'Luvas de nitrila de alta espessura',
                    'Jaleco de algodão abotoado'
                ];
            } elseif (strpos($nomeLower, 'fenolftaleina') !== false || strpos($nomeLower, 'fenolftaleína') !== false) {
                $perigo = 'Líquido Inflamável (solução hidroalcoólica) e suspeito de efeitos mutagênicos em altas concentrações';
                $incomp = 'Oxidantes fortes, peróxidos e fontes de ignição ou calor.';
                $descarte = 'Armazenar em frasco âmbar para solventes orgânicos inflamáveis não halogenados. Não descartar na pia.';
                $epis = [
                    'Luvas de nitrila descartáveis',
                    'Óculos de proteção lateral',
                    'Máscara para vapores orgânicos (em preparo de soluções grandes)'
                ];
            } elseif (strpos($nomeLower, 'dicromato') !== false || strpos($nomeLower, 'cromo') !== false) {
                $perigo = 'Carcinogênico, Tóxico por Inalação e Oxidante Forte (Cromo Hexavalente Cr VI)';
                $incomp = 'Solventes orgânicos, materiais combustíveis, substâncias redutoras e ácidos fortes.';
                $descarte = 'Redução de Cr(VI) para Cr(III) com bissulfito de sódio em meio ácido, seguida de precipitação alcalina como Cr(OH)3. Proibido qualquer descarte em rede comum.';
                $epis = [
                    'Respirador P3 contra poeiras/aerossóis tóxicos',
                    'Luvas de neoprene ou nitrila pesada',
                    'Avental impermeável e capela de exaustão'
                ];
            } elseif (strpos($nomeLower, 'metanol') !== false || strpos($nomeLower, 'metilico') !== false || strpos($nomeLower, 'metílico') !== false) {
                $perigo = 'Inflamável Categoria 2 e Tóxico por Inalação, Ingestão ou Absorção Cutânea';
                $incomp = 'Oxidantes fortes, ácidos concentrados, halogênios e metais alcalinos.';
                $descarte = 'Segregar na bombona de solventes orgânicos inflamáveis para recuperação ou incineração.';
                $epis = [
                    'Capela de exaustão química',
                    'Luvas de borracha butílica',
                    'Óculos panorâmicos'
                ];
            } elseif (strpos($nomeLower, 'acetona') !== false || strpos($nomeLower, 'etanol') !== false || strpos($nomeLower, 'alcool') !== false || strpos($nomeLower, 'álcool') !== false) {
                $perigo = 'Líquido Altamente Inflamável (H225) - Risco de ignição por faísca ou calor';
                $incomp = 'Agentes oxidantes fortes (peróxidos, permanganatos, dicromatos), ácidos concentrados e metais alcalinos.';
                $descarte = 'Segregar em bombona de solventes orgânicos inflamáveis para recuperação por destilação ou incineração homologada.';
                $epis = [
                    'Capela de exaustão ligada',
                    'Luvas de nitrila',
                    'Óculos de proteção lateral',
                    'Jaleco de algodão'
                ];
            } elseif (strpos($nomeLower, 'sulfuric') !== false || strpos($nomeLower, 'sulfúric') !== false) {
                $perigo = 'Corrosivo Categoria 1A e Reativo com Água (H314 + H290)';
                $incomp = 'Bases fortes, metais, materiais orgânicos, cianetos e agentes oxidantes. Reação violenta com água.';
                $descarte = 'Adicionar MUITO lentamente sobre água gelada (nunca o contrário) e neutralizar com carbonato de cálcio até pH 6-9.';
                $epis = [
                    'Protetor facial completo',
                    'Luvas de neoprene longas',
                    'Avental e botas impermeáveis',
                    'Capela de exaustão ligada'
                ];
            } elseif (strpos($nomeLower, 'nitric') !== false || strpos($nomeLower, 'nítric') !== false) {
                $perigo = 'Corrosivo e Oxidante Forte (H272 + H314) - Libera vapores nitrosos tóxicos';
                $incomp = 'Solventes orgânicos, materiais combustíveis, metais em pó, álcoois e agentes redutores.';
                $descarte = 'Neutralizar com bicarbonato de sódio em solução diluída sob capela. Nunca misturar com solventes orgânicos.';
                $epis = [
                    'Capela de exaustão ligada',
                    'Respirador com filtro para vapores ácidos',
                    'Luvas de nitrila pesada',
                    'Protetor facial'
                ];
            } elseif (strpos($nomeLower, 'amonia') !== false || strpos($nomeLower, 'amônia') !== false || strpos($nomeLower, 'amoniac') !== false) {
                $perigo = 'Gás Tóxico e Corrosivo quando concentrado (H331 + H314)';
                $incomp = 'Ácidos (reação violenta), halogênios, oxidantes fortes e metais como cobre e prata.';
                $descarte = 'Diluir abundantemente em água e neutralizar com ácido clorídrico diluído sob capela.';
                $epis = [
                    'Capela de exaustão ligada',
                    'Respirador com filtro para amônia',
                    'Luvas de butila',
                    'Óculos de vedação química'
                ];
            }

            $resposta = [
                'reagente' => $nome,
                'periculosidade_resumo' => $perigo . ($isVencido ? ' [ALERTA: PRODUTO VENCIDO NO ESTOQUE]' : ''),
                'incompatibilidades' => $incomp,
                'protocolo_descarte' => $descarte,
                'epis_obrigatorios' => $epis
            ];
        }

        echo json_encode($resposta, JSON_UNESCAPED_UNICODE);
    }

    // ========================================================================
    // 3. ANÁLISE DE VIABILIDADE E RISCO DE AULA PRÁTICA
    // Cruza os itens necessários no roteiro da prática com os estoques reais.
    // ========================================================================
    public function analisarViabilidadePratica()
    {
        header('Content-Type: application/json; charset=utf-8');
        $praticaId = (int) ($_GET['id'] ?? 0);

        if ($praticaId <= 0) {
            echo json_encode(['erro' => 'Identificador da prática não informado'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $stmtP = $this->pdo->prepare("SELECT * FROM praticas WHERE id = :id");
            $stmtP->execute([':id' => $praticaId]);
            $pratica = $stmtP->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            echo json_encode(['erro' => 'Erro ao consultar a prática'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!$pratica) {
            echo json_encode(['erro' => 'Prática não encontrada'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $stmtItens = $this->pdo->prepare("SELECT * FROM pratica_itens WHERE pratica_id = :id");
            $stmtItens->execute([':id' => $praticaId]);
            $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            $itens = [];
        }

        $impeditivos = [];
        $alertas = [];
        $hoje = date('Y-m-d');

        foreach ($itens as $it) {
            $tipo = $it['tipo_item'] ?? '';
            $itemId = (int) ($it['item_id'] ?? 0);

            if ($tipo === 'quimico') {
                try {
                    $stmtQ = $this->pdo->prepare("SELECT * FROM quimicos WHERE id = :id");
                    $stmtQ->execute([':id' => $itemId]);
                    $q = $stmtQ->fetch(PDO::FETCH_ASSOC);
                } catch (\PDOException $e) {
                    $q = null;
                }
                if ($q) {
                    if (!empty($q['validade']) && $q['validade'] < $hoje) {
                        $impeditivos[] = "Reagente <strong>{$q['nome']}</strong> está EXPIRADO ({$q['validade']}). Risco de erro analítico e contaminação.";
                    }
                    if ((float) $q['quantidade'] <= 0) {
                        $alertas[] = "Estoque do reagente <strong>{$q['nome']}</strong> está zerado ou em quantidade insuficiente.";
                    }
                } else {
                    $alertas[] = "Reagente com ID {$itemId} não localizado no cadastro de químicos.";
                }

            } elseif ($tipo === 'vidraria') {
                try {
                    $stmtV = $this->pdo->prepare("SELECT * FROM equipamentos WHERE id = :id AND tipo = 'vidraria'");
                    $stmtV->execute([':id' => $itemId]);
                    $v = $stmtV->fetch(PDO::FETCH_ASSOC);
                } catch (\PDOException $e) {
                    $v = null;
                }
                if ($v) {
                    if ((int) $v['quantidade_disponivel'] <= 0) {
                        $impeditivos[] = "Vidraria <strong>{$v['nome']}</strong> não possui peças intactas disponíveis no laboratório.";
                    } elseif ((int) $v['quantidade_defeituosa'] > 0) {
                        $alertas[] = "Vidraria <strong>{$v['nome']}</strong> possui {$v['quantidade_defeituosa']} peça(s) com trincas/fissuras no lote.";
                    }
                } else {
                    $alertas[] = "Vidraria com ID {$itemId} não localizada no cadastro de equipamentos.";
                }

            } elseif ($tipo === 'equipamento') {
                try {
                    $stmtE = $this->pdo->prepare("SELECT * FROM equipamentos WHERE id = :id AND tipo = 'equipamento'");
                    $stmtE->execute([':id' => $itemId]);
                    $e = $stmtE->fetch(PDO::FETCH_ASSOC);
                } catch (\PDOException $e) {
                    $e = null;
                }
                if ($e) {
                    if (!empty($e['proxima_manutencao']) && $e['proxima_manutencao'] < $hoje) {
                        $alertas[] = "Equipamento <strong>{$e['nome']}</strong> está com a revisão preventiva atrasada.";
                    }
                }
            }
        }

        $aprovado = empty($impeditivos);

        echo json_encode([
            'pratica_id' => $praticaId,
            'pratica_titulo' => $pratica['titulo'] ?? 'Prática sem título',
            'aprovado' => $aprovado,
            'status' => $aprovado ? 'LIBERADA PARA MONTAGEM' : 'BLOQUEIO ANALÍTICO IDENTIFICADO',
            'impeditivos' => $impeditivos,
            'alertas' => $alertas,
            'parecer_ia' => $aprovado
                ? 'Todos os reagentes e materiais requisitados estão com status regular para execução da bancada.'
                : 'Existem pendências críticas de segurança ou insumos vencidos que impedem a realização segura do roteiro.'
        ], JSON_UNESCAPED_UNICODE);
    }

    // ========================================================================
    // 4. AUTO-PREENCHIMENTO INTELIGENTE NO CADASTRO DE REAGENTES
    // ========================================================================
    public function sugerirDadosQuimico()
    {
        header('Content-Type: application/json; charset=utf-8');
        $input = json_decode(file_get_contents('php://input'), true);
        $nome = mb_strtolower(trim($input['nome'] ?? ''), 'UTF-8');

        $dados = [
            'estado' => 'Pó',
            'tipo_armazenamento' => 'Armário Geral de Reagentes',
            'periculosidade' => 'Baixa / Irritante Leve'
        ];

        if ($nome === '') {
            echo json_encode($dados, JSON_UNESCAPED_UNICODE);
            return;
        }

        if (strpos($nome, 'acido') !== false || strpos($nome, 'ácido') !== false) {
            $dados['estado'] = 'Liquido';
            $dados['tipo_armazenamento'] = 'Armário Específico para Ácidos';
            $dados['periculosidade'] = 'Altamente Corrosivo';
        } elseif (
            strpos($nome, 'alcool') !== false || strpos($nome, 'álcool') !== false ||
            strpos($nome, 'solucao') !== false || strpos($nome, 'solução') !== false ||
            strpos($nome, 'eter') !== false || strpos($nome, 'éter') !== false ||
            strpos($nome, 'acetona') !== false || strpos($nome, 'hexano') !== false ||
            strpos($nome, 'tolueno') !== false || strpos($nome, 'cloroformio') !== false
        ) {
            $dados['estado'] = 'Liquido';
            $dados['tipo_armazenamento'] = 'Armário Corta-Fogo de Solventes';
            $dados['periculosidade'] = 'Líquido Inflamável / Volátil';
        } elseif (
            strpos($nome, 'hidroxido') !== false || strpos($nome, 'hidróxido') !== false ||
            strpos($nome, 'soda') !== false || strpos($nome, 'carbonato de sodio') !== false ||
            strpos($nome, 'carbonato de sódio') !== false
        ) {
            $dados['estado'] = 'Pó';
            $dados['tipo_armazenamento'] = 'Armário de Bases e Alcalinos';
            $dados['periculosidade'] = 'Corrosivo / Alcalino Cáustico';
        } elseif (
            strpos($nome, 'cromato') !== false || strpos($nome, 'dicromato') !== false ||
            strpos($nome, 'prata') !== false || strpos($nome, 'mercurio') !== false ||
            strpos($nome, 'mercúrio') !== false || strpos($nome, 'chumbo') !== false
        ) {
            $dados['tipo_armazenamento'] = 'Frasco Âmbar em Armário Trancado';
            $dados['periculosidade'] = 'Tóxico / Nocivo ao Meio Ambiente';
        } elseif (
            strpos($nome, 'lupulo') !== false || strpos($nome, 'lúpulo') !== false ||
            strpos($nome, 'malte') !== false || strpos($nome, 'levedura') !== false ||
            strpos($nome, 'extrato') !== false
        ) {
            $dados['estado'] = 'Granulo';
            $dados['tipo_armazenamento'] = 'Câmara Fria / Ambiente Seco';
            $dados['periculosidade'] = 'Inerte / Grau Alimentício';
        } elseif (strpos($nome, 'nitrato') !== false || strpos($nome, 'permanganato') !== false || strpos($nome, 'peroxido') !== false || strpos($nome, 'peróxido') !== false) {
            $dados['estado'] = 'Sólido Cristalino';
            $dados['tipo_armazenamento'] = 'Armário de Oxidantes (segregado)';
            $dados['periculosidade'] = 'Oxidante Forte - Risco de Incêndio em Contato com Matéria Orgânica';
        } elseif (strpos($nome, 'indicador') !== false || strpos($nome, 'fenolftaleina') !== false || strpos($nome, 'fenolftaleína') !== false || strpos($nome, 'metilorange') !== false) {
            $dados['estado'] = 'Liquido';
            $dados['tipo_armazenamento'] = 'Prateleira de Indicadores e Soluções';
            $dados['periculosidade'] = 'Baixa / Solução Alcoólica Inflamável';
        }

        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    }

    // ========================================================================
    // 5. ANÁLISE DE OCORRÊNCIAS COM HISTÓRICO DE REINCIDÊNCIA
    // ========================================================================
    public function analisarOcorrencia()
    {
        header('Content-Type: application/json; charset=utf-8');
        $input = json_decode(file_get_contents('php://input'), true);

        $item = trim($input['item_nome'] ?? 'Item');
        $categoria = trim($input['categoria'] ?? 'Geral');
        $descricao = mb_strtolower(trim($input['descricao'] ?? ''), 'UTF-8');

        $reincidencias = 0;
        try {
            $stmtHist = $this->pdo->prepare("SELECT COUNT(*) FROM ocorrencias WHERE item_nome LIKE :nome");
            $stmtHist->execute([':nome' => "%{$item}%"]);
            $reincidencias = (int) $stmtHist->fetchColumn();
        } catch (\PDOException $e) {
            $reincidencias = 0;
        }

        $gravidade = 'Média';
        $causa = 'Fadiga estrutural de material ou manuseio com técnica inadequada.';
        $acao = 'Isolar o item envolvido, registrar a baixa no sistema e revisar os lotes similares.';

        if (
            strpos($descricao, 'quebr') !== false ||
            strpos($descricao, 'estilhac') !== false ||
            strpos($descricao, 'estilhaç') !== false ||
            strpos($descricao, 'trinc') !== false
        ) {
            $gravidade = 'Média';
            $causa = 'Choque mecânico pontual ou choque térmico por aquecimento/resfriamento brusco.';
            $acao = 'Descartar fragmentos em caixa rígida para perfurocortantes. Atualizar saldo no controle de vidrarias.';

        } elseif (
            strpos($descricao, 'fogo') !== false ||
            strpos($descricao, 'fumaca') !== false ||
            strpos($descricao, 'fumaça') !== false ||
            strpos($descricao, 'curto') !== false ||
            strpos($descricao, 'queimad') !== false ||
            strpos($descricao, 'explos') !== false
        ) {
            $gravidade = 'Crítica';
            $causa = 'Sobrecarga de circuito elétrico, resistência em curto ou superaquecimento de enrolamento.';
            $acao = 'Desconectar da rede imediatamente, etiquetar com placa de BLOQUEIO e acionar manutenção técnica.';

        } elseif (
            strpos($descricao, 'derram') !== false ||
            strpos($descricao, 'vazou') !== false ||
            strpos($descricao, 'vazament') !== false ||
            strpos($descricao, 'derramament') !== false
        ) {
            $gravidade = 'Alta';
            $causa = 'Frasco danificado, vedação comprometida ou falta de contenção secundária durante o transporte.';
            $acao = 'Evacuar a bancada, neutralizar com vermiculita/agente absorvente e transferir para frasco íntegro sob capela.';

        } elseif (
            strpos($descricao, 'resping') !== false ||
            strpos($descricao, 'queimadura quimic') !== false ||
            strpos($descricao, 'pele') !== false ||
            strpos($descricao, 'olho') !== false
        ) {
            $gravidade = 'Crítica';
            $causa = 'Falta de EPI adequado ou manuseio incorreto de reagente corrosivo sem capela ligada.';
            $acao = 'Acionar lava-olhos/chuveiro de emergência por 15 minutos, remover roupas contaminadas e encaminhar para atendimento médico imediato.';

        } elseif (
            strpos($descricao, 'reagente vencid') !== false ||
            strpos($descricao, 'validade') !== false ||
            strpos($descricao, 'expirad') !== false
        ) {
            $gravidade = 'Alta';
            $causa = 'Falha no controle de rastreabilidade e ausência de inspeção periódica de validade.';
            $acao = 'Segregar imediatamente o reagente, registrar a baixa no sistema e revisar o inventário do armário.';

        } elseif (
            strpos($descricao, 'falta') !== false ||
            strpos($descricao, 'insuficient') !== false ||
            strpos($descricao, 'estoque') !== false
        ) {
            $gravidade = 'Baixa';
            $causa = 'Consumo acima do previsto no roteiro ou ausência de reposição programada.';
            $acao = 'Ajustar o estoque mínimo no sistema e solicitar reposição com antecedência mínima de 7 dias.';
        }

        if ($reincidencias >= 2) {
            $causa .= " [ALERTA DE REINCIDÊNCIA: Este item possui {$reincidencias} registros anteriores de ocorrência no banco].";
        }

        echo json_encode([
            'item' => $item,
            'categoria' => $categoria,
            'gravidade' => $gravidade,
            'causa_raiz' => $causa,
            'acao_imediata' => $acao,
            'reincidencias_banco' => $reincidencias
        ], JSON_UNESCAPED_UNICODE);
    }

    // ========================================================================
    // 6. GERADOR AUTOMÁTICO DE ROTEIROS DE PRÁTICA (CONSULTANDO ESTOQUE REAL)
    // ========================================================================
    public function gerarRoteiroPraticaIa()
    {
        header('Content-Type: application/json; charset=utf-8');
        $input = json_decode(file_get_contents('php://input'), true);
        $tema = trim($input['tema'] ?? 'Química Geral');
        $curso = trim($input['curso'] ?? 'Engenharia / Farmácia');

        if ($tema === '') {
            $tema = 'Química Geral';
        }

        $hoje = date('Y-m-d');
        $quimicosDisponiveis = [];
        $vidrariasDisponiveis = [];
        $equipamentosDisponiveis = [];

        try {
            $stmtQ = $this->pdo->prepare(
                "SELECT id, nome, quantidade, estado
                 FROM quimicos
                 WHERE (validade IS NULL OR validade >= :hoje)
                   AND quantidade > 0
                 ORDER BY nome ASC"
            );
            $stmtQ->execute([':hoje' => $hoje]);
            $quimicosDisponiveis = $stmtQ->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $stmtV = $this->pdo->query(
                "SELECT id, nome, capacidade_volume, quantidade_disponivel
                 FROM equipamentos
                 WHERE tipo = 'vidraria'
                   AND quantidade_disponivel > 0
                 ORDER BY nome ASC"
            );
            $vidrariasDisponiveis = $stmtV ? ($stmtV->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];

            $stmtE = $this->pdo->query(
                "SELECT id, nome
                 FROM equipamentos
                 WHERE tipo = 'equipamento'
                 ORDER BY nome ASC"
            );
            $equipamentosDisponiveis = $stmtE ? ($stmtE->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
        } catch (\PDOException $e) {
            error_log('[LabManager][gerarRoteiroPraticaIa] ' . $e->getMessage());
        }

        if (!empty($this->apiKey)) {
            $contextoQuimicos = json_encode(array_slice($quimicosDisponiveis, 0, 25), JSON_UNESCAPED_UNICODE);
            $contextoVidrarias = json_encode(array_slice($vidrariasDisponiveis, 0, 20), JSON_UNESCAPED_UNICODE);
            $contextoEquipamentos = json_encode(array_slice($equipamentosDisponiveis, 0, 15), JSON_UNESCAPED_UNICODE);

            $prompt = "Você é um professor universitário e coordenador de laboratórios de química.
Crie um roteiro experimental completo para o tema: '{$tema}' voltado ao curso/disciplina: '{$curso}'.
Regra OBRIGATÓRIA: utilize PREFERENCIALMENTE os reagentes e vidrarias deste estoque real do laboratório:
Reagentes disponíveis: {$contextoQuimicos}
Vidrarias disponíveis: {$contextoVidrarias}
Equipamentos disponíveis: {$contextoEquipamentos}

Responda ESTRITAMENTE em formato JSON com a seguinte estrutura:
{
  \"titulo\": (título acadêmico do experimento),
  \"materia_curso\": \"{$curso}\",
  \"objetivo\": (objetivo pedagógico e analítico claro),
  \"procedimento\": (passo a passo numerado, detalhado e seguro com 5 a 7 etapas),
  \"itens_sugeridos\": [
    {\"tipo_item\": \"quimico\", \"item_id\": (id do reagente do estoque ou 1), \"quantidade\": \"25 mL\", \"obs\": \"Uso na bancada\"},
    {\"tipo_item\": \"vidraria\", \"item_id\": (id da vidraria do estoque ou 10), \"quantidade\": \"2 un\", \"obs\": \"Limpa e seca\"}
  ]
}";
            $resposta = $this->executarGemini($prompt);
            if (!isset($resposta['erro']) && !empty($resposta)) {
                echo json_encode($resposta, JSON_UNESCAPED_UNICODE);
                return;
            }
        }

        // Fallback Heurístico (offline)
        $q1 = $quimicosDisponiveis[0] ?? ['id' => 1, 'nome' => 'Reagente Padrão'];
        $q2 = $quimicosDisponiveis[1] ?? ['id' => 2, 'nome' => 'Solução Auxiliar'];
        $q3 = $quimicosDisponiveis[2] ?? ['id' => 3, 'nome' => 'Reagente Titulante'];

        $v1 = $vidrariasDisponiveis[0] ?? ['id' => 10, 'nome' => 'Béquer 250 mL'];
        $v2 = $vidrariasDisponiveis[1] ?? ['id' => 15, 'nome' => 'Erlenmeyer 250 mL'];
        $v3 = $vidrariasDisponiveis[2] ?? ['id' => 20, 'nome' => 'Bureta 50 mL'];

        echo json_encode([
            'titulo' => "Ensaio Experimental de " . htmlspecialchars($tema, ENT_QUOTES, 'UTF-8'),
            'materia_curso' => $curso,
            'objetivo' => "Compreender os princípios físico-químicos fundamentais associados a {$tema}, aplicando técnicas analíticas padronizadas e controle rigoroso de variáveis de bancada.",
            'procedimento' => "1. Higienizar e preparar as vidrarias na bancada de trabalho, inspecionando ausência de trincas.\n"
                . "2. Medir com exatidão a alíquota necessária de {$q1['nome']} utilizando pipeta calibrada.\n"
                . "3. Transferir para o {$v1['nome']} sob agitação suave e controle de temperatura ambiente.\n"
                . "4. Adicionar moderadamente o {$q2['nome']} observando alterações térmicas ou de coloração.\n"
                . "5. Adicionar o {$q3['nome']} como titulante até o ponto de viragem, registrando o volume gasto.\n"
                . "6. Aquecer em banho brando ou titular conforme o ponto de equivalência do ensaio.\n"
                . "7. Registrar os volumes e massas obtidos em triplicata e proceder com a neutralização dos resíduos.",
            'itens_sugeridos' => [
                ['tipo_item' => 'quimico', 'item_id' => (int) $q1['id'], 'quantidade' => '20 mL', 'obs' => 'Fase inicial'],
                ['tipo_item' => 'quimico', 'item_id' => (int) $q2['id'], 'quantidade' => '10 mL', 'obs' => 'Fase de reação'],
                ['tipo_item' => 'quimico', 'item_id' => (int) $q3['id'], 'quantidade' => '15 mL', 'obs' => 'Titulação'],
                ['tipo_item' => 'vidraria', 'item_id' => (int) $v1['id'], 'quantidade' => '2 un', 'obs' => 'Reação principal'],
                ['tipo_item' => 'vidraria', 'item_id' => (int) $v2['id'], 'quantidade' => '2 un', 'obs' => 'Coleta e titulação'],
                ['tipo_item' => 'vidraria', 'item_id' => (int) $v3['id'], 'quantidade' => '1 un', 'obs' => 'Titulação']
            ]
        ], JSON_UNESCAPED_UNICODE);
    }

    // ========================================================================
    // 7. MATRIZ PREDITIVA DE INCOMPATIBILIDADE DE ARMAZENAMENTO
    // ========================================================================
    public function verificarIncompatibilidadesArmazenamento()
    {
        header('Content-Type: application/json; charset=utf-8');

        $quimicos = [];
        try {
            $stmt = $this->pdo->query(
                "SELECT id, nome, tipo_armazenamento, periculosidade
                 FROM quimicos
                 ORDER BY tipo_armazenamento ASC, nome ASC"
            );
            $quimicos = $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
        } catch (\PDOException $e) {
            error_log('[LabManager][verificarIncompatibilidades] ' . $e->getMessage());
        }

        $porArmario = [];
        foreach ($quimicos as $q) {
            $arm = trim($q['tipo_armazenamento'] ?? '') ?: 'Geral / Não Informado';
            if (!isset($porArmario[$arm])) {
                $porArmario[$arm] = [];
            }
            $porArmario[$arm][] = $q['nome'];
        }

        $alertas = [];
        foreach ($porArmario as $armario => $itens) {
            $nomesTexto = mb_strtolower(implode(' ', $itens), 'UTF-8');

            $temAcido = (
                strpos($nomesTexto, 'ácido') !== false ||
                strpos($nomesTexto, 'acido') !== false ||
                strpos($nomesTexto, 'cloridrico') !== false ||
                strpos($nomesTexto, 'clorídrico') !== false ||
                strpos($nomesTexto, 'sulfuric') !== false ||
                strpos($nomesTexto, 'sulfúric') !== false ||
                strpos($nomesTexto, 'nitric') !== false ||
                strpos($nomesTexto, 'nítric') !== false
            );
            $temBase = (
                strpos($nomesTexto, 'hidróxido') !== false ||
                strpos($nomesTexto, 'hidroxido') !== false ||
                strpos($nomesTexto, 'soda') !== false ||
                strpos($nomesTexto, 'amônia') !== false ||
                strpos($nomesTexto, 'amonia') !== false
            );

            if ($temAcido && $temBase) {
                $alertas[] = [
                    'local' => $armario,
                    'gravidade' => 'Alta',
                    'motivo' => 'Ácidos corrosivos e bases fortes concentradas estão cadastrados no mesmo armário. Risco de reação exotérmica violenta em caso de vazamento.',
                    'itens' => $itens
                ];
            }

            $temOxidante = (
                strpos($nomesTexto, 'ácido') !== false ||
                strpos($nomesTexto, 'acido') !== false ||
                strpos($nomesTexto, 'dicromato') !== false ||
                strpos($nomesTexto, 'peróxido') !== false ||
                strpos($nomesTexto, 'peroxido') !== false ||
                strpos($nomesTexto, 'permanganato') !== false ||
                strpos($nomesTexto, 'nitrato') !== false
            );
            $temSolvente = (
                strpos($nomesTexto, 'álcool') !== false ||
                strpos($nomesTexto, 'alcool') !== false ||
                strpos($nomesTexto, 'etanol') !== false ||
                strpos($nomesTexto, 'metanol') !== false ||
                strpos($nomesTexto, 'acetona') !== false ||
                strpos($nomesTexto, 'hexano') !== false ||
                strpos($nomesTexto, 'tolueno') !== false
            );

            if ($temOxidante && $temSolvente) {
                $alertas[] = [
                    'local' => $armario,
                    'gravidade' => 'Crítica',
                    'motivo' => 'Agentes oxidantes ou ácidos fortes coarmazenados com solventes inflamáveis. Risco iminente de ignição e incêndio espontâneo.',
                    'itens' => $itens
                ];
            }

            if (
                strpos($nomesTexto, 'cianeto') !== false &&
                (strpos($nomesTexto, 'ácido') !== false || strpos($nomesTexto, 'acido') !== false)
            ) {
                $alertas[] = [
                    'local' => $armario,
                    'gravidade' => 'Crítica',
                    'motivo' => 'Cianetos coarmazenados com ácidos podem liberar gás cianídrico (HCN), altamente tóxico e letal por inalação.',
                    'itens' => $itens
                ];
            }

            if (
                (strpos($nomesTexto, 'sódio metálico') !== false || strpos($nomesTexto, 'sodio metalico') !== false || strpos($nomesTexto, 'potássio') !== false || strpos($nomesTexto, 'potassio') !== false) &&
                (strpos($nomesTexto, 'água') !== false || strpos($nomesTexto, 'agua') !== false || strpos($nomesTexto, 'ácido') !== false || strpos($nomesTexto, 'acido') !== false)
            ) {
                $alertas[] = [
                    'local' => $armario,
                    'gravidade' => 'Crítica',
                    'motivo' => 'Metais alcalinos reagem violentamente com água e ácidos, liberando hidrogênio inflamável. Armazenar em óleo mineral isolado.',
                    'itens' => $itens
                ];
            }
        }

        if (empty($alertas)) {
            $alertas[] = [
                'local' => 'Geral',
                'gravidade' => 'Conforme',
                'motivo' => 'Nenhum cruzamento crítico de incompatibilidade severa detectado nos armários atuais.',
                'itens' => []
            ];
        }

        echo json_encode([
            'total_locais_auditados' => count($porArmario),
            'total_reagentes_analisados' => count($quimicos),
            'alertas_incompatibilidade' => $alertas
        ], JSON_UNESCAPED_UNICODE);
    }

    // ========================================================================
    // 8. CALCULADORA DE DILUIÇÃO E PREPARO DE SOLUÇÕES (C1.V1 = C2.V2)
    // ========================================================================
    public function calcularDiluicaoIa()
    {
        header('Content-Type: application/json; charset=utf-8');
        $input = json_decode(file_get_contents('php://input'), true);

        $tipo = $input['tipo'] ?? 'diluicao';
        $nomeReagente = trim($input['reagente'] ?? 'Solução');

        if ($tipo === 'diluicao') {
            $c1 = (float) ($input['c1'] ?? 0);
            $c2 = (float) ($input['c2'] ?? 0);
            $v2 = (float) ($input['v2'] ?? 0);

            if ($c1 <= 0 || $c2 <= 0 || $v2 <= 0) {
                echo json_encode([
                    'sucesso' => false,
                    'mensagem' => 'Informe C1, C2 e V2 com valores numéricos maiores que zero.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            if ($c2 > $c1) {
                echo json_encode([
                    'sucesso' => false,
                    'mensagem' => 'A concentração inicial (C1) deve ser estritamente maior que a concentração final desejada (C2).'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $v1 = ($c2 * $v2) / $c1;
            $vAgua = $v2 - $v1;

            $procedimento = "1. Pipetar exatamente " . round($v1, 2) . " mL da solução concentrada de {$nomeReagente}.\n"
                . "2. Transferir para um balão volumétrico de " . round($v2, 0) . " mL contendo previamente cerca de 50% de água destilada.\n"
                . "3. Homogeneizar por inversão e completar o volume até a marca do menisco com água destilada.\n"
                . "4. Registrar o fator de diluição (1:" . round($c1 / $c2, 2) . ") no caderno de bancada.";

            echo json_encode([
                'sucesso' => true,
                'tipo_calculo' => 'diluicao',
                'reagente' => $nomeReagente,
                'concentracao_inicial' => $c1,
                'concentracao_final' => $c2,
                'volume_final_ml' => $v2,
                'volume_aliquota_ml' => round($v1, 2),
                'volume_solvente_ml' => round($vAgua, 2),
                'fator_diluicao' => '1:' . round($c1 / $c2, 2),
                'procedimento' => $procedimento
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Modo cálculo de massa para sólido (m = M * MM * V(L))
        $molaridade = (float) ($input['molaridade'] ?? 0.1);
        $massaMolar = (float) ($input['massa_molar'] ?? 40.0);
        $volumeMl = (float) ($input['volume_ml'] ?? 1000);
        $volumeL = $volumeMl / 1000;

        if ($molaridade <= 0 || $massaMolar <= 0 || $volumeMl <= 0) {
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Informe molaridade, massa molar e volume desejado com valores maiores que zero.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $massaGramas = $molaridade * $massaMolar * $volumeL;

        $procedimento = "1. Pesar exatamente " . round($massaGramas, 4) . " g de {$nomeReagente} em béquer seco usando balança analítica calibrada.\n"
            . "2. Dissolver o sólido com pequena porção de solvente destilado (cerca de 30% do volume final).\n"
            . "3. Transferir quantitativamente para o balão volumétrico de " . round($volumeMl, 0) . " mL, lavando o béquer com pequenas porções de solvente.\n"
            . "4. Avolumar até o menisco e homogeneizar por inversão 10 vezes.";

        echo json_encode([
            'sucesso' => true,
            'tipo_calculo' => 'massa',
            'reagente' => $nomeReagente,
            'molaridade' => $molaridade . ' mol/L',
            'massa_molar' => $massaMolar . ' g/mol',
            'volume_final_ml' => $volumeMl,
            'massa_pesar_g' => round($massaGramas, 4),
            'procedimento' => $procedimento
        ], JSON_UNESCAPED_UNICODE);
    }

    // ========================================================================
    // 9. PREVISÃO DE DEMANDA E LISTA DE COMPRAS
    // ========================================================================
    public function previsaoCompras()
    {
        header('Content-Type: application/json; charset=utf-8');

        $comprasSugeridas = [];
        $itensAgendados = [];

        try {
            $stmt = $this->pdo->query(
                "SELECT a.materia, p.titulo, pi.tipo_item, pi.item_id, pi.quantidade_necessaria
                 FROM aulas_agenda a
                 INNER JOIN praticas p ON a.pratica_id = p.id
                 INNER JOIN pratica_itens pi ON p.id = pi.pratica_id
                 WHERE a.data_aula BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)"
            );
            $itensAgendados = $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
        } catch (\PDOException $e) {
            error_log('[LabManager][previsaoCompras] ' . $e->getMessage());
        }

        try {
            $stmtQ = $this->pdo->query(
                "SELECT nome, quantidade, validade
                 FROM quimicos
                 WHERE quantidade <= 1.0
                    OR (validade IS NOT NULL AND validade < CURDATE())
                 ORDER BY validade ASC, quantidade ASC"
            );
            $quimicosCriticos = $stmtQ ? ($stmtQ->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];

            foreach ($quimicosCriticos as $qc) {
                $isVencido = (!empty($qc['validade']) && $qc['validade'] < date('Y-m-d'));
                $comprasSugeridas[] = [
                    'categoria' => 'Reagente Químico',
                    'item' => $qc['nome'],
                    'motivo' => $isVencido
                        ? "Validade expirada em {$qc['validade']}"
                        : "Estoque em nível crítico ({$qc['quantidade']})",
                    'prioridade' => $isVencido ? 'Crítica' : 'Alta'
                ];
            }
        } catch (\PDOException $e) {
            // Mantém lista parcial
        }

        try {
            $stmtV = $this->pdo->query(
                "SELECT nome, capacidade_volume, quantidade_disponivel, quantidade_quebrada
                 FROM equipamentos
                 WHERE tipo = 'vidraria'
                   AND quantidade_disponivel <= 2
                 ORDER BY quantidade_disponivel ASC"
            );
            $vidrariasCriticas = $stmtV ? ($stmtV->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];

            foreach ($vidrariasCriticas as $vc) {
                $comprasSugeridas[] = [
                    'categoria' => 'Vidraria de Bancada',
                    'item' => "{$vc['nome']} ({$vc['capacidade_volume']})",
                    'motivo' => "Estoque restante de apenas {$vc['quantidade_disponivel']} peça(s) - {$vc['quantidade_quebrada']} quebrada(s)",
                    'prioridade' => ((int) $vc['quantidade_disponivel'] === 0) ? 'Crítica' : 'Média'
                ];
            }
        } catch (\PDOException $e) {
            // Mantém lista parcial
        }

        try {
            $stmtE = $this->pdo->query(
                "SELECT nome, codigo_patrimonio, proxima_manutencao
                 FROM equipamentos
                 WHERE tipo = 'equipamento'
                   AND proxima_manutencao IS NOT NULL
                   AND proxima_manutencao < CURDATE()"
            );
            $equipsAtrasados = $stmtE ? ($stmtE->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];

            foreach ($equipsAtrasados as $ea) {
                $comprasSugeridas[] = [
                    'categoria' => 'Serviço de Manutenção',
                    'item' => "{$ea['nome']} (Patrimônio: {$ea['codigo_patrimonio']})",
                    'motivo' => "Preventiva atrasada desde {$ea['proxima_manutencao']}",
                    'prioridade' => 'Alta'
                ];
            }
        } catch (\PDOException $e) {
            // Mantém lista parcial
        }

        $ordem = ['Crítica' => 1, 'Alta' => 2, 'Média' => 3, 'Baixa' => 4];
        usort($comprasSugeridas, function ($a, $b) use ($ordem) {
            $pa = $ordem[$a['prioridade']] ?? 99;
            $pb = $ordem[$b['prioridade']] ?? 99;
            return $pa <=> $pb;
        });

        echo json_encode([
            'data_analise' => date('Y-m-d H:i:s'),
            'total_itens_agendados_30d' => count($itensAgendados),
            'total_sugestoes' => count($comprasSugeridas),
            'sugestoes_compra' => $comprasSugeridas
        ], JSON_UNESCAPED_UNICODE);
    }

    // ========================================================================
    // HELPER PRIVADO - INTEGRAÇÃO COM A API DO GOOGLE GEMINI (RESPOSTA JSON)
    // ========================================================================
    private function executarGemini($prompt)
    {
        if (empty($this->apiKey)) {
            return ['erro' => 'API Key não configurada'];
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/"
            . $this->geminiModel
            . ":generateContent?key=" . $this->apiKey;

        $postData = [
            "contents" => [
                ["parts" => [["text" => $prompt]]]
            ],
            "generationConfig" => [
                "response_mime_type" => "application/json",
                "temperature" => 0.7,
                "maxOutputTokens" => 2048
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            error_log('[LabManager][executarGemini] cURL: ' . $curlErr);
            return ['erro' => 'Falha de conexão com a API externa'];
        }

        if ($httpCode !== 200 || !$response) {
            error_log('[LabManager][executarGemini] HTTP ' . $httpCode . ' - ' . substr((string) $response, 0, 500));
            return ['erro' => 'Falha na resposta da API externa (HTTP ' . $httpCode . ')'];
        }

        $data = json_decode($response, true);
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            $texto = $data['candidates'][0]['content']['parts'][0]['text'];
            $decodificado = json_decode($texto, true);
            return is_array($decodificado) ? $decodificado : ['erro' => 'Resposta da IA não é JSON válido'];
        }

        return ['erro' => 'Estrutura de resposta inesperada da API'];
    }
}