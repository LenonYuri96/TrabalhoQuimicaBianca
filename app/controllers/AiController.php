<?php
class AiController {
    // Insira sua API Key do Google Gemini ou OpenAI aqui se desejar.
    // Se deixar vazio, o sistema roda no Modo Inteligente com dados técnicos reais embutidos!
    private $apiKey = '';

    /**
     * 1. Auditoria Geral do Dashboard
     */
    public function auditoriaGeral() {
        header('Content-Type: application/json; charset=utf-8');
        $input = json_decode(file_get_contents('php://input'), true);

        $vencidos = (int) ($input['quimicos_vencidos'] ?? 0);
        $manutAtrasadas = (int) ($input['manutencoes_atrasadas'] ?? 0);
        $totalOcorrencias = (int) ($input['total_ocorrencias'] ?? 0);

        if (!empty($this->apiKey)) {
            $prompt = "Você é um auditor sênior de conformidade em laboratórios químicos.
Analise os seguintes indicadores:
- Reagentes vencidos: $vencidos
- Manutenções preventivas atrasadas: $manutAtrasadas
- Ocorrências/acidentes registrados: $totalOcorrencias

Responda ESTRITAMENTE em formato JSON com as seguintes chaves:
'score' (um número de 0 a 100 indicando a saúde operacional do lab),
'status_geral' (Excelente, Bom, Alerta ou Crítico),
'diagnostico' (um resumo executivo de 2 frases),
'riscos_principais' (um array com 3 strings de riscos iminentes),
'plano_acao' (um array com 3 ações imediatas prioritárias).";

            $resposta = $this->executarGemini($prompt);
        } else {
            // Heurística de Laboratório
            $score = 100 - ($vencidos * 5) - ($manutAtrasadas * 8) - ($totalOcorrencias * 4);
            $score = max(10, min(100, $score));

            $status = $score >= 80 ? 'Bom' : ($score >= 50 ? 'Alerta' : 'Crítico');

            $resposta = [
                'score' => $score,
                'status_geral' => $status,
                'diagnostico' => "O laboratório opera com $vencidos reagente(s) vencido(s) e $manutAtrasadas equipamento(s) sem manutenção periódica regular, exigindo plano de intervenção preventiva.",
                'riscos_principais' => [
                    "Risco de contaminação e perda de exatidão em ensaios devido a produtos vencidos",
                    "Risco à integridade física por equipamentos com calibração ou exaustão defasada",
                    "Possível reincidência de avarias decorrente da falta de triagem corretiva"
                ],
                'plano_acao' => [
                    "Isolar os produtos com prazo de validade expirado e providenciar descarte homologado",
                    "Paralisar temporariamente equipamentos com manutenção crítica atrasada (ex: capela/balança)",
                    "Realizar DDS (Diálogo Diário de Segurança) revisando o manuseio seguro de vidrarias"
                ]
            ];
        }

        echo json_encode($resposta);
    }

    /**
     * 2. Consulta de FISPQ, EPIs e Descarte de Reagente
     */
    public function consultarQuimico() {
        header('Content-Type: application/json; charset=utf-8');
        $input = json_decode(file_get_contents('php://input'), true);
        $nome = $input['nome'] ?? '';

        if (!empty($this->apiKey)) {
            $prompt = "Você é um especialista em segurança química e meio ambiente.
Forneça as diretrizes de segurança para o reagente: '$nome'.
Responda ESTRITAMENTE em JSON com as chaves:
'periculosidade_resumo' (classe de perigo principal),
'epis_obrigatorios' (array de strings com EPIs recomendados),
'incompatibilidades' (com quais substâncias ele NUNCA deve ser misturado ou armazenado perto),
'protocolo_descarte' (como neutralizar ou destinar este produto conforme normas ambientais).";

            $resposta = $this->executarGemini($prompt);
        } else {
            $nomeLower = strtolower($nome);
            $perigo = 'Químico Geral / Cuidado no Manuseio';
            $incomp = 'Ácidos fortes, bases concentradas e fontes diretas de calor.';
            $descarte = 'Armazenar em bombona de resíduos devidamente identificada para incineração ou coprocessamento terceirizado.';
            $epis = ['Óculos de proteção com vedação', 'Luvas de nitrila', 'Jaleco 100% algodão'];

            if (strpos($nomeLower, 'cromato') !== false) {
                $perigo = 'Tóxico, Carcinogênico e Oxidante Forte (Cromo Hexavalente)';
                $incomp = 'Agentes redutores, materiais combustíveis, solventes orgânicos e ácidos fortes.';
                $descarte = 'Redução de Cr(VI) para Cr(III) com bissulfito de sódio em meio ácido, seguido de precipitação como hidróxido de cromo e envio a aterro industrial classe 1.';
                $epis = ['Máscara com filtro para vapores/aerossóis', 'Luvas de neoprene ou nitrila pesada', 'Óculos panorâmicos'];
            } elseif (strpos($nomeLower, 'metilico') !== false || strpos($nomeLower, 'metanol') !== false) {
                $perigo = 'Líquido Altamente Inflamável e Tóxico por Inalação/Absorção Cutânea';
                $incomp = 'Oxidantes fortes, peróxidos, ácidos concentrados e metais alcalinos.';
                $descarte = 'Segregar na bombona de resíduos orgânicos inflamáveis não halogenados. Não descartar em esgoto sob hipótese alguma.';
                $epis = ['Capela de exaustão de gases ligada', 'Luvas de borracha butílica', 'Máscara com filtro contra vapores orgânicos'];
            } elseif (strpos($nomeLower, 'lupulo') !== false || strpos($nomeLower, 'malte') !== false) {
                $perigo = 'Material Biológico / Inerte (Grau Alimentício)';
                $incomp = 'Substâncias químicas tóxicas e umidade excessiva (risco de proliferação fúngica).';
                $descarte = 'Pode ser destinado à compostagem orgânica ou resíduo sólido comum não reciclável.';
                $epis = ['Luvas higiênicas descartáveis', 'Touca e máscara para contenção de poeira'];
            }

            $resposta = [
                'reagente' => $nome,
                'periculosidade_resumo' => $perigo,
                'incompatibilidades' => $incomp,
                'protocolo_descarte' => $descarte,
                'epis_obrigatorios' => $epis
            ];
        }

        echo json_encode($resposta);
    }

    /**
     * 3. Auto-Preenchimento Inteligente de Reagente no Cadastro
     */
    public function sugerirDadosQuimico() {
        header('Content-Type: application/json; charset=utf-8');
        $input = json_decode(file_get_contents('php://input'), true);
        $nome = strtolower(trim($input['nome'] ?? ''));

        $dados = [
            'estado' => 'Pó',
            'tipo_armazenamento' => 'Armário Geral de Reagentes',
            'periculosidade' => 'Baixa / Irritante Leve'
        ];

        if (strpos($nome, 'alcool') !== false || strpos($nome, 'solucao') !== false || strpos($nome, 'solução') !== false) {
            $dados['estado'] = 'Liquido';
            $dados['tipo_armazenamento'] = 'Armário Corta-Fogo de Solventes';
            $dados['periculosidade'] = 'Inflamável / Tóxico';
        } elseif (strpos($nome, 'lupulo') !== false || strpos($nome, 'malte') !== false) {
            $dados['estado'] = 'Granulo';
            $dados['tipo_armazenamento'] = 'Câmara Fria / Ambiente Seco';
            $dados['periculosidade'] = 'Inerte / Alimentício';
        } elseif (strpos($nome, 'acido') !== false || strpos($nome, 'ácido') !== false) {
            $dados['estado'] = 'Liquido';
            $dados['tipo_armazenamento'] = 'Armário Específico para Ácidos';
            $dados['periculosidade'] = 'Altamente Corrosivo';
        } elseif (strpos($nome, 'cromato') !== false || strpos($nome, 'prata') !== false) {
            $dados['tipo_armazenamento'] = 'Frasco Âmbar em Armário Trancado';
            $dados['periculosidade'] = 'Tóxico / Nocivo ao Meio Ambiente';
        }

        echo json_encode($dados);
    }

    /**
     * 4. Análise de Ocorrência (Manutenção da rotina anterior)
     */
    public function analisarOcorrencia() {
        header('Content-Type: application/json; charset=utf-8');
        $input = json_decode(file_get_contents('php://input'), true);
        $item = $input['item_nome'] ?? 'Item não informado';
        $tipo = $input['categoria'] ?? 'Geral';
        $descricao = $input['descricao'] ?? '';

        $descLower = strtolower($descricao);
        $gravidade = 'Média';
        $causa = 'Desgaste mecânico por fadiga de material ou manuseio com técnica inadequada.';
        $acao = 'Segregar o item envolvido, registrar em controle interno e inspecionar lotes similares.';

        if (strpos($descLower, 'quebr') !== false || strpos($descLower, 'trinc') !== false) {
            $gravidade = 'Baixa a Média';
            $causa = 'Choque mecânico pontual ou choque térmico pós-aquecimento/secagem.';
            $acao = 'Descartar imediatamente em recipiente rígido para perfurocortantes. Repor vidraria calibrada.';
        } elseif (strpos($descLower, 'queimad') !== false || strpos($descLower, 'fogo') !== false || strpos($descLower, 'fumaça') !== false) {
            $gravidade = 'Crítica';
            $causa = 'Sobrecarga de circuito elétrico, enrolamento do estator superaquecido ou trava de rotor.';
            $acao = 'Desconectar da rede elétrica, etiquetar como EQUIPAMENTO INTERDITADO e acionar manutenção técnica.';
        } elseif (strpos($descLower, 'vazament') !== false || strpos($descLower, 'derram') !== false) {
            $gravidade = 'Alta';
            $causa = 'Fissura estrutural no recipiente ou perda de vedação na tampa por ataque de solvente.';
            $acao = 'Evacuar bancada, conter com vermiculita/neutralizante e reembalar o produto em frasco íntegro sob capela.';
        }

        echo json_encode([
            'gravidade' => $gravidade,
            'causa_raiz' => $causa,
            'acao_imediata' => $acao
        ]);
    }

    private function executarGemini($prompt) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $this->apiKey;
        $postData = [
            "contents" => [["parts" => [["text" => $prompt]]]],
            "generationConfig" => ["response_mime_type" => "application/json"]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return json_decode($data['candidates'][0]['content']['parts'][0]['text'], true);
        }
        return ['erro' => 'Falha ao consultar IA'];
    }
}