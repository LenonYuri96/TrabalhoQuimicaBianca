document.addEventListener("DOMContentLoaded", () => {
  // 1. Filtro de Busca em Tempo Real nas Tabelas
  const inputFiltro = document.getElementById("filtroTabela");
  if (inputFiltro) {
    inputFiltro.addEventListener("keyup", function () {
      const termo = this.value.toLowerCase();
      const linhas = document.querySelectorAll("tbody tr");
      linhas.forEach((linha) => {
        const textoLinha = linha.textContent.toLowerCase();
        linha.style.display = textoLinha.includes(termo) ? "" : "none";
      });
    });
  }

  // 2. Cálculo Automático da Próxima Manutenção Preventiva
  const inputUltima = document.getElementById("inputUltimaManutencao");
  const selectFreq = document.getElementById("selectFrequencia");
  const inputProxima = document.getElementById("inputProximaManutencao");

  function atualizarProximaManutencao() {
    if (!inputUltima || !selectFreq || !inputProxima) return;
    const dataBase = inputUltima.value;
    const meses = parseInt(selectFreq.value, 10);
    if (dataBase && !isNaN(meses) && meses > 0) {
      const data = new Date(dataBase);
      data.setMonth(data.getMonth() + meses);
      const yyyy = data.getFullYear();
      const mm = String(data.getMonth() + 1).padStart(2, "0");
      const dd = String(data.getDate()).padStart(2, "0");
      inputProxima.value = `${yyyy}-${mm}-${dd}`;
    }
  }

  if (inputUltima && selectFreq) {
    inputUltima.addEventListener("change", atualizarProximaManutencao);
    selectFreq.addEventListener("change", atualizarProximaManutencao);
  }

  // 3. IA: Parecer Técnico na Tela de Ocorrências
  const btnIaOcorrencia = document.getElementById("btnAnalisarIa");
  if (btnIaOcorrencia) {
    btnIaOcorrencia.addEventListener("click", async () => {
      const item = document.getElementById("itemNomeOcorrencia").value.trim();
      const categoria = document.getElementById("categoriaOcorrencia").value;
      const descricao = document
        .getElementById("descricaoOcorrencia")
        .value.trim();

      if (!descricao) {
        alert(
          "Preencha a descrição do fato antes de solicitar o parecer da IA.",
        );
        return;
      }

      const htmlOriginal = btnIaOcorrencia.innerHTML;
      btnIaOcorrencia.disabled = true;
      btnIaOcorrencia.innerHTML =
        '<span class="spinner-border spinner-border-sm me-1"></span> Analisando...';

      try {
        const res = await fetch(
          "index.php?route=ai&action=analisarOcorrencia",
          {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
              item_nome: item,
              categoria: categoria,
              descricao: descricao,
            }),
          },
        );
        const data = await res.json();

        document.getElementById("iaBadgeGravidade").textContent =
          `Gravidade: ${data.gravidade}`;
        document.getElementById("iaCausa").textContent = data.causa_raiz;
        document.getElementById("iaAcao").textContent = data.acao_imediata;

        const painel = document.getElementById("painelIaResultado");
        painel.classList.remove("d-none");
        painel.scrollIntoView({ behavior: "smooth", block: "nearest" });
      } catch (err) {
        alert("Erro ao consultar a IA.");
      } finally {
        btnIaOcorrencia.disabled = false;
        btnIaOcorrencia.innerHTML = htmlOriginal;
      }
    });
  }

  // 4. IA: Auditoria Executiva do Dashboard
  const btnAuditoria = document.getElementById("btnAuditoriaIa");
  if (btnAuditoria) {
    btnAuditoria.addEventListener("click", async () => {
      const totalQuimicos =
        document.getElementById("statTotalQuimicos")?.textContent || 0;
      const vencidos =
        document.getElementById("statQuimicosVencidos")?.textContent || 0;
      const atrasadas =
        document.getElementById("statManutAtrasadas")?.textContent || 0;
      const ocorrencias =
        document.getElementById("statTotalOcorrencias")?.textContent || 0;

      const htmlOriginal = btnAuditoria.innerHTML;
      btnAuditoria.disabled = true;
      btnAuditoria.innerHTML =
        '<span class="spinner-border spinner-border-sm me-1"></span> Auditando...';

      try {
        const res = await fetch("index.php?route=ai&action=auditoriaGeral", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            quimicos_vencidos: vencidos,
            manutencoes_atrasadas: atrasadas,
            total_ocorrencias: ocorrencias,
          }),
        });

        const data = await res.json();

        // Monta o score e status
        const badge = document.getElementById("badgeScoreIa");
        badge.textContent = `Score: ${data.score}/100 (${data.status_geral})`;
        badge.className = `badge rounded-pill px-3 py-2 fs-6 ${data.score >= 80 ? "bg-success" : data.score >= 50 ? "bg-warning text-dark" : "bg-danger"}`;

        document.getElementById("iaDiagnosticoTexto").textContent =
          data.diagnostico;

        const listaRiscos = document.getElementById("iaListaRiscos");
        listaRiscos.innerHTML = data.riscos_principais
          .map(
            (r) =>
              `<li class="list-group-item px-0 border-0 text-muted"><i class="bi bi-x-circle text-danger me-2"></i>${r}</li>`,
          )
          .join("");

        const listaAcoes = document.getElementById("iaListaAcoes");
        listaAcoes.innerHTML = data.plano_acao
          .map(
            (a) =>
              `<li class="list-group-item px-0 border-0 text-muted"><i class="bi bi-arrow-right-circle text-success me-2"></i>${a}</li>`,
          )
          .join("");

        const box = document.getElementById("boxAuditoriaIa");
        box.classList.remove("d-none");
        box.scrollIntoView({ behavior: "smooth", block: "nearest" });
      } catch (e) {
        alert("Erro ao gerar auditoria.");
      } finally {
        btnAuditoria.disabled = false;
        btnAuditoria.innerHTML = htmlOriginal;
      }
    });
  }

  // 5. IA: Consulta de Descarte / FISPQ na Tabela de Químicos
  const modalQuimico = document.getElementById("modalQuimicoIa")
    ? new bootstrap.Modal(document.getElementById("modalQuimicoIa"))
    : null;
  document.querySelectorAll(".btn-consultar-quimico-ia").forEach((btn) => {
    btn.addEventListener("click", async function () {
      const nome = this.getAttribute("data-nome");
      document.getElementById("modalQuimicoNome").textContent =
        `Guia IA: ${nome}`;
      document.getElementById("iaPerigoTexto").textContent =
        "Analisando banco de dados de segurança...";
      document.getElementById("iaIncompTexto").textContent = "Carregando...";
      document.getElementById("iaDescarteTexto").textContent = "Carregando...";
      document.getElementById("iaEpisBadges").innerHTML = "";

      modalQuimico.show();

      try {
        const res = await fetch("index.php?route=ai&action=consultarQuimico", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ nome: nome }),
        });
        const data = await res.json();

        document.getElementById("iaPerigoTexto").textContent =
          data.periculosidade_resumo;
        document.getElementById("iaIncompTexto").textContent =
          data.incompatibilidades;
        document.getElementById("iaDescarteTexto").textContent =
          data.protocolo_descarte;
        document.getElementById("iaEpisBadges").innerHTML =
          data.epis_obrigatorios
            .map(
              (epi) =>
                `<span class="badge bg-secondary-subtle text-dark border px-3 py-2 rounded-pill"><i class="bi bi-shield-check text-primary me-1"></i>${epi}</span>`,
            )
            .join("");
      } catch (e) {
        document.getElementById("iaPerigoTexto").textContent =
          "Erro ao carregar dados de segurança.";
      }
    });
  });

  // 6. IA: Auto-Preenchimento Inteligente no Cadastro de Químico
  const btnAutoPreencher = document.getElementById("btnAutoPreencherIa");
  if (btnAutoPreencher) {
    btnAutoPreencher.addEventListener("click", async () => {
      const nome = document.getElementById("inputNomeQuimico").value.trim();
      if (!nome) {
        alert(
          "Digite o nome do produto antes de preencher com IA (ex: Álcool Metílico, Cromato, etc)",
        );
        return;
      }

      const htmlOriginal = btnAutoPreencher.innerHTML;
      btnAutoPreencher.disabled = true;
      btnAutoPreencher.innerHTML =
        '<span class="spinner-border spinner-border-sm me-1"></span> Consultando...';

      try {
        const res = await fetch(
          "index.php?route=ai&action=sugerirDadosQuimico",
          {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ nome: nome }),
          },
        );
        const data = await res.json();

        document.getElementById("selectEstado").value = data.estado;
        document.getElementById("inputArmazenamento").value =
          data.tipo_armazenamento;
        document.getElementById("inputPericulosidade").value =
          data.periculosidade;
      } catch (e) {
        alert("Falha ao auto-preencher dados.");
      } finally {
        btnAutoPreencher.disabled = false;
        btnAutoPreencher.innerHTML = htmlOriginal;
      }
    });
  }
});
