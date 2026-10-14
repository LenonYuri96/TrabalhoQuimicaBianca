# 🧪 LabManager — Sistema Inteligente de Gestão Laboratorial

> Sistema completo para controle de estoque de reagentes químicos, cronograma de manutenção preventiva de equipamentos, livro de registros de ocorrências e auditoria com Inteligência Artificial.

Desenvolvido com arquitetura **MVC (Model-View-Controller)** em **PHP**, banco de dados **MySQL/MariaDB**, interface moderna e responsiva com **Bootstrap 5** e motor de análise assistido por **IA**.

---

## 📌 Sumário
- [Sobre o Projeto](#-sobre-o-projeto)
- [Funcionalidades Principais](#-funcionalidades-principais)
- [Módulos com Inteligência Artificial](#-módulos-com-inteligência-artificial)
- [Tecnologias Utilizadas](#-tecnologias-utilizadas)
- [Estrutura do Projeto (MVC)](#-estrutura-do-projeto-mvc)
- [Instalação e Configuração](#-instalação-e-configuração)
- [Testando com Dados Iniciais](#-testando-com-dados-iniciais)
- [Configuração da Chave de IA (Opcional)](#-configuração-da-chave-de-ia-opcional)

---

## 📖 Sobre o Projeto

O **LabManager** foi idealizado para resolver desafios diários na rotina de laboratórios analíticos e de ensino:
1. **Controle de Validade de Reagentes:** Evitar o uso de insumos vencidos que comprometem ensaios e garantir conformidade com normas ambientais e de segurança.
2. **Manutenção Preventiva de Equipamentos:** Evitar paradas não programadas em capelas de exaustão, balanças analíticas, destiladores e agitadores.
3. **Rastreabilidade de Ocorrências:** Formalizar quebras de vidrarias, falhas mecânicas e descarte acidental com avaliação de causa-raiz.
4. **Assistência por IA:** Diagnósticos rápidos de FISPQ, incompatibilidade química e pareceres periciais automatizados.

---

## 🚀 Funcionalidades Principais

### 1. 📊 Dashboard Analítico
- Indicadores em tempo real (KPIs): Total de Reagentes, Reagentes Vencidos, Equipamentos e Ocorrências.
- Alerta visual imediato de preventivas atrasadas.
- Lista resumida com os registros mais recentes.

### 2. 🧪 Estoque de Químicos e Reagentes
- Controle de quantidade, lote, tipo de armazenamento, periculosidade e estado físico (Pó, Líquido, Grânulo).
- **Cálculo automático de validade:**
  - 🟢 **VÁLIDO:** Data posterior à atual.
  - 🔴 **VENCIDO:** Linha destacada em vermelho suave.
  - ⚪ **NÃO INFORMADO:** Para materiais inertes/alimentícios (ex: Lúpulos e Maltes).
- Filtro de busca textual em tempo real no cabeçalho da tabela.

### 3. ⚙️ Manutenção Preventiva de Equipamentos
- Controle de periodicidade (mensal, semestral, anual ou inspeção manual).
- **Cálculo automatizado no cadastro:** ao definir a última revisão e a frequência em meses, o sistema calcula a data da próxima manutenção preventiva via JavaScript.
- Identificação de revisões atrasadas com badge de urgência.

### 4. 📋 Livro de Registro de Ocorrências
- Registro padronizado com: Relator, Local, Data/Hora, Categoria (Vidraria, Equipamento, Produto), Item envolvido e Descrição detalhada do fato.
- Layout padronizado com formulários higienizados e botões ergonômicos.

---

## 🤖 Módulos com Inteligência Artificial

O sistema conta com 4 integrações com IA (pronto para **Google Gemini** ou **OpenAI**, com motor heurístico de contingência offline):

| Recurso de IA | Onde Fica | O que Faz |
| :--- | :--- | :--- |
| **Auditoria Geral do Lab** | Dashboard | Avalia a taxa de reagentes vencidos e manutenções atrasadas, calcula um *Score de Conformidade (0-100)* e gera os 3 principais riscos e plano de ação semanal. |
| **Guia FISPQ & Descarte** | Tabela de Químicos | Fornece protocolo de descarte ecológico/neutralização, incompatibilidades perigosas (ex: oxidantes x solventes) e EPIs recomendados. |
| **Auto-Preenchimento IA** | Cadastro de Reagente | Ao digitar o nome da substância (ex: *Álcool Metílico*), preenche automaticamente estado físico, tipo de armário e periculosidade. |
| **Parecer Pericial de Ocorrência** | Nova Ocorrência | Analisa o texto do acidente/quebra e determina a gravidade (Baixa, Média, Crítica), provável causa-raiz e ação corretiva imediata. |

---

## 🛠️ Tecnologias Utilizadas

- **Back-end:** PHP 7.4+ (Sem dependências externas, PDO para acesso a dados).
- **Arquitetura:** Padrão MVC (Model-View-Controller) com Front-Controller.
- **Banco de Dados:** MySQL / MariaDB (com integridade referencial e charset `utf8mb4`).
- **Front-end:** HTML5, CSS3 moderno com variáveis customizadas, JavaScript nativo (Fetch API) e **Bootstrap 5.3**.
- **Ícones:** Bootstrap Icons.

---

## 📂 Estrutura do Projeto (MVC)

```text
lab_system/
├── app/
│   ├── config/
│   │   └── Database.php                 # Conexão PDO segura (Prepared Statements)
│   ├── controllers/
│   │   ├── DashboardController.php      # Métricas e indicadores gerais
│   │   ├── QuimicosController.php       # Gestão de estoque de reagentes
│   │   ├── EquipamentosController.php   # Cronograma de manutenções preventivas
│   │   ├── OcorrenciasController.php    # Livro de ocorrências
│   │   └── AiController.php             # Endpoints da IA e análises técnicas
│   ├── models/
│   │   ├── Quimico.php                  # Regras de negócio e status de validade
│   │   ├── Equipamento.php              # Prazos e revisões críticas
│   │   └── Ocorrencia.php               # Rastreamento de acidentes e perdas
│   └── views/
│       ├── layouts/
│       │   ├── header.php               # Navbar e cabeçalho global
│       │   └── footer.php               # Rodapé e carregamento de scripts
│       ├── dashboard/index.php          # Painel principal com auditoria IA
│       ├── quimicos/                    # Listagem com modal FISPQ e cadastro com IA
│       ├── equipamentos/                # Listagem e agendamento de manutenção
│       └── ocorrencias/                 # Livro e registro com análise técnica IA
├── public/
│   ├── css/style.css                    # Estilização visual e responsividade
│   ├── js/main.js                       # Cálculos dinâmicos, filtros e chamadas AJAX
│   └── index.php                        # Roteador central (Front Controller)
├── database.sql                         # Estrutura do banco de dados
└── README.md                            # Documentação do projeto
```

---

## 💻 Instalação e Configuração

### 1. Pré-requisitos
- Ter instalado um ambiente de servidor local como **XAMPP**, **WampServer** ou **Docker**.
- PHP 7.4 ou superior com extensão `pdo_mysql` e `curl` ativas.

### 2. Configurar o Projeto
1. Clone ou descompacte o repositório na pasta raiz do seu servidor web (ex: `C:/xampp/htdocs/lab_system`).
2. Abra o painel do MySQL (phpMyAdmin) e crie o banco importando o arquivo `database.sql`.

### 3. Configurar Conexão com o Banco
Se necessário, edite as credenciais no arquivo `app/config/Database.php`:
```php
private static $host = 'localhost';
private static $db_name = 'lab_control';
private static $username = 'root'; // seu usuário
private static $password = '';     // sua senha
```

### 4. Acessar o Sistema
Abra o navegador e acerte a URL passando pelo ponto de entrada `public/index.php`:
```text
http://localhost/lab_system/public/index.php
```

---

## 🧪 Testando com Dados Iniciais

Para popular o banco e testar imediatamente os gráficos, status vencidos e manutenções em atraso, execute a query abaixo no seu phpMyAdmin:

```sql
USE lab_control;

-- Químicos (Válidos, Vencidos e Inertes)
INSERT INTO quimicos (nome, quantidade, validade, tipo_armazenamento, periculosidade, estado) VALUES
('Alizarina', 1.00, '2031-02-17', 'Armário Geral A', 'Baixa', 'Pó'),
('Violeta Cristal', 2.00, '2027-01-26', 'Armário de Corantes', 'Irritante', 'Pó'),
('Alcool Metilico', 1.00, '2030-10-22', 'Armário Corta-Fogo', 'Inflamável / Tóxico', 'Liquido'),
('Azul Metileno', 1.00, '2023-05-01', 'Armário Geral B', 'Tóxico', 'Pó'),
('Cromato de potassio solução 5%', 2.00, '2022-08-10', 'Armário de Reagentes', 'Tóxico / Oxidante', 'Liquido'),
('Lupulo SAAZ', 1.00, NULL, 'Câmara Fria 01', 'Inerte', 'Granulo');

-- Equipamentos (Em dia e Atrasados)
INSERT INTO equipamentos (nome, codigo_patrimonio, frequencia_meses, ultima_manutencao, proxima_manutencao, observacao) VALUES
('Capela de exaustão de gases', 'CAP-01', 6, '2026-06-01', '2026-12-01', 'Troca dos filtros de carvão'),
('Chuveiro de emergência', 'CHUV-01', 1, '2026-10-01', '2026-11-01', 'Teste de vazão semanal'),
('Destilador de água', 'DEST-02', 6, '2025-08-10', '2026-02-10', 'Desincrustação das resistências atrasada');

-- Ocorrências
INSERT INTO ocorrencias (relator_nome, local_ocorrido, data_hora, categoria, item_nome, descricao, status) VALUES
('Ellen Carneiro', 'Bancada 03', '2026-10-12 14:35:00', 'Vidraria', 'Balão Volumétrico 250mL', 'O balão escorregou durante a secagem e trincou o gargalo.', 'Resolvido'),
('Lucas Silveira', 'Bancada de Ensaios', '2026-10-13 09:15:00', 'Equipamento', 'Agitador de tubos (Vórtex)', 'Apresentou cheiro de queimado e parou de vibrar.', 'Pendente');
```

---

## 🔑 Configuração da Chave de IA (Opcional)

O sistema conta com um **modo de contingência inteligente embutido**: ele reconhece termos como *quebras*, *choques térmicos*, *motores queimados*, *ácidos*, *solventes* e compostos como *Cromato* ou *Metanol*, gerando respostas técnicas estruturadas mesmo **sem internet ou sem chave de API**.

Caso deseje ativar o modelo generativo em tempo real via **Google Gemini**:
1. Obtenha uma chave gratuita no [Google AI Studio](https://aistudio.google.com/).
2. Abra o arquivo `app/controllers/AiController.php`.
3. Preencha a variável `$apiKey`:
   ```php
   private $apiKey = 'SUA_CHAVE_AQUI';
   ```

---

## 👥 Autoria e Licença

Desenvolvido para fins de gestão, auditoria e controle de laboratórios químicos e analíticos.

Distribuído sob a licença **MIT**. Sinta-se livre para utilizar, expandir e aprimorar!
