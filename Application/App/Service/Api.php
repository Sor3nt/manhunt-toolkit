<?php

namespace App\Service;


use App\MHT;
use App\Service\Archive\Gxt;

class Api
{
    /** @var Config */
    public $config;

    public function __construct(){
        $this->config = new Config();
    }

    public function detectGameAndPlatformByFolder($folder ){

        if ($folder === false) return false;

        foreach ([MHT::GAME_MANHUNT => 'manhunt.exe', MHT::GAME_MANHUNT_2 => 'Manhunt2.exe', ] as $game => $exe) {
            if (file_exists($folder . '/' . $exe)){
                return [
                    'game' => $game,
                    'platform' => MHT::PLATFORM_PC
                ];
            }
        }

        if (file_exists($folder . '/UMD_DATA.BIN')){

            if (!file_exists($folder . '/MOVIES/A01_ES_I.PMF')){
                return [
                    'game' => MHT::GAME_MANHUNT_2,
                    'platform' => MHT::PLATFORM_PSP_001
                ];
            }

            return [
                'game' => MHT::GAME_MANHUNT_2,
                'platform' => MHT::PLATFORM_PSP
            ];
        }

        return false;
    }

    public function getLevelList( $id ){

        $levels = [];

        $game = $this->config->getGame($id);

        if ($game['game'] == MHT::GAME_MANHUNT && $game['platform'] === MHT::PLATFORM_PC) {
            $data = file_get_contents($game['path'] . '/initscripts/LEVELS/levels.txt');
            $data = explode("\n", $data);
            foreach ($data as $line) {
                $line = trim($line);
                $line = str_replace("\t", " ", $line);

                if (substr($line, 0, 5) !== "LEVEL") continue;

                $folderName = trim(substr($line, 6));
                $folderName = explode(" ", $folderName)[0];

                if (is_dir($game['path'] . '/levels/' . strtolower($folderName)))
                    $levels[] = [
                        'icon' => '',
                        'name' => $folderName,
                        'folderName' => $folderName,
                        'folder' => '/levels/' . strtolower($folderName)
                    ];
            }

        }else if ($game['game'] == MHT::GAME_MANHUNT_2 && $game['platform'] === MHT::PLATFORM_PC){
            $data = file_get_contents($game['path'] . '/global/initscripts/resource23.glg');
            $data = (new NBinary($data))->binary;


            $translationData = file_get_contents($game['path'] . '/global/game.gxt');
            $gxt = new Gxt();
            $translation = $gxt->unpack(new NBinary($translationData), MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC);

            $index = 0;
            foreach (explode("\n", $data) as $line){
                $line = trim($line);
                if (substr($line, 0, 5) !== "LEVEL") continue;

                $folderName = substr($line, 6);
                $folderNumber = (int)substr($folderName, 1, 2);
                $folderId = substr($folderName, 0, 3);

                if (is_dir($game['path'] . '/levels/' . $folderName)){

                    $realName = false;
                    foreach ($translation as $pair) {
                        if ($pair['key'] !== "LVL_" . $folderNumber) continue;
                        $realName = $pair['text'];
                    }

                    $levels[] = [
                        'folderName' => $folderName,
                        'icon' => strtolower($folderId),
                        'name' => $realName,
                        'folder' => '/levels/' . strtolower($folderName)
                    ];

                }

                $index++;

            }
        }


        return $levels;
    }


    public function readAndSendFile($gameId, $file ){

                $game = $this->config->getGame($gameId);

        if (strpos($file, ".pak#") !== false){
            list($pakFile, $innerFile) = explode("#", $file);
            $pakHandler = new \App\Service\Archive\Pak();
            $pakFiles = $pakHandler->unpack(
                new \App\Service\NBinary(file_get_contents($game['path'] . "/"  . $pakFile)),
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
            $realFile = file_get_contents($game['path'] . "/" . $file);
        }

        $this->send($realFile);
    }

    public function sendStatus( $status ){
        $this->send([ 'status' => $status ]);
    }

    public function send($file){

        if (is_array($file))
            $file = \json_encode($file);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=data');
        header('Content-Transfer-Encoding: binary');
        header('Connection: Keep-Alive');
        header('Content-Length: ' . \mb_strlen($file, '8bit'));

        echo $file;
        exit;

    }


}