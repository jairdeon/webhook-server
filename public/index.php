<?php
require_once __DIR__ . '/../src/app/app.php';

$router = new \Router\Router();
$router->post('/api/v1/test', function () {
    header('Content-Type: application/json');
    $request = RequestService::receiveRequest();
    $file = RequestService::makeFile($request);
    $body = RequestService::makeRequest($file, "Teste de envio de requisição");
    RequestService::sendRequestToDiscord(getenv('TEST_DISCORD_WEBHOOK'), $body);
});

$router->post('/api/v1/galaxpay', function () {
    header('Content-Type: application/json');
    $request = RequestService::receiveRequest();
    $file = RequestService::makeFile($request);
    $body = RequestService::makeRequest($file, "Webhook - Galaxpay");
    RequestService::sendRequestToDiscord(getenv('GALAXPAY_DISCORD_WEBHOOK'), $body);
});

$router->post('/api/v1/omie', function () {
    header('Content-Type: application/json');
    $request = RequestService::receiveRequest();
    $file = RequestService::makeFile($request);

    $ref = null;
    if (isset($_SERVER['HTTP_REFERER'])) {
        $ref = $_SERVER['HTTP_REFERER'];
    }

    $decode = json_decode($request, true);
    $id = $decode['messageId'];
    $action = $decode['topic'];
    $licence = $decode['appHash'];
    $content = "**$licence** | *$action* \n $id | $ref";

    $body = RequestService::makeRequest($file, $content);
    RequestService::sendRequestToDiscord($_ENV['OMIE_DISCORD_WEBHOOK'], $body);
});

$router->run();
