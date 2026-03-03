<?php

/** @var app\models\CandidateUpgradeRequest $request */
/** @var app\models\User|null $user */
/** @var bool $approved */

$status = $approved ? 'aprovada' : 'reprovada';
$reviewDate = $request->reviewed_at ? date('d/m/Y H:i', (int) $request->reviewed_at) : '-';
?>
<p>Ola <?= htmlspecialchars((string) ($user->username ?? 'usuario'), ENT_QUOTES, 'UTF-8') ?>,</p>

<p>Sua solicitacao para mudanca de perfil para <strong>candidate</strong> foi <strong><?= $status ?></strong>.</p>

<p><strong>Data da analise:</strong> <?= htmlspecialchars($reviewDate, ENT_QUOTES, 'UTF-8') ?></p>

<?php if (!empty($request->admin_notes)): ?>
    <p><strong>Observacao do administrador:</strong><br>
    <?= nl2br(htmlspecialchars((string) $request->admin_notes, ENT_QUOTES, 'UTF-8')) ?></p>
<?php endif; ?>

<p>Se precisar, acesse sua area "Minha Conta" para acompanhar os detalhes.</p>

<p>Atenciosamente,<br>Equipe Passando a Limpo</p>
