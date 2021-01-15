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

    public function detectGameByFolder($folder ){

        if ($folder === false) return false;

        foreach (['manhunt' => 'manhunt.exe', 'manhunt2' => 'Manhunt2.exe', ] as $game => $exe) {
            if (file_exists($folder . '/' . $exe)){
                return $game;
            }
        }

        return false;
    }

    public function readAndSendLevelList( ){
        $manhuntFolder = $this->config->get('manhunt_folder');
        $manhunt2Folder = $this->config->get('manhunt2_folder');
        if ($manhuntFolder === false && $manhunt2Folder === false) return false;

        $levels = [];
        if ($manhuntFolder !== false){
            $data = file_get_contents($manhuntFolder . '/initscripts/LEVELS/levels.txt');
            $data = explode("\n", $data);
            foreach ($data as $line){
                $line = trim($line);
                $line = str_replace("\t", " ", $line);

                if (substr($line, 0, 5) !== "LEVEL") continue;

                $folderName = trim(substr($line, 6));
                $folderName = explode(" ", $folderName)[0];

                if (is_dir($manhuntFolder . '/levels/' . strtolower($folderName)))
                    $levels[] = [
                        'game' => 'manhunt',
                        'icon' => '',
                        'name' => $folderName,
                        'folderName' => $folderName,
                        'folder' => '/levels/' . strtolower($folderName)
                    ];
            }
        }

        if ($manhunt2Folder !== false){
            $data = file_get_contents($manhunt2Folder . '/global/initscripts/resource23.glg');
            $data = (new NBinary($data))->binary;


            $translationData = file_get_contents($manhunt2Folder . '/global/game.gxt');
            $gxt = new Gxt();
            $translation = $gxt->unpack(new NBinary($translationData), MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC);

            $index = 0;
            foreach (explode("\n", $data) as $line){
                $line = trim($line);
                if (substr($line, 0, 5) !== "LEVEL") continue;


                $folderName = substr($line, 6);
                $folderNumber = (int)substr($folderName, 1, 2);
                $folderId = substr($folderName, 0, 3);

                if (is_dir($manhunt2Folder . '/levels/' . $folderName)){

                    $realName = false;
                    foreach ($translation as $pair) {
                        if ($pair['key'] !== "LVL_" . $folderNumber) continue;
                        $realName = $pair['text'];
                    }


                    $levels[] = [
                        'game' => 'manhunt2',
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


    public function readAndSendFile($game, $file ){

        if (strpos($file, ".pak#") !== false){
            list($pakFile, $innerFile) = explode("#", $file);
            $pakHandler = new \App\Service\Archive\Pak();
            $pakFiles = $pakHandler->unpack(
                new \App\Service\NBinary(file_get_contents($this->config->get($game . '_folder') . $pakFile)),
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
            $realFile = file_get_contents($this->config->get($game . '_folder') . $file);

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