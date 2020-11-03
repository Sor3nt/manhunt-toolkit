<?php

namespace App\Service\Patch;

use App\MHT;
use App\Service\Resource;

abstract class PatchAbstract
{
    public $patchRoot = ".";
    public $resource;
    public $game;
    public $platform;
    public $debug;

    public $applied = [];
    public $error = [];
    public $exists = [];


    public function __construct( Resource $resource, $game = MHT::GAME_MANHUNT_2, $platform = MHT::PLATFORM_PC, $debug = false )
    {
        $this->resource = $resource;
        $this->game = $game;
        $this->platform = $platform;
        $this->debug = $debug;
    }

    abstract public function apply($patch);



}