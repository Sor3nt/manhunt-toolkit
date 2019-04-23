<?php
namespace App\Service\Archive\Glg\EntityTypeData;

use App\MHT;

class EcShot extends Ec {

    public $class           = MHT::EC_SHOT;
    public $name            = null;

    protected $map = [
        'CLASS' => true,
        'MODEL' => null,
        'MATERIAL' => null,
        'COLLISION_DATA' => null,
        'PHYSICS' => null,
        'LOD_DATA' => [],
    ];


}
