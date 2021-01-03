<?php
ini_set('memory_limit','-1');

require_once __DIR__ . '/../../vendor/autoload.php';

class Api{

    private $folders = [
        'manhunt' => '/Users/matthias/Downloads/__MANHUNT/Manhunt 1/',
        'manhunt2' => '/Users/matthias/mh2/'
    ];

    public function loadFile($game, $file ){

        if (strpos($file, ".pak#") !== false){
            list($pakFile, $innerFile) = explode("#", $file);
            $pakHandler = new \App\Service\Archive\Pak();
            $pakFiles = $pakHandler->unpack(
                new \App\Service\NBinary(file_get_contents($this->folders[$game] . $pakFile)),
                \App\MHT::GAME_MANHUNT,
                \App\MHT::PLATFORM_PC
            );

            foreach ($pakFiles as $fileName => $data) {

                if (strtolower($fileName) == strtolower($innerFile)){
                    $realFile = $data;
                    break;


                }
            }

        }else{
            $realFile = file_get_contents($this->folders[$game] . $file);

        }


        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=data');
        header('Content-Transfer-Encoding: binary');
        header('Connection: Keep-Alive');
        header('Content-Length: ' . \mb_strlen($realFile, '8bit'));

        echo $realFile;
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
