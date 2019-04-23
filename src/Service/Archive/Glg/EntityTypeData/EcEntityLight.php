<?php
namespace App\Service\Archive\Glg\EntityTypeData;

use App\MHT;

class EcEntityLight extends Ec {

    public $class           = MHT::EC_ENTITYLIGHT;
    public $name            = null;

    protected $map = [
        'CLASS' => true,
        'MODEL' => null,

        'COLLISION_DATA' => null,
        'LOD_DATA' => [],
        'MATERIAL' => null,
        'ARMOUR_CLASS' => null,
        'HIT_POINTS' => null,
        'DAMAGE_FX' => null,
        'DEPTHOFFSET' => null,

        'ADDITIVEBLEND' => false,
        'NODEPTHWRITE' => false,
        'TRANSPARENT' => false,
        'DONT_LIGHT' => false,
        'LOCKED' => false,
    ];

}
