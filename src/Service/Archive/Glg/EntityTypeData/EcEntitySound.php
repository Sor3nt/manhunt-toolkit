<?php
namespace App\Service\Archive\Glg\EntityTypeData;

use App\MHT;

class EcEntitySound extends Ec {

    public $class           = MHT::EC_ENTITYSOUND;
    public $name            = null;

    protected $map = [
        'CLASS' => true,
        'MODEL' => null,

        'LOD_DATA' => [],

        'LOCKED' => false,
    ];

}
