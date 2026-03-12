<?php

return [
    'adminEmail' => 'financeiro@helum.com.br',
    'senderEmail' => 'financeiro@helum.com.br',
    'senderName' => 'Helum Financeiro',
    'mailer' => [
        'useFileTransport' => filter_var(getenv('MAILER_USE_FILE_TRANSPORT') ?: (YII_ENV_DEV ? '1' : '0'), FILTER_VALIDATE_BOOL),
        'scheme' => getenv('SMTP_SCHEME') ?: 'smtp',
        'host' => getenv('SMTP_HOST') ?: 'smtp.helum.com.br',
        'port' => (int) (getenv('SMTP_PORT') ?: 587),
        'username' => getenv('SMTP_USERNAME') ?: 'financeiro@helum.com.br',
        'password' => getenv('SMTP_PASSWORD') ?: '',
        'encryption' => getenv('SMTP_ENCRYPTION') ?: 'tls',
        'authMode' => getenv('SMTP_AUTH_MODE') ?: 'login',
    ],
];
