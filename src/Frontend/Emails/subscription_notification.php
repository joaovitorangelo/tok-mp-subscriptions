<!-- subscription_notification.php -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= $subject ?? 'Finalize sua assinatura!' ?></title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9f9f9;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 40px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        h2 {
            color: #f4b41a;
            font-size: 28px;
            margin-bottom: 20px;
        }
        p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        .btn {
            background-color: #f4b41a;
            color: #fff;
            padding: 15px 25px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #d9a31f;
        }
        .footer {
            font-size: 12px;
            color: #999;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="<?= wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'full') ?: '' ?>" alt="Logo da Cervejaria" style="max-width: 150px; margin-bottom: 20px;">
        <h2>Olá <?= htmlspecialchars($user_name) ?>!</h2>
        <p>Estamos felizes que você escolheu se juntar à nossa comunidade! 🎉</p>
        <p>Para finalizar sua assinatura, clique no botão abaixo:</p>
        <p><a href="<?= $plan_link ?>" class="btn">Concluir Assinatura</a></p>
        <p>Se tiver qualquer dúvida, nossa equipe está pronta para te ajudar.</p>
        <p>Obrigado por escolher a nossa cervejaria! 🍺</p>
        <div class="footer">
            Você recebeu este e-mail porque se inscreveu em nosso site. Se não reconhece esta ação, por favor ignore esta mensagem.
        </div>
    </div>
</body>
</html>
