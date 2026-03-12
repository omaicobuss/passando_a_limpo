# Passo a passo detalhado para desenvolvimento do sistema "Passando a Limpo" com Yii2

Siga esta sequência para construir o sistema descrito. Cada etapa contém orientações práticas, comandos e exemplos para facilitar a implementação.

---

## 1. Criar o projeto utilizando Yii2 basic

```bash
composer create-project yiisoft/yii2-app-basic passando_a_limpo
cd passando_a_limpo
2. Configurar o ambiente
Configure o banco de dados em config/db.php:

php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=passando_a_limpo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
];
Instale o Bootstrap 5:

bash
composer require yiisoft/yii2-bootstrap5
No arquivo config/web.php, adicione o componente bootstrap5 no request se necessário e ajuste o layout para usar Bootstrap (opcional).

3. Criar as migrations para todas as tabelas
Execute o comando para gerar migrations e defina as estruturas:

bash
php yii migrate/create create_user_table              # se precisar estender a tabela user padrão
php yii migrate/create create_election_table
php yii migrate/create create_candidate_table
php yii migrate/create create_proposal_table
php yii migrate/create create_proposal_vote_table
php yii migrate/create create_proposal_comment_table
php yii migrate/create create_proposal_suggestion_table
php yii migrate/create create_proposal_suggestion_vote_table
php yii migrate/create create_proposal_status_update_table
Em cada migration, defina as colunas conforme o modelo de dados sugerido no documento. Exemplo simplificado para election:

php
public function up()
{
    $this->createTable('election', [
        'id' => $this->primaryKey(),
        'title' => $this->string()->notNull(),
        'description' => $this->text(),
        'start_date' => $this->date(),
        'end_date' => $this->date(),
        'created_at' => $this->integer(),
        'updated_at' => $this->integer(),
    ]);
}
Após criar todas, execute:

bash
php yii migrate
4. Criar os models ActiveRecord
Utilize o Gii (acessado via http://localhost/passando_a_limpo/web/index.php?r=gii) ou crie manualmente:

models/User.php (estenda de yii\web\User ou use a classe padrão)

models/Election.php

models/Candidate.php

models/Proposal.php

models/ProposalVote.php

models/ProposalComment.php

models/ProposalSuggestion.php

models/ProposalSuggestionVote.php

models/ProposalStatusUpdate.php

Defina as relações nos models (exemplo em Proposal.php):

php
public function getCandidate()
{
    return $this->hasOne(Candidate::class, ['id' => 'candidate_id']);
}
public function getVotes()
{
    return $this->hasMany(ProposalVote::class, ['proposal_id' => 'id']);
}
5. Implementar autenticação e RBAC básico
Configure o RBAC em config/web.php:

php
'components' => [
    'authManager' => [
        'class' => 'yii\rbac\DbManager',
    ],
],
Execute a migration do RBAC: php yii migrate --migrationPath=@yii/rbac/migrations

Crie um comando/seed para criar os papéis: admin, candidate, citizen e as permissões básicas (ex.: createProposal, voteProposal). Utilize yii\rbac\DbManager para atribuir permissões.

Ajuste o User para implementar a interface yii\web\IdentityInterface (já está no template básico).

6. Criar controllers e views para gerenciamento de eleições
Gere o CRUD de Election com Gii (escolhendo modelo Election). Ajuste as views para usar Bootstrap 5.

Implemente verificação de acesso: apenas admin pode criar/editar/excluir eleições.

7. Criar controllers e views para candidatos
Crie CandidateController com ações:

index (pública)

view (pública, perfil do candidato com lista de propostas)

create, update, delete (apenas admin ou o próprio candidato)

Associe o candidato a um usuário (user_id) e a uma eleição.

Ajuste o cadastro de usuário para permitir que cidadãos se tornem candidatos (ex.: campo role na tabela user ou via RBAC).

8. Criar controllers e views para propostas
Crie ProposalController com ações:

index (pública, com filtros)

view (detalhe da proposta, incluindo votos, comentários, sugestões e status updates)

create (candidato autenticado)

update (candidato autor ou admin)

delete (admin)

Use formulários com Bootstrap e validação via ActiveForm.

9. Implementar sistema de votação em propostas (AJAX)
No ProposalController, adicione a ação actionVote:

Recebe via POST proposal_id e value (+1 ou -1).

Verifica se o usuário já votou na proposta; se sim, atualiza o voto ou impede novo voto.

Salva em proposal_vote e recalcula a pontuação da proposta (pode ser uma coluna score na tabela proposal ou calculada dinamicamente).

No frontend, use JavaScript (Yii2 possui yii.js e helpers para AJAX) para enviar o voto e atualizar o contador sem refresh.

10. Implementar comentários em propostas
Crie ProposalCommentController com ações:

actionCreate (usuário logado) – salva comentário, com parent_id para respostas.

actionDelete (dono ou admin).

Na view proposal/view, exiba os comentários em árvore (pode usar uma função recursiva ou widget). Use Bootstrap para estilizar.

11. Implementar sugestões de alteração
Crie ProposalSuggestionController com ações:

actionCreate (usuário logado) – sugere melhoria para uma proposta.

actionView (pública) – detalhe da sugestão, com votação.

actionVote (AJAX) – similar à votação de propostas.

actionModerate (candidato da proposta ou admin) – aprova/rejeita a sugestão.

Sugestões podem ter status: pending, approved, rejected. Ao aprovar, o texto da proposta pode ser atualizado (opcional).

12. Implementar acompanhamento pós-eleição (status updates)
Adicione em ProposalController a ação actionStatusUpdate ou crie ProposalStatusUpdateController.

Candidato pode adicionar uma atualização com:

status (not_started, in_progress, completed, cancelled)

descrição e data.

Exiba esses updates na view da proposta, em ordem cronológica.

13. Criar painel do candidato
Crie uma área restrita (/candidate-panel) com um dashboard.

Exiba estatísticas: total de propostas, média de votos, comentários recentes, sugestões pendentes.

Links rápidos para gerenciar propostas e atualizações.

14. Refinar layout com Bootstrap 5
Substitua o layout padrão (views/layouts/main.php) para usar os recursos do yii\bootstrap5.

Utilize componentes como NavBar, Card, Modal para melhorar a interface.

Garanta que todas as views estejam responsivas e com boa aparência.

15. Implementar busca e filtros avançados
Na actionIndex de Proposal, adicione filtros por:

Eleição (drop-down)

Candidato (drop-down)

Tema (campo ou tag)

Status de cumprimento

Ordenação por popularidade (votos), data, etc.

Use yii\data\ActiveDataProvider com filterModel para facilitar.

16. Testes e ajustes finais
Teste todos os fluxos: cadastro de eleição, candidato, proposta, votação, comentários, sugestões e atualizações.

Verifique permissões: usuários comuns não podem editar propostas alheias, candidatos só veem seu painel, etc.

Ajuste pequenos detalhes de UX/UI, mensagens de feedback e validações.

---

## 17. Checklist executável de smoke test web (homologação)

Use este checklist para validar rapidamente os fluxos principais após deploy/ajustes.

### 17.1 Preparação (obrigatório antes do teste)

- [ ] Subir aplicação e banco sem erros.
- [ ] Executar migrations:

```bash
php yii migrate --interactive=0
```

- [ ] Recriar RBAC com regras customizadas:

```bash
php yii rbac/init
```

- [ ] Configurar SMTP real no ambiente:
    - `MAILER_USE_FILE_TRANSPORT=0`
    - `SMTP_HOST`
    - `SMTP_PORT`
    - `SMTP_USERNAME=financeiro@helum.com.br`
    - `SMTP_PASSWORD`
    - `SMTP_ENCRYPTION=tls`
    - `SMTP_AUTH_MODE=login`

### 17.2 Execução dos cenários (PASS/FAIL)

Preencha para cada item:
- Resultado: `PASS` ou `FAIL`
- Evidência: URL, print, ID do registro, mensagem apresentada

| ID | Cenário | Passos rápidos | Critério de aprovação | Resultado | Evidência |
|---|---|---|---|---|---|
| ST-01 | Cadastro de usuário citizen | Abrir cadastro, criar usuário novo | Conta criada com sucesso; papel inicial citizen |  |  |
| ST-02 | Login | Entrar com usuário recém-criado | Login concluído e sessão ativa |  |  |
| ST-03 | Solicitação de candidatura | Em Minha Conta, enviar documento + mensagem | Solicitação criada com status pending |  |  |
| ST-04 | Acesso admin às solicitações | Login admin, abrir listagem de solicitações | Lista carregada; somente admin acessa |  |  |
| ST-05 | Aprovação da solicitação | Admin aprova solicitação pendente | Status aprovado; usuário vira candidate |  |  |
| ST-06 | E-mail de decisão | Após aprovação/reprovação | E-mail enviado para usuário solicitante |  |  |
| ST-07 | Criação de candidato (candidate) | Login como candidate e criar/editar próprio perfil | Operação permitida para próprio recurso |  |  |
| ST-08 | Bloqueio de edição de candidato alheio | Candidate tenta editar outro candidato | Acesso negado (RBAC ownership) |  |  |
| ST-09 | Criação de proposta | Candidate cria proposta vinculada ao próprio candidato | Proposta salva e visível na listagem |  |  |
| ST-10 | Bloqueio de edição de proposta alheia | Candidate tenta editar proposta de outro candidato | Acesso negado (RBAC ownership) |  |  |
| ST-11 | Edição da própria proposta com registro público | Dono da proposta edita título/conteúdo/status e abre a visualização | Edição permitida; proposta salva; última edição registrada exibida para todos na versão atual |  |  |
| ST-12 | Consulta admin ao histórico de versões | Admin abre proposta já editada e expande o histórico | Versões anteriores visíveis apenas ao admin, com conteúdo e metadados de edição |  |  |
| ST-13 | Votação em proposta (AJAX) | Usuário logado vota +1 e -1 | Score atualiza sem refresh e persiste |  |  |
| ST-14 | Comentário em proposta | Usuário logado comenta e responde comentário | Comentário e resposta exibidos em árvore |  |  |
| ST-15 | Exclusão de comentário próprio | Autor exclui seu comentário | Exclusão permitida |  |  |
| ST-16 | Bloqueio de exclusão de comentário alheio | Usuário tenta excluir comentário de terceiro | Acesso negado (RBAC ownership) |  |  |
| ST-17 | Sugestão de alteração | Usuário cria sugestão em proposta | Sugestão criada com status pending |  |  |
| ST-18 | Moderação de sugestão (dono/admin) | Candidato dono da proposta ou admin aprova/reprova | Moderação permitida e status atualizado |  |  |
| ST-19 | Bloqueio de moderação indevida | Candidate não-dono tenta moderar sugestão | Acesso negado (RBAC ownership) |  |  |
| ST-20 | Status update pós-eleição | Dono da proposta adiciona update | Atualização registrada e exibida na timeline |  |  |
| ST-21 | Bloqueio de status update alheio | Candidate tenta atualizar proposta de outro | Acesso negado (RBAC ownership) |  |  |
| ST-22 | Filtro de eleições por título | Aplicar filtro de título em eleições | Lista retorna somente itens compatíveis |  |  |
| ST-23 | Filtro de candidatos por nome/eleição | Aplicar filtros na listagem de candidatos | Lista filtrada corretamente |  |  |
| ST-24 | Download de comprovante da solicitação (owner/admin) | Dono e admin testam download | Permitido para dono/admin e negado para terceiros |  |  |

### 17.3 Critério de aceite final

- [ ] Todos os cenários ST-01 a ST-24 com `PASS`.
- [ ] Nenhum erro 500 durante a execução.
- [ ] Nenhuma falha de autorização em ações permitidas.
- [ ] Nenhuma ação sensível permitida para usuário sem permissão.

Se qualquer cenário falhar, registrar o ID do cenário, mensagem de erro, usuário/perfil usado e reproduzir em ambiente local para correção.