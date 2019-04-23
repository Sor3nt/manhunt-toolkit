<?php
namespace App\Service\Archive\Glg\EntityTypeData;

use App\MHT;

class EcBasic extends Ec {

    public $class           = MHT::EC_BASIC;
    public $name            = null;

    protected $map = [
        'CLASS' => true,
        'MODEL' => null,
        'ANIMATION_BLOCK' => null,
        'COLLISION_DATA' => null,
        'LOD_DATA' => [],
        'MATERIAL' => null,
        'ARMOUR_CLASS' => null,
        'HIT_POINTS' => null,
        'DEPTHOFFSET' => null,
        'SPAWN' => null,
        'PHYSICS' => null,
        'DAMAGE_FX' => null,
        'TEXTURE_ANIM' => null,
        'DAMAGE_SPAWN' => null,
        'SPAWN_VELOCITY' => null,
        'BND_SPH_BOOST' => null,



        'SMASHABLE' => false,
        'MIRROR' => false,
        'ADDITIVEBLEND' => false,
        'NODEPTHWRITE' => false,
        'TRANSPARENT' => false,
        'DONT_LIGHT' => false,
        'LOCKED' => false,
        'SMASHABLE4' => false,
        'SHADOW' => false,
        'KICKABLE' => false,
        'ROTATELIKEFANX' => false,
//        'EXPLODEABLE' => false,
    ];


}
