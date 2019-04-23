<?php
namespace App\Service\Archive\Glg\EntityTypeData;

use App\MHT;

class EcResponder extends Ec {

    public $class           = MHT::EC_RESPONDER;
    public $name            = null;

    protected $map = [
        'CLASS' => true,
        'MATERIAL' => null,
        'COLLECTABLE_TYPE' => null,
        'ANIMATION_BLOCK' => null,
        'HOLSTER_SLOT' => null,
        'MODEL' => null,
        'WEAPON' => null,
        'KICK_WEAPON' => null,
        'OBSTRUCT_WEAPON' => null,
        'COLLISION_DATA' => null,
        'HOLSTER_MODEL' => null,
        'PHYSICS' => null,
        'HOTSPOT_RADIUS' => null,
        'LOD_DATA' => [],
        'ATTACHABLE' => null,
        'FIRE_DELAY' => null,
        'USE_DELAY' => null,
        'SELECT_DELAY' => null,
        'HOLSTER_TRANSLATION' => null,
        'HOLSTER_ROTATION' => null,
        'MODEL_DISPLAY_ANIM' => null,
        'BND_SPH_BOOST' => null,

        'TRANSPARENT' => false,
        'KICKABLE' => false
    ];


}
