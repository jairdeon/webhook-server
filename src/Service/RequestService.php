<?php
class RequestService {
    public const FILENAME = 'requisition.json';
    public static function receiveRequest() {
        $request = json_decode(file_get_contents("php://input"), true);
        return json_encode($request, JSON_PRETTY_PRINT);
    }

    public static function makeFile($request) {
        $requisitionFile = fopen(self::FILENAME, "w") or die("Não foi possível abrir este arquivo.");
        fwrite($requisitionFile, $request);
        fclose($requisitionFile);

        $fileName = $_SERVER['DOCUMENT_ROOT'] . '/' . self::FILENAME;
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $finfo = finfo_file($finfo, $fileName);
        return new CURLFile($fileName, $finfo, self::FILENAME);
    }

    public static function makeRequest($file, $content): array
    {
        return [
            "username" => "Webhooks - TEST",
            "content" => $content,
            "tts" => "false",
            "file" => $file
        ];
    }

    public static function sendRequestToDiscord($discordServerURL, $body): void
    {
        $curl = curl_init($discordServerURL);
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

        self::deleteFile();
    }

    public static function deleteFile(): void
    {
        unlink(self::FILENAME);
    }
}