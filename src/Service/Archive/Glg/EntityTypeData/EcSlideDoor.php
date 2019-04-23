<?php
namespace App\Service\Archive\Glg\EntityTypeData;

use App\MHT;

class EcSlideDoor extends Ec {

    public $class           = MHT::EC_SLIDEDOOR;
    public $name            = null;

    protected $map = [
        'CLASS' => true,
        'MODEL' => null,
        'LOD_DATA' => [],
        'COLLISION_DATA' => null,
        'MATERIAL' => null,
        'ARMOUR_CLASS' => null,
        'HIT_POINTS' => null,
        'PHYSICS' => null,
        'PHYSICS' => null,

        'TRANSPARENT' => false,
        'DONT_LIGHT' => false,
    ];


}
