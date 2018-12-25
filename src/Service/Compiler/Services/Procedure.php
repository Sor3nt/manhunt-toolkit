<?php
namespace App\Service\Compiler\Services;

class Procedure {

    private $procedures = [];

    public function clear(){
        $this->procedures = [];
    }


    public function add( $procedures){

        $this->procedures = array_merge($this->procedures, $procedures);

    }

    public function get( $name ){
        if (isset($this->procedures[ $name ])) return $this->procedures[ $name ];
        return false;
    }

}