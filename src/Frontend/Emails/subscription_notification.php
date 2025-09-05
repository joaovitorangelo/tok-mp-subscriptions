<!-- subscription_confirmation.php -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= $subject ?? 'Assinatura' ?></title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .btn { background-color: #f4b41a; color: #fff; padding: 12px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
    </style>
</head>
<body>
    <h2>Olá <?= htmlspecialchars($user_name) ?>!</h2>
    <p>Clique no botão abaixo para continuar e concluir a compra do seu plano:</p>
    <p><a href="<?= $plan_link ?>" class="btn">Concluir Assinatura</a></p>
    <p>Obrigado por escolher a nossa cervejaria!</p>
</body>
</html>
