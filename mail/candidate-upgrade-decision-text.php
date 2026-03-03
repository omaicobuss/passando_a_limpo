<?php

/** @var app\models\CandidateUpgradeRequest $request */
/** @var app\models\User|null $user */
/** @var bool $approved */

$status = $approved ? 'aprovada' : 'reprovada';
$reviewDate = $request->reviewed_at ? date('d/m/Y H:i', (int) $request->reviewed_at) : '-';
?>
Ola <?= (string) ($user->username ?? 'usuario') ?>,

Sua solicitacao para mudanca de perfil para candidate foi <?= $status ?>.

Data da analise: <?= $reviewDate ?>
<?php if (!empty($request->admin_notes)): ?>
Observacao do administrador:
<?= (string) $request->admin_notes ?>
<?php endif; ?>

Se precisar, acesse sua area "Minha Conta" para acompanhar os detalhes.

Atenciosamente,
Equipe Passando a Limpo
