# 🏛️ Painel Político Nacional — API Backend

> Portal público de transparência cívica que consolida dados de APIs governamentais e os apresenta de forma acessível a qualquer cidadão brasileiro.

---

## 📋 Índice

- [Sobre o Projeto](#sobre-o-projeto)
- [Arquitetura](#arquitetura)
- [Escopo do MVP](#escopo-do-mvp)
- [Origem dos Dados](#origem-dos-dados)
- [Limitações Conhecidas](#limitações-conhecidas)
- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Configuração](#configuração)
- [Rodando o Projeto](#rodando-o-projeto)
- [Endpoints da API](#endpoints-da-api)
- [Autenticação](#autenticação)
- [Coleta de Dados (Jobs)](#coleta-de-dados-jobs)
- [Testes](#testes)
- [Roadmap](#roadmap)
- [Licença](#licença)

---

## Sobre o Projeto

O **Painel Político Nacional** é um portal público, sem fins lucrativos, criado para transformar dados brutos de APIs e portais governamentais em informação legível e acessível para o cidadão brasileiro comum. O projeto nasce da crença de que a transparência pública é o fundamento de uma democracia saudável.

### Princípios

- **100% público**: nenhuma informação requer cadastro para ser acessada
- **Mobile-first**: projetado prioritariamente para uso em smartphones
- **Dados verificáveis**: toda informação exibida tem sua fonte identificada
- **Atualização mensal**: snapshots cobrindo os 48 meses de cada mandato
- **Cobertura progressiva**: cobertura começa pelas capitais e expande-se

### Entidades cobertas (fases)

| Fase | Escopo | Status |
|------|--------|--------|
| **MVP (atual)** | Municípios (prefeituras e câmaras de vereadores) | 🔨 Em desenvolvimento |
| Fase 2 | Assembleias Legislativas e Governos Estaduais | 📅 Planejado |
| Fase 3 | Congresso Nacional (Câmara Federal + Senado) e Governo Federal | 📅 Planejado |

---

## Arquitetura

```
┌─────────────────────────────────────────────────────────┐
│                   APIs Governamentais                    │
│  TSE │ SICONFI │ Portal Transparência │ IBGE │ TCEs     │
└───────────────────────┬─────────────────────────────────┘
                        │ Jobs agendados (mensal)
┌───────────────────────▼─────────────────────────────────┐
│              Backend Laravel 10 (esta API)               │
│  ┌──────────────┐  ┌──────────────┐  ┌───────────────┐  │
│  │ IntegrationJobs│  │  PostgreSQL  │  │  Redis Cache  │  │
│  └──────────────┘  └──────────────┘  └───────────────┘  │
│  ┌──────────────────────────────────────────────────┐   │
│  │              API REST pública (JSON)              │   │
│  └──────────────────────────────────────────────────┘   │
└───────────────────────┬─────────────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────────────┐
│              Frontend ReactJS (Mobile-First)              │
│  Visão Geral │ Prefeitura │ Câmara │ Obras │ Finanças   │
└─────────────────────────────────────────────────────────┘
```

**Stack:**
- Backend: Laravel 10 + PHP 8.2
- Banco: PostgreSQL 15+
- Cache: Redis 7+
- Auth: Laravel Sanctum (tokens Bearer)
- Deploy sugerido: Railway (backend) + Vercel (frontend)

---

## Escopo do MVP

### Módulos implementados

| Módulo | Descrição |
|--------|-----------|
| `municipalities` | Dados gerais do município (IBGE, IDH, orçamento) |
| `executives` | Prefeito e secretários (estrutura do executivo) |
| `legislators` | Vereadores e projetos de lei |
| `public_works` | Obras e contratos em execução |
| `finances` | Receitas, despesas e execução orçamentária |
| `transfers` | Repasses federais e estaduais |
| `transparency_ranking` | Índice de transparência por município |
| `timeline` | Linha do tempo mês a mês do mandato |

### Campos por entidade — Prefeito / Executivo

- Nome completo, foto, partido, coligação eleitoral
- Votos recebidos e percentual (fonte: TSE)
- Biografia e formação educacional
- Redes sociais e contato institucional
- Salário bruto e número de assessores
- Histórico de mandatos anteriores
- Secretarias vinculadas (com secretário atual)

### Campos por entidade — Vereador

- Nome completo, foto, partido, número de votos (fonte: TSE)
- Salário, verba de gabinete, número de assessores
- Presença em sessões (%)
- Projetos: apresentados / aprovados / em votação / rejeitados
- Alinhamento com o executivo (% votações a favor)
- Índice de produtividade legislativa
- Redes sociais e contato institucional

### Campos por entidade — Obras

- Nome, endereço, bairro, categoria
- Valor contratado e valor executado
- Empresa contratada (CNPJ)
- Status (em dia / atrasada / paralisada)
- Fonte do recurso (municipal / estadual / federal)
- Número do contrato e licitação
- Georreferenciamento (lat/lng)

---

## Origem dos Dados

> ⚠️ **Transparência sobre as fontes**: todas as informações exibidas neste portal são coletadas de fontes oficiais do governo brasileiro. Os dados pertencem ao domínio público e estão disponíveis por força da Lei de Acesso à Informação (Lei 12.527/2011).

| Informação | Fonte | URL | Atualização |
|------------|-------|-----|-------------|
| Candidatos eleitos, votos, partido, coligação | TSE — Repositório de Dados Eleitorais | https://dadosabertos.tse.jus.br | Por eleição |
| Finanças municipais (receitas, despesas, LRF) | SICONFI — Tesouro Nacional | https://apidatalake.tesouro.gov.br | Mensal |
| Contratos, convênios e repasses federais | Portal da Transparência Federal | https://portaldatransparencia.gov.br/api-de-dados | Diária |
| Dados demográficos e municipais | IBGE API | https://servicodados.ibge.gov.br/api/v1 | Anual (Censo) |
| Pesquisa MUNIC (estrutura municipal) | IBGE — MUNIC | https://www.ibge.gov.br/estatisticas/sociais/habitacao/10586-pesquisa-de-informacoes-basicas-municipais.html | Bienal |
| Despesas e obras de municípios (SP) | TCESP | https://transparencia.tce.sp.gov.br/api | Mensal |
| Secretários e vereadores (municípios menores) | Portais de Transparência municipais (LAI) | Varia por município | Varia |
| Projetos de lei (capitais) | Câmaras municipais com dados abertos | Varia por município | Varia |

---

## Limitações Conhecidas

> ℹ️ **Por que alguns municípios têm menos informações?**

O Brasil possui **5.570 municípios** com realidades muito distintas em termos de maturidade digital e cumprimento da Lei de Acesso à Informação. Isso impacta diretamente a cobertura deste portal:

### O que sempre estará disponível (cobertura nacional)
- ✅ Dados eleitorais de candidatos eleitos (TSE — 100% dos municípios)
- ✅ Finanças consolidadas — receitas e despesas por função (SICONFI — ~98% dos municípios)
- ✅ Repasses federais (Portal da Transparência — 100%)
- ✅ Dados demográficos básicos (IBGE — 100%)

### O que pode estar incompleto
- ⚠️ **Secretários**: não há API federal para composição de secretariado — depende do portal de transparência de cada prefeitura
- ⚠️ **Projetos de lei**: câmaras municipais menores raramente publicam dados estruturados — cobertura estimada em ~15% dos municípios
- ⚠️ **Obras detalhadas**: contratos de obras com dados completos dependem dos portais municipais ou dos TCEs estaduais (cobertura variável)
- ⚠️ **Vereadores — presença e votos nominais**: idem projetos de lei

### Estratégia de cobertura progressiva
1. **Capitais estaduais** (27 municípios): cobertura máxima — prioridade inicial
2. **Municípios com mais de 100 mil habitantes** (~300 municípios): alta cobertura esperada
3. **Municípios com portais de transparência ativos** (~1.500 municípios): cobertura parcial
4. **Demais municípios**: dados eleitorais e financeiros apenas

---

## Endpoints da API

Base URL: `http://localhost:8000/api/v1`

> Documentação interativa completa disponível na collection do Postman (arquivo `docs/PainelPolitico.postman_collection.json`)

### Municípios
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/municipalities` | Lista municípios (paginado, filtrável por UF) |
| GET | `/municipalities/{ibge_code}` | Dados gerais de um município |
| GET | `/municipalities/{ibge_code}/summary` | Resumo executivo do município |
| GET | `/municipalities/search?q={termo}` | Busca por nome |

### Executivo Municipal
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/municipalities/{ibge_code}/executive` | Prefeito + secretários |
| GET | `/municipalities/{ibge_code}/executive/mayor` | Dados do prefeito |
| GET | `/municipalities/{ibge_code}/executive/secretaries` | Lista de secretários |

### Câmara de Vereadores
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/municipalities/{ibge_code}/legislature` | Todos os vereadores |
| GET | `/municipalities/{ibge_code}/legislature/{legislator_id}` | Perfil de um vereador |
| GET | `/municipalities/{ibge_code}/legislature/{legislator_id}/bills` | Projetos de lei do vereador |
| GET | `/municipalities/{ibge_code}/bills` | Todos os projetos da câmara |

### Obras e Infraestrutura
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/municipalities/{ibge_code}/public-works` | Lista de obras |
| GET | `/municipalities/{ibge_code}/public-works/{work_id}` | Detalhes de uma obra |
| GET | `/municipalities/{ibge_code}/public-works?status=delayed` | Obras atrasadas |

### Finanças
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/municipalities/{ibge_code}/finances` | Visão geral financeira |
| GET | `/municipalities/{ibge_code}/finances/revenues` | Receitas detalhadas |
| GET | `/municipalities/{ibge_code}/finances/expenditures` | Despesas por função |
| GET | `/municipalities/{ibge_code}/finances/fiscal-report` | Relatório RGF/RREO |

### Repasses
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/municipalities/{ibge_code}/transfers` | Repasses recebidos |
| GET | `/municipalities/{ibge_code}/transfers?source=federal` | Filtrar por esfera |

### Linha do Tempo
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/municipalities/{ibge_code}/timeline` | Timeline do mandato completo |
| GET | `/municipalities/{ibge_code}/timeline/{year}/{month}` | Snapshot de um mês |

### Rankings
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/rankings/transparency` | Ranking nacional de transparência |
| GET | `/rankings/transparency?uf=SP` | Ranking por estado |
| GET | `/rankings/budget-execution` | Ranking de execução orçamentária |

### Modo Jornalista (requer Bearer Token)
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/auth/journalist/register` | Solicitar acesso jornalista |
| POST | `/auth/journalist/login` | Login e obter token |
| GET | `/export/{ibge_code}/full` | Exportar tudo (CSV/JSON) |
| GET | `/export/{ibge_code}/legislators` | Exportar vereadores |
| GET | `/export/{ibge_code}/finances` | Exportar finanças |
| GET | `/export/compare?codes=1200401,2611606` | Comparar municípios |

---

## Autenticação

A API é **100% pública** — todos os endpoints de leitura funcionam sem token.

O **Modo Jornalista** adiciona:
- Rate limit ampliado (300 req/min vs 60 req/min)
- Acesso a endpoints de exportação em massa (CSV/JSON)
- Acesso ao comparador de municípios com mais campos
- Sem captcha nos endpoints de busca

```bash
# Obter token
curl -X POST http://localhost:8000/api/v1/auth/journalist/login \
  -H "Content-Type: application/json" \
  -d '{"email": "jornalista@exemplo.com", "password": "senha"}'

# Usar token
curl http://localhost:8000/api/v1/export/3550308/full \
  -H "Authorization: Bearer {seu_token}"
```

---

## Coleta de Dados (Jobs)

Os dados são coletados por Jobs agendados:

| Job | Frequência | Fonte | Descrição |
|-----|------------|-------|-----------|
| `SyncMunicipalitiesJob` | Anual | IBGE | Atualiza lista e dados dos municípios |
| `SyncElectoralDataJob` | Pós-eleição | TSE | Importa eleitos, votos, partidos |
| `SyncFinancesJob` | Mensal | SICONFI | Receitas e despesas do mês |
| `SyncTransfersJob` | Mensal | Portal Transparência | Convênios e repasses |
| `SyncPublicWorksJob` | Mensal | Portais municipais / TCEs | Obras e contratos |
| `SyncLegislativeDataJob` | Semanal | Câmaras com API aberta | Projetos de lei |
| `ComputeTransparencyRankingJob` | Mensal | Interno | Recalcula ranking |
| `GenerateTimelineSnapshotJob` | Mensal | Interno | Consolida snapshot mensal |

```bash
# Disparar manualmente
php artisan data:sync-finances --all
php artisan data:sync-electoral --uf=SP
php artisan data:compute-ranking
```

---

## Testes

```bash
# Todos os testes
php artisan test

# Apenas feature tests da API
php artisan test --testsuite=Feature

# Com cobertura
php artisan test --coverage
```

---

## Roadmap

### MVP v1.0 (Municipal)
- [x] Estrutura base do projeto
- [ ] Migrations e Models
- [ ] Jobs de coleta: TSE, SICONFI, Portal Transparência
- [ ] Controllers e Resources da API
- [ ] Ranking de transparência
- [ ] Linha do tempo do mandato
- [ ] Modo Jornalista (Sanctum)
- [ ] Testes automatizados
- [ ] Deploy no Railway

### v2.0 (Estadual)
- [ ] Entidade: Governo Estadual (Governador + Secretários)
- [ ] Entidade: Assembleia Legislativa (Deputados Estaduais)
- [ ] Integração com APIs das Assembleias

### v3.0 (Federal)
- [ ] Entidade: Governo Federal (Presidente + Ministros)
- [ ] Entidade: Câmara dos Deputados (API dadosabertos.camara.leg.br)
- [ ] Entidade: Senado Federal (API legis.senado.leg.br)

---

## Licença

MIT License — dados são públicos, código é livre.

> **Aviso legal**: Este portal agrega dados de fontes governamentais públicas. As informações são fornecidas "como estão", sem garantia de completude. Para dados oficiais e atualizados, consulte sempre as fontes primárias listadas na seção [Origem dos Dados](#origem-dos-dados).