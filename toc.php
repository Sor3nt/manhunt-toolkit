<?php

require_once 'vendor/autoload.php';

use App\MHT;
use App\Service\Resources;

$files = [
    'collisions.col' => 'collisions.col',
    'scene1.bsp' => 'scene1.bsp',
    'scene2.bsp' => 'scene2.bsp',
    'levelsetup.ini' => 'levelsetup.ini',
    'mapAI.grf' => 'mapAI.grf',
    'entity.inst' => 'entity.inst',
    'entity2.inst' => 'entity2.inst',
    'scrap2.mls' => 'scripts/scrap2.mls',
    'splines.spl' => 'spl/splines.spl',
    'splines.ini' => 'spl/splines.ini',
    'allanims.ifp' => 'allanims.ifp',
    'entitytypedata.ini' => 'entitytypedata.ini',
    'picmap.txd' => 'picmap.txd',
    'picmmap.txd' => 'picmmap.txd'
];

$result = [];

foreach ($files as $tocName => $file) {
    if (file_exists($file)){
        $result[] = sprintf('%s %s', $tocName, filesize($file));
    }else{

        var_dump("File not found ?! " . $file);exit;
    }
}

file_put_contents('toc.txt', implode("\r\n",$result) . "\r\n");

echo sprintf("\ntoc.txt generated!") . "\n";
