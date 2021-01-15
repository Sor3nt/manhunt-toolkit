<?php

use App\Service\Api;

ini_set('memory_limit','-1');

require_once __DIR__ . '/../../vendor/autoload.php';

$api = new Api();

$json = \json_decode(file_get_contents("php://input"), true );

switch ($json['action']){

    case 'setConfig':

        $api->config->config = $json['data'];

        foreach (['manhunt', 'manhunt2'] as $game) {
            if ($api->config->config[$game . '_folder'] === false) continue;

            $detectedGame = $api->detectGameByFolder(realpath($api->config->config[$game . '_folder']));
            if ($detectedGame == false || $detectedGame !== $game ) $api->send(\json_encode([
                'status' => false,
                'field' => $game . '_folder'
            ]));
        }

        $api->config->save();

        $api->sendStatus(true);
        break;

    case 'getConfig':
        $api->send([
            'status' => true,
            'data' => $api->config->config
        ]);
        break;

    case 'getLevels':
        $levels = $api->readAndSendLevelList();
        if ($levels === false)
            $api->sendStatus(false);

        $api->send([ 'status' => true, 'data' => $levels ]);

        break;

    case 'read':
        $api->readAndSendFile($json['game'], $json['file']);
        break;
}
