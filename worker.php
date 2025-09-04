<?php

require __DIR__ . '/vendor/autoload.php';

require __DIR__ . '/wp-load.php'; // Se precisar do WordPress

use Tok\MPSubscriptions\Infrastructure\HttpClient;

$accessToken = get_option('MP_ACCESS_TOKEN');
$client = new HttpClient($accessToken);

while (true) {
    $client->processSqsJobs();

    // Espera 5 segundos antes do próximo loop
    sleep(5);
}
