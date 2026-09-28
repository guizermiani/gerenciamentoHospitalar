# Sistema de Chamados - Hospital

Sistema web (PHP + MariaDB + HTML/JS) para abertura e acompanhamento de chamados internos do hospital.

## Como rodar (XAMPP)

1. Copie a pasta do projeto para `htdocs/gerenciamentohospitalar`.
2. Inicie **Apache** e **MySQL/MariaDB** no XAMPP.
3. No phpMyAdmin, crie o banco `sistema_chamados_hospital` (utf8mb4_general_ci).
4. Com o banco selecionado, importe nesta ordem:
   - `database/01_estrutura.sql` (tabelas, prioridades e status)
   - `database/02_dados_iniciais.sql` (setores, categorias, usuários de teste e pacientes de exemplo)
5. Confira `config/conexao.php` (usuário `root`, senha vazia por padrão).
6. Acesse `http://localhost/gerenciamentohospitalar/`.

## Usuários de teste

| Perfil        | E-mail                   | Senha         |
|---------------|--------------------------|---------------|
| Administrador | admin@hospital.com       | Admin@123     |
| Técnico       | tecnico@hospital.com     | Tecnico@123   |
| Atendente     | atendente@hospital.com   | Atendente@123 |

Novos usuários são cadastrados pelo administrador em **Funcionários**.

## Regras de negócio por perfil

| Ação                                   | Atendente           | Técnico                              | Admin |
|----------------------------------------|---------------------|--------------------------------------|-------|
| Abrir chamado                          | sim                 | sim                                  | sim   |
| Ver chamados                           | só os que abriu     | todos                                | todos |
| Registrar andamento                    | nos seus (não encerrados) | nos que são dele ou sem responsável | todos (não encerrados) |
| Mudar status                           | cancelar o seu, se Aberto | nos que são dele ou sem responsável | todos |
| Reatribuir responsável                 | não                 | não                                  | sim   |
| Cadastrar funcionários/categorias/setores | não              | não                                  | sim   |

Fluxo de status: Aberto → Em andamento ou Cancelado; Em andamento → Resolvido ou Cancelado; Resolvido → Em andamento (reabrir); Cancelado é final.
Resolver, cancelar e reabrir exigem comentário. Toda mudança gera uma linha no histórico (andamentos) do chamado.
