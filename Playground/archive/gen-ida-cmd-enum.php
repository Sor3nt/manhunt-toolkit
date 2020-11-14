<?php

require_once 'vendor/autoload.php';

use App\Service\CompilerV2\Manhunt2;
use App\Service\Helper;

$mh2 = new Manhunt2();

$command = "import idaapi;id = idc.AddEnum(0, \"COMMAND_NAMES\", idaapi.hexflag());";

foreach ($mh2->functions as $lName => $function) {

    if (isset($function['name'])) $name = $lName;
    else $name = $lName;

    if (strlen($function['offset']) !== 8) continue;

    $index = Helper::fromHexToInt($function['offset']);
    $command .= "idc.AddConstEx(id, \"" . $name . "\", " . $index . ", -1);";


}

echo $command;


