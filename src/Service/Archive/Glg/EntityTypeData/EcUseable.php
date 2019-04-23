<?php
namespace App\Service\Archive\Glg\EntityTypeData;

use App\MHT;

class EcUseable extends Ec {

    public $class           = MHT::EC_USEABLE;
    public $name            = null;

    protected $map = [
        'CLASS' => true,
        'LOD_DATA' => [],
        'HOTSPOT_RADIUS' => null,
        'ANIMATION_BLOCK' => null,
        'USEABLE_ANIM' => null,
        'MODEL' => null,
        'COLLISION_DATA' => null,
        'FOCUS_RADIUS' => null,
        'USE_DELAY' => null,
        'SEARCHABLE' => false,
        'MATERIAL' => false,
        'USEABLE_CLASS' => null,
        'LOCKED' => false,
        'TRANSPARENT' => false,
        'SWITCH_MODEL' => false,
        'MULTIPLE' => false
    ];


}
