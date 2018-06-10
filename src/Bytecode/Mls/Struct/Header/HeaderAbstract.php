<?php

namespace App\Bytecode\Mls\Struct\Header;


use App\Service\Binary;

abstract class HeaderAbstract {

    protected $buffer;

    protected $name;

    protected $value;

    protected $type = null;


    public function __construct( $code )
    {
        $this->buffer = $code;
        $this->parse();
    }

    public function __toString(){
        return $this->name . ' : ' . $this->value .  ( is_null($this->type) ? '' : ' ' . $this->type);
    }


    private function parse(){

        if (strpos($this->buffer, ':') === false){
            throw new \Exception(sprintf('Invalid Buffer, no assignment found: %s', $this->buffer));
        }

        $line = str_replace(':', ' ', $this->buffer);

        $line = preg_replace("/\ {1,}/", ' ', $line);

        $parts = explode(' ', $line);

        $this->name = $parts[0];
        $this->value = $parts[1];

        if (isset($parts[2])){
            $this->type = $parts[2];
        }
    }

}