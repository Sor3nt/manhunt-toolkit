<?php
namespace App\Service\Archive\Glg\EntityTypeData;

use App\MHT;

class EcCollectable extends Ec {

    public $class           = MHT::EC_COLLECTABLE;
    public $name            = null;

    protected $map = [
        'CLASS' => true,
        'COLLECTABLE_TYPE' => null,
        'ANIMATION_BLOCK' => null,
        'HOLSTER_SLOT' => null,
        'MODEL' => null,
        'COLLISION_DATA' => null,
        'PHYSICS' => null,
        'HOTSPOT_RADIUS' => null,
        'LOD_DATA' => [],
        'SELECT_DELAY' => null,
        'HOLSTER_TRANSLATION' => null,
        'HOLSTER_ROTATION' => null,
        'DISPLAY_SCALE' => null,
        'MODEL_DISPLAY_ANIM' => null,
        'BND_SPH_BOOST' => null,

        'TRANSPARENT' => false,
    ];


}
