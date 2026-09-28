<?php
/**
 * Controller de Chamado — concentra as regras de negócio por perfil.
 *
 * Perfis (funcionario.tipo_usuario):
 *   atendente -> abre chamados, vê SOMENTE os que ele abriu, comenta neles
 *                e pode cancelar o próprio chamado enquanto estiver Aberto.
 *   tecnico   -> vê todos, assume chamados sem responsável, e conduz os que são dele
 *                (Em andamento -> Resolvido/Cancelado), registrando andamentos.
 *   admin     -> vê e conduz todos, e é o único que reatribui o responsável.
 *
 * Status (tabela status_chamado): 1 Aberto, 2 Em andamento, 3 Resolvido, 4 Cancelado.
 * Fluxo permitido: 1 -> 2|4 ; 2 -> 3|4 ; 3 -> 2 (reabrir) ; 4 -> (final).
 */
require_once __DIR__ . '/../model/ChamadoModel.php';
require_once __DIR__ . '/../model/HistoricoModel.php';
require_once __DIR__ . '/../model/ApoioModel.php';
require_once __DIR__ . '/../model/FuncionarioModel.php';

class ChamadoController
{
    private const ABERTO        = 1;
    private const EM_ANDAMENTO  = 2;
    private const RESOLVIDO     = 3;
    private const CANCELADO     = 4;

    private const TRANSICOES = [
        self::ABERTO       => [self::EM_ANDAMENTO, self::CANCELADO],
        self::EM_ANDAMENTO => [self::RESOLVIDO, self::CANCELADO],
        self::RESOLVIDO    => [self::EM_ANDAMENTO],
        self::CANCELADO    => [],
    ];

    private PDO $conexao;
    private ChamadoModel $chamados;
    private HistoricoModel $historico;
    private ApoioModel $apoio;
    private FuncionarioModel $funcionarios;
    private array $usuario;

    public function __construct(PDO $conexao, array $usuarioLogado)
    {
        $this->conexao      = $conexao;
        $this->chamados     = new ChamadoModel($conexao);
        $this->historico    = new HistoricoModel($conexao);
        $this->apoio        = new ApoioModel($conexao);
        $this->funcionarios = new FuncionarioModel($conexao);
        $this->usuario      = $usuarioLogado;
    }

    // ---------------------------------------------------------------- permissões

    private function ehAdmin(): bool     { return $this->usuario['tipo_usuario'] === 'admin'; }
    private function ehTecnico(): bool   { return $this->usuario['tipo_usuario'] === 'tecnico'; }
    private function ehAtendente(): bool { return $this->usuario['tipo_usuario'] === 'atendente'; }

    private function podeVer(array $chamado): bool
    {
        if ($this->ehAtendente()) {
            return (int) $chamado['id_funcionario_abertura'] === (int) $this->usuario['id_funcionario'];
        }
        return true;
    }

    private function estaFechado(array $chamado): bool
    {
        return in_array((int) $chamado['id_status'], [self::RESOLVIDO, self::CANCELADO], true);
    }

    /** Diz o que o usuário logado pode fazer neste chamado (a tela usa isso para montar os botões). */
    private function acoesPermitidas(array $chamado): array
    {
        $statusAtual = (int) $chamado['id_status'];
        $meuId       = (int) $this->usuario['id_funcionario'];
        $transicoes  = [];
        $podeComentar = false;

        if ($this->ehAdmin()) {
            $transicoes   = self::TRANSICOES[$statusAtual];
            $podeComentar = !$this->estaFechado($chamado);
        } elseif ($this->ehTecnico()) {
            $responsavel = $chamado['id_funcionario_responsavel'];
            $ehMeu = $responsavel === null || (int) $responsavel === $meuId;
            if ($ehMeu) {
                $transicoes   = self::TRANSICOES[$statusAtual];
                $podeComentar = !$this->estaFechado($chamado);
            }
        } else { // atendente (já filtrado por podeVer)
            if ($statusAtual === self::ABERTO) {
                $transicoes = [self::CANCELADO];
            }
            $podeComentar = !$this->estaFechado($chamado);
        }

        return [
            'transicoes'         => array_values($transicoes),
            'pode_comentar'      => $podeComentar,
            'pode_reatribuir'    => $this->ehAdmin() && !$this->estaFechado($chamado),
        ];
    }

    // ---------------------------------------------------------------- consulta

    public function listar(array $filtros): array
    {
        $f = [
            'id_status'     => $filtros['id_status']     ?? null,
            'id_prioridade' => $filtros['id_prioridade'] ?? null,
            'id_setor'      => $filtros['id_setor']      ?? null,
            'id_categoria'  => $filtros['id_categoria']  ?? null,
            'busca'         => isset($filtros['busca']) ? trim($filtros['busca']) : null,
        ];

        if ($this->ehAtendente()) {
            // Regra de negócio: atendente só enxerga os próprios chamados.
            $f['somente_abertos_por'] = (int) $this->usuario['id_funcionario'];
        } elseif (!empty($filtros['meus'])) {
            $f['responsavel'] = (int) $this->usuario['id_funcionario'];
        }

        return ['sucesso' => true, 'dados' => $this->chamados->listar($f)];
    }

    public function buscar(int $id): array
    {
        $chamado = $this->chamados->buscarPorId($id);

        if ($chamado === null) {
            return ['sucesso' => false, 'erro' => 'Chamado não encontrado.', 'status' => 404];
        }
        if (!$this->podeVer($chamado)) {
            return ['sucesso' => false, 'erro' => 'Você não tem permissão para ver este chamado.', 'status' => 403];
        }

        $chamado['andamentos'] = $this->historico->listarPorChamado($id);
        $chamado['acoes']      = $this->acoesPermitidas($chamado);

        return ['sucesso' => true, 'dados' => $chamado];
    }

    // ---------------------------------------------------------------- cadastro

    public function criar(array $dados): array
    {
        $titulo    = trim((string) ($dados['titulo'] ?? ''));
        $descricao = trim((string) ($dados['descricao'] ?? ''));

        if ($titulo === '' || $descricao === '') {
            return ['sucesso' => false, 'erro' => 'Título e descrição são obrigatórios.', 'status' => 400];
        }
        if (mb_strlen($titulo) > 150) {
            return ['sucesso' => false, 'erro' => 'O título pode ter no máximo 150 caracteres.', 'status' => 400];
        }

        $idSetor      = (int) ($dados['id_setor'] ?? 0);
        $idCategoria  = (int) ($dados['id_categoria'] ?? 0);
        $idPrioridade = (int) ($dados['id_prioridade'] ?? 0);
        $idPaciente   = !empty($dados['id_paciente']) ? (int) $dados['id_paciente'] : null;

        if (!$this->apoio->existe('setor', 'id_setor', $idSetor)) {
            return ['sucesso' => false, 'erro' => 'Setor inválido.', 'status' => 400];
        }
        if (!$this->apoio->existe('categoria_chamado', 'id_categoria', $idCategoria)) {
            return ['sucesso' => false, 'erro' => 'Categoria inválida.', 'status' => 400];
        }
        if (!$this->apoio->existe('prioridade', 'id_prioridade', $idPrioridade)) {
            return ['sucesso' => false, 'erro' => 'Prioridade inválida.', 'status' => 400];
        }
        if ($idPaciente !== null && !$this->apoio->pacienteExiste($idPaciente)) {
            return ['sucesso' => false, 'erro' => 'Paciente inválido.', 'status' => 400];
        }

        $this->conexao->beginTransaction();
        try {
            $id = $this->chamados->criar([
                'titulo'                  => $titulo,
                'descricao'               => $descricao,
                'id_setor'                => $idSetor,
                'id_categoria'            => $idCategoria,
                'id_prioridade'           => $idPrioridade,
                // Sempre o usuário da sessão — nunca um valor vindo do navegador.
                'id_funcionario_abertura' => (int) $this->usuario['id_funcionario'],
                'id_paciente'             => $idPaciente,
            ]);
            $this->historico->criar($id, (int) $this->usuario['id_funcionario'], 'Chamado aberto.');
            $this->conexao->commit();
        } catch (Throwable $e) {
            $this->conexao->rollBack();
            throw $e;
        }

        return ['sucesso' => true, 'dados' => $this->chamados->buscarPorId($id), 'status' => 201];
    }

    // ---------------------------------------------------------------- andamento / status

    /**
     * Muda status e/ou responsável e registra o que aconteceu no histórico.
     * Campos aceitos: id_status, id_funcionario_responsavel (só admin), comentario.
     */
    public function atualizar(int $id, array $dados): array
    {
        $chamado = $this->chamados->buscarPorId($id);
        if ($chamado === null) {
            return ['sucesso' => false, 'erro' => 'Chamado não encontrado.', 'status' => 404];
        }
        if (!$this->podeVer($chamado)) {
            return ['sucesso' => false, 'erro' => 'Você não tem permissão para este chamado.', 'status' => 403];
        }

        $acoes      = $this->acoesPermitidas($chamado);
        $statusAtual = (int) $chamado['id_status'];
        $novoStatus  = !empty($dados['id_status']) ? (int) $dados['id_status'] : $statusAtual;
        $comentario  = trim((string) ($dados['comentario'] ?? ''));

        $responsavelAtual = $chamado['id_funcionario_responsavel'] !== null
            ? (int) $chamado['id_funcionario_responsavel'] : null;
        $novoResponsavel  = $responsavelAtual;

        // Reatribuição (somente admin)
        if (array_key_exists('id_funcionario_responsavel', $dados)) {
            $pedido = $dados['id_funcionario_responsavel'] === '' || $dados['id_funcionario_responsavel'] === null
                ? null : (int) $dados['id_funcionario_responsavel'];

            if ($pedido !== $responsavelAtual) {
                if (!$acoes['pode_reatribuir']) {
                    return ['sucesso' => false, 'erro' => 'Somente o administrador pode alterar o responsável.', 'status' => 403];
                }
                if ($pedido !== null) {
                    $alvo = $this->funcionarios->buscarPorId($pedido);
                    if ($alvo === null || !in_array($alvo['tipo_usuario'], ['tecnico', 'admin'], true)) {
                        return ['sucesso' => false, 'erro' => 'O responsável deve ser um técnico ou administrador.', 'status' => 400];
                    }
                }
                $novoResponsavel = $pedido;
            }
        }

        // Mudança de status
        if ($novoStatus !== $statusAtual) {
            if (!in_array($novoStatus, $acoes['transicoes'], true)) {
                return ['sucesso' => false, 'erro' => 'Você não pode mover este chamado para esse status.', 'status' => 422];
            }
            // Técnico que inicia o atendimento assume o chamado.
            if ($novoStatus === self::EM_ANDAMENTO && $novoResponsavel === null && $this->ehTecnico()) {
                $novoResponsavel = (int) $this->usuario['id_funcionario'];
            }
            if ($novoStatus === self::EM_ANDAMENTO && $novoResponsavel === null) {
                return ['sucesso' => false, 'erro' => 'Defina um responsável antes de colocar o chamado em andamento.', 'status' => 422];
            }
            // Encerrar/cancelar/reabrir exige justificativa registrada.
            $exigeComentario = in_array($novoStatus, [self::RESOLVIDO, self::CANCELADO], true)
                            || $statusAtual === self::RESOLVIDO;
            if ($exigeComentario && $comentario === '') {
                return ['sucesso' => false, 'erro' => 'Informe um comentário para resolver, cancelar ou reabrir o chamado.', 'status' => 422];
            }
        }

        if ($novoStatus === $statusAtual && $novoResponsavel === $responsavelAtual) {
            return ['sucesso' => false, 'erro' => 'Nenhuma alteração para registrar.', 'status' => 400];
        }
        if ($this->estaFechado($chamado) && $novoStatus === $statusAtual) {
            return ['sucesso' => false, 'erro' => 'Chamado encerrado não pode ser alterado.', 'status' => 422];
        }

        $fechando       = in_array($novoStatus, [self::RESOLVIDO, self::CANCELADO], true);
        $dataFechamento = $fechando ? date('Y-m-d H:i:s') : null;

        $this->conexao->beginTransaction();
        try {
            $this->chamados->atualizarAndamento($id, $novoStatus, $novoResponsavel, $dataFechamento);
            $depois = $this->chamados->buscarPorId($id);

            $linhas = [];
            if ($novoStatus !== $statusAtual) {
                $linhas[] = "Status alterado de {$chamado['status']} para {$depois['status']}.";
            }
            if ($novoResponsavel !== $responsavelAtual) {
                $linhas[] = $novoResponsavel === null
                    ? 'Responsável removido.'
                    : "Responsável definido: {$depois['responsavel']}.";
            }
            if ($comentario !== '') {
                $linhas[] = $comentario;
            }
            $this->historico->criar($id, (int) $this->usuario['id_funcionario'], implode("\n", $linhas));
            $this->conexao->commit();
        } catch (Throwable $e) {
            $this->conexao->rollBack();
            throw $e;
        }

        return $this->buscar($id);
    }

    /** Registra apenas um comentário (andamento) no histórico. */
    public function registrarAndamento(int $idChamado, array $dados): array
    {
        $chamado = $this->chamados->buscarPorId($idChamado);
        if ($chamado === null) {
            return ['sucesso' => false, 'erro' => 'Chamado não encontrado.', 'status' => 404];
        }
        if (!$this->podeVer($chamado)) {
            return ['sucesso' => false, 'erro' => 'Você não tem permissão para este chamado.', 'status' => 403];
        }
        if (!$this->acoesPermitidas($chamado)['pode_comentar']) {
            return ['sucesso' => false, 'erro' => 'Não é possível registrar andamento neste chamado.', 'status' => 422];
        }

        $comentario = trim((string) ($dados['comentario'] ?? ''));
        if ($comentario === '') {
            return ['sucesso' => false, 'erro' => 'Escreva o andamento antes de salvar.', 'status' => 400];
        }
        if (mb_strlen($comentario) > 5000) {
            return ['sucesso' => false, 'erro' => 'O andamento pode ter no máximo 5000 caracteres.', 'status' => 400];
        }

        $this->historico->criar($idChamado, (int) $this->usuario['id_funcionario'], $comentario);

        return ['sucesso' => true, 'dados' => $this->historico->listarPorChamado($idChamado), 'status' => 201];
    }
}
