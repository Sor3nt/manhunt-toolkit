<?php
namespace App\Service\Archive\Glg\EntityTypeData;

use App\MHT;

class EcSwitch extends Ec {

    public $class           = MHT::EC_SWITCH;
    public $name            = null;

    protected $map = [
        'CLASS' => true,
        'MODEL' => null,
        'COLLISION_DATA' => null,
        'USEABLE_CLASS' => null,
        'USE_DELAY' => null,
        'HOTSPOT_HORIZ_DIST' => null,
        'HOTSPOT_VERT_DIST' => null,
        'HOTSPOT_RADIUS' => null,
        'FOCUS_RADIUS' => null,
        'MUST_ALIGN' => false,

        'LOD_DATA' => [],

        'LOCKED' => false,
        'MULTIPLE' => false,
    ];

}
