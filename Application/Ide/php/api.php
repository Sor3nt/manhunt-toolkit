<?php

use App\MHT;
use App\Service\Api;

ini_set('memory_limit','-1');

require_once __DIR__ . '/../../vendor/autoload.php';

$api = new Api();

$json = \json_decode(file_get_contents("php://input"), true );

switch ($json['action']){

    case 'addGame':

        $folder = $json['data'];

        $gamePlatform = $api->detectGameAndPlatformByFolder($folder);
        if ($gamePlatform == false) $api->sendStatus(false);

        $info = $gamePlatform;
        $info['path'] = realpath($folder);

        $info['id'] = $api->config->addGame(
            $gamePlatform['game'],
            $gamePlatform['platform'],
            $info['path']
        );

        $api->config->save();

        $api->send([
            'status' => true,
            'data' => $info
        ]);
        break;

    case 'getConfig':
        $api->send([
            'status' => true,
            'data' => $api->config->config
        ]);
        break;

    case 'getLevels':
        $levels = $api->getLevelList($json['id']);
        if ($levels === false)
            $api->sendStatus(false);

        $api->send([ 'status' => true, 'data' => $levels ]);

        break;

    case 'read':
        $api->readAndSendFile($json['gameId'], $json['file']);
        break;
}
