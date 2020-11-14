<?php

    $search = [
        'ForceNextState',
        'SetStand',
        'SetPedFlag',
        'StopMovePelvis',

    ];

    $asm = file_get_contents('mh2_vs2003_PAL_F.asm');

    $parts = explode('# =============== S U B R O U T I N E =======================================', $asm);
    unset($parts[0]);

    $result = [];
    foreach ($parts as $part) {

        $found = true;
        foreach ($search as $look) {
            if (strpos(strtolower($part), strtolower($look)) === false) $found = false;
        }

        if ($found) $result[] = $part;
    }

    if (count($result)){
        echo "Found " . count($result) . " functions";
        file_put_contents('mh2_vs2003_PAL_F.log', '');

        foreach ($result as $item) {
            file_put_contents('mh2_vs2003_PAL_F.log', $item, FILE_APPEND);
        }
    }