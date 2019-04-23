<?php
namespace App\Service\Archive\Glg\EntityTypeData;

use App\MHT;

class EcDoor extends Ec {

    public $class           = MHT::EC_DOOR;
    public $name            = null;

    protected $map = [
        'CLASS' => true,
        'MODEL' => null,
        'COLLISION_DATA' => null,
        'LOD_DATA' => [],
        'MATERIAL' => null,
        'ARMOUR_CLASS' => null,
        'HIT_POINTS' => null,
        'PHYSICS' => null,

        'MUST_ALIGN' => false,
        'TRANSPARENT' => false,
    ];


}
