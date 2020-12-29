<?php

class Api{

    private $folders = [
        'manhunt2' => '/Users/matthias/mh2/'
    ];

    public function loadFile($game, $file ){

        $realFile = $this->folders[$game] . $file;
//        $data = file_get_contents($realFile );

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=data');
        header('Content-Transfer-Encoding: binary');
        header('Connection: Keep-Alive');
        header('Content-Length: ' . filesize($realFile));

        readfile($realFile);
    }

}

$api = new Api();
$json = file_get_contents("php://input");
$json = \json_decode( $json, true );

switch ($json['action']){

    case 'read':
        $api->loadFile($json['game'], $json['file']);
        break;

}
