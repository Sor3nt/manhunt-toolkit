<?php
namespace App\Service\Archive\Glg\EntityTypeData;

use App\MHT;

class EcHunter extends Ec {

    public $class           = MHT::EC_HUNTER;
    public $name            = null;

    protected $map = [
        'CLASS' => true,
        'LEADER' => null,
        'MODEL' => null,
        'IMPULSE_LIMIT' => null,
        'COLLISION_DATA' => null,
        'LOD_DATA' => [],
        'PHYSICS' => null,
        'STATE_SOUNDS' => null,
        'ANIMATION_BLOCK' => null,
        'HEAD' => null,
        'HEAD_SELECTION' => null,
        'VOICE' => null,
        'KO_TIME' => null,
        'STUN_TIME' => null,
        'HIT_POINTS' => null,
        'WALK_SPEED' => null,
        'RUN_SPEED' => null,
        'RUN_TIME' => null,
        'SWING_PAUSE' => null,
        'HIT_ACCURACY' => null,
        'MAX_LEG_DAMAGE' => null,
        'MAX_ARM_DAMAGE' => null,
        'BLOCK_DAMAGE' => null,
        'BND_SPH_BOOST' => null,
        'MELEE_TYPE' => null,
        'TRAIT_TYPE' => null,
        'BODY_TYPE' => null
    ];


}
