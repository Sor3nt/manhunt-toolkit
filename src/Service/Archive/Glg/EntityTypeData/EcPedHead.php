<?php
namespace App\Service\Archive\Glg\EntityTypeData;

use App\MHT;

class EcPedHead extends Ec {

    public $class           = MHT::EC_PEDHEAD;
    public $name            = null;

    protected $map = [
        'CLASS' => true,
        'MODEL' => null,
        'LOD_DATA' => [],
        'BND_SPH_BOOST' => null,
        'EXPLODE_BIT_MODEL_1' => null,
        'EXPLODE_BIT_MODEL_2' => null,
        'EXPLODE_BIT_MODEL_3' => null,
        'EXPLODE_BIT_MODEL_4' => null,
        'EXPLODEABLE' => false
    ];


}
