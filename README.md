# Gestão de Manutenção Predial – PredialFix

## 📌 Proposta 3 – Gestão de Manutenção Predial

---

## 🔗 Links do Projeto

📌 Protótipo no Figma:  
https://www.figma.com/design/SQHpUYEEDjRpvFAai5TZEo/Gest%C3%A3o-de-Manuten%C3%A7%C3%A3o-Predial?node-id=0-1&p=f&t=Czhuw3LMMouW7fCQ-0

📌 Organização Ágil no Trello:  
https://trello.com/b/EwVMPQLe/projeto-gestao-de-manutencao-predial

📌 Repositório no GitHub:  
https://github.com/matheus130607/Gestao-de-Manutencao-Predial

---

## 🏢 Situação Problema

O SENAI enfrenta diariamente centenas de solicitações de manutenção predial, variando entre problemas simples (lâmpadas queimadas) até ocorrências complexas (falhas hidráulicas e estruturais).

Atualmente, a ausência de um sistema estruturado gera:

- Falta de transparência no andamento dos chamados  
- Demora no atendimento  
- Dificuldade no controle histórico das manutenções  
- Reclamações recorrentes de alunos e colaboradores  

Diante desse cenário, surge o **PredialFix**, uma plataforma de gestão de chamados prediais, com foco no desenvolvimento de uma API segura, organizada e escalável utilizando Laravel.

---

# 🎯 Objetivo do Projeto

Desenvolver o Back-End do PredialFix utilizando Laravel, responsável por:

- Gerenciar usuários com níveis de acesso distintos  
- Controlar abertura e fluxo de chamados  
- Garantir rastreabilidade e histórico das manutenções  
- Fornecer transparência no acompanhamento do atendimento  
- Priorizar urgências de forma organizada  

---

# 🧩 Funcionalidades Essenciais

## 1️⃣ Gestão de Usuários (Multi-nível)

Sistema de autenticação com níveis de acesso:

- **Usuário**
  - Pode abrir chamados
  - Pode acompanhar status
  - Pode visualizar histórico

- **Responsável/Técnico**
  - Atualiza status do chamado
  - Registra observações técnicas
  - Finaliza atendimentos

Recursos técnicos:

- Autenticação nativa do Laravel (Laravel Breeze ou Sanctum)
- Controle de permissões por perfil
- Criptografia de senha com Hash do Laravel

---

## 2️⃣ Abertura de Chamados

Campos obrigatórios:

- Tipo: Elétrica | Hidráulica | Outros  
- Descrição detalhada  
- Local (Sala, laboratório, área comum)  
- Data de abertura  
- Prioridade (Baixa, Média, Alta, Urgente)

---

## 3️⃣ Workflow de Atendimento

Fluxo padrão de status:

Aberto → Em Análise → Em Execução → Concluído

Regras de negócio:

- Apenas responsáveis podem alterar status
- Registro automático de data e responsável por cada alteração
- Histórico completo de movimentações

---

## 4️⃣ Histórico da Unidade

Consulta por:

- Sala específica  
- Bloco  
- Área comum  

Exibição de:

- Chamados anteriores  
- Tipo de problema  
- Tempo médio de resolução  
- Frequência de ocorrências  

---

## 5️⃣ Notificações de Progresso (Simuladas)

Exemplos:

- Técnico a caminho  
- Serviço em execução  
- Serviço finalizado  
- Chamado concluído com sucesso  

Implementação inicial via registro no banco de dados e retorno via API.

---

# 🛠️ Capacidades Técnicas Aplicadas

## 1. Sequência de Desenvolvimento

1. Levantamento de requisitos  
2. Modelagem do banco de dados (MySQL)  
3. Prototipagem das telas no Figma  
4. Definição da arquitetura no Laravel  
5. Implementação da autenticação  
6. Desenvolvimento dos módulos de chamados  
7. Implementação do workflow  
8. Testes e validações  
9. Documentação técnica em Word  
10. Deploy em servidor  

---

## 2. Diagramas do Sistema

Para garantir a correta estruturação e compreensão do fluxo de dados da API, o projeto utilizará as seguintes modelagens UML:

**Diagrama de Classes:**
Focado na estruturação do banco de dados e da modelagem orientada a objetos (Models do Laravel), definindo:
- **Usuários:** Atributos de autenticação e níveis de acesso (Usuário vs. Responsável).
- **Chamados:** Relação com usuários (solicitante e técnico designado), contendo atributos como tipo, local, prioridade e status atual.
- **Histórico:** Entidade associativa para registrar a linha do tempo das manutenções, garantindo a rastreabilidade do workflow.

**Diagrama de Sequência:**
Focado em mapear a comunicação entre as camadas do sistema durante as principais ações:
- **Abertura de Chamados:** Demonstra o fluxo desde a requisição do Front-End, passando pela rota da API, validação no *FormRequest*, processamento pelo *Controller/Service*, persistência no banco (MySQL) e retorno de sucesso em JSON.
- **Atualização de Status:** Detalha a interação onde o Técnico altera o estado de um chamado (ex: "Em Análise" para "Em Execução"), acionando os gatilhos de registro automático no histórico do sistema.

---

## 3. Arquitetura do Sistema

Arquitetura baseada em:

- Laravel (MVC)
- API RESTful
- Separação em camadas:
  - Controllers
  - Models
  - Services
  - Migrations
  - Requests (validação)

Banco de Dados: MySQL

---

## 4. Recursos Humanos

Equipe mínima:

- Desenvolvedor Back-End (Laravel)  
- Desenvolvedor Front-End  
- Designer (Prototipagem no Figma)  
- Analista de Requisitos  
- Gestor de Projeto  

---

## 5. Metodologia de Desenvolvimento

Metodologia Ágil (Scrum simplificado):

- Organização das tarefas no Trello  
- Divisão por Sprints  
- Reuniões semanais de acompanhamento  
- Controle de backlog  

---

## 6. Cronograma do Projeto

Duração total: até junho

**Fevereiro:**
- Levantamento de requisitos
- Modelagem do banco e criação de Diagramas
- Prototipagem no Figma

**Março:**
- Configuração do ambiente
- Implementação da autenticação
- Estrutura inicial da API

**Abril:**
- Desenvolvimento completo dos chamados
- Implementação do workflow

**Maio:**
- Testes
- Ajustes
- Implementação do histórico e notificações

**Junho:**
- Finalização
- Documentação em Word
- Entrega e apresentação

---

## 7. Softwares Utilizados

- Laravel  
- PHP  
- MySQL  
- Composer  
- Git e GitHub  
- Trello  
- Figma  
- Microsoft Word  
- Ferramenta de Modelagem UML (Astah, Lucidchart ou similar)

---

## 8. Documentação Técnica

A documentação será elaborada em Word contendo:

- Descrição do projeto  
- Requisitos funcionais e não funcionais  
- Diagrama de Classes e de Sequência  
- Diagrama de banco de dados  
- Fluxo do sistema  
- Manual de instalação  
- Manual de uso  

---

# 🤝 Capacidades Socioemocionais Desenvolvidas

Durante o projeto, a equipe deverá demonstrar:

- Autogestão  
- Pensamento analítico  
- Inteligência emocional  
- Autonomia  
- Resiliência emocional  
- Trabalho em equipe  
- Criatividade e inovação  

---

# 🚀 Diferenciais Futuros

- Dashboard administrativo com métricas  
- Relatórios de desempenho  
- Sistema de prioridade automática  
- Aplicativo mobile  
- Integração com QR Code para abertura rápida de chamados  

---

# 📎 Conclusão

O PredialFix é uma solução estratégica para modernizar a gestão de manutenção predial no SENAI, promovendo:

✔ Transparência  
✔ Organização  
✔ Agilidade  
✔ Rastreabilidade  
✔ Melhor experiência para alunos e colaboradores  

O projeto será desenvolvido utilizando Laravel, banco de dados MySQL, metodologia ágil com Trello, prototipagem no Figma e documentação formal em Word, com conclusão prevista até junho.
