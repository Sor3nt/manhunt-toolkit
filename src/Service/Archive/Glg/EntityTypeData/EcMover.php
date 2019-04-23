<?php
namespace App\Service\Archive\Glg\EntityTypeData;

use App\MHT;

class EcMover extends Ec {

    public $class           = MHT::EC_MOVER;
    public $name            = null;

    protected $map = [
        'CLASS' => true,
        'MODEL' => null,
        'ANIMATION_BLOCK' => null,
        'COLLISION_DATA' => null,
        'LOD_DATA' => [],
        'MATERIAL' => null,
        'PHYSICS' => null,
        'ATTACH_ENTITY_NAME' => null,
        'ATTACH_ENTITY_TO_HELPER' => null,
        'DONT_LIGHT' => false
    ];


}
