<?php
$filename = __DIR__ . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

require_once __DIR__ . '/../src/Router/Router.php';
require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__FILE__, 2));
$dotenv->load();

$router = new \Router\Router();
$router->set404(function () {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    echo '404, route not found!';
});

$router->set404('/api(/.*)?', function () {
    header('HTTP/1.1 404 Not Found');
    header('Content-Type: application/json');

    $jsonArray = array();
    $jsonArray['status'] = "404";
    $jsonArray['status_text'] = "route not defined";

    echo json_encode($jsonArray);
});

$router->before('GET', '/.*', function () {
    header('X-Powered-By: bramus/router');
});

$router->get('/', function () {
    die();
});

$router->post('/api/v1/test', function () {
    header('Content-Type: application/json');
    $request = json_decode(file_get_contents("php://input"), true);
    $request = json_encode($request, JSON_PRETTY_PRINT);

    $finalFileName = 'requisition.json';
    $requisitionFile = fopen($finalFileName, "w") or die("Unable to open file!");
    fwrite($requisitionFile, $request);
    fclose($requisitionFile);

    $fileName = $_SERVER['DOCUMENT_ROOT'] . '/' . $finalFileName;
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $finfo = finfo_file($finfo, $fileName);
    $cFile = new CURLFile($fileName, $finfo, $finalFileName);

    $body = [
        "username" => "Webhooks - TEST",
        "content" => "Teste de envio de requisição",
        "tts" => "false",
        "file" => $cFile
    ];

    $curl = curl_init($_ENV['TEST_DISCORD_WEBHOOK']);
    curl_setopt($curl, CURLOPT_TIMEOUT, 5);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $body);

    curl_exec($curl);
    curl_close($curl);
    unlink($finalFileName);
});

$router->post('/api/v1/webhook-omie', function () {
    header('Content-Type: application/json');
    $request = json_decode(file_get_contents("php://input"), true);

    $action = $request['topic'];
    $id = $request['messageId'];
    $licence = $request['appHash'];
    $request = json_encode($request, JSON_PRETTY_PRINT);

    $finalFileName = 'requisition.json';
    $requisitionFile = fopen($finalFileName, "w") or die("Unable to open file!");
    fwrite($requisitionFile, $request);
    fclose($requisitionFile);

    $fileName = $_SERVER['DOCUMENT_ROOT'] . '/' . $finalFileName;
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $finfo = finfo_file($finfo, $fileName);
    $cFile = new CURLFile($fileName, $finfo, $finalFileName);

    $ref = null;
    if (isset($_SERVER['HTTP_REFERER'])) {
        $ref = $_SERVER['HTTP_REFERER'];
    }

$content = "
**$licence** | *$action*
$id | $ref
";

    $body = [
        "username" => "Webhooks - OMIE",
        "content" => $content,
        "tts" => "false",
        "file" => $cFile
    ];

    $curl = curl_init($_ENV['OMIE_DISCORD_WEBHOOK']);
    curl_setopt($curl, CURLOPT_TIMEOUT, 5);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $body);

    curl_exec($curl);
    curl_close($curl);
    unlink($finalFileName);
});

$router->run();
