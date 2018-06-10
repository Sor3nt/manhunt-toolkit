<?php

namespace App\Bytecode\Mls;


use App\Bytecode\Mls\Struct\Header\HeaderAbstract;
use App\Service\Binary;

class Header {

    protected $lines;
    protected $name = "unknown";

    /** @var HeaderAbstract[]  */
    private $sections = [];

    /**
     * Header constructor.
     * @param Binary $srceCode
     */
    public function __construct( $lines ) {
        $this->lines = $lines;

        $this->parse();
    }

    /**
     * @return string
     */
    public function __toString(){
        return $this->name . ': ' . implode(", ", array_keys($this->sections));
    }

    /**
     * @param null|string $section
     * @return HeaderAbstract|HeaderAbstract[]
     * @throws \Exception
     */
    public function get( $section = null ){
        if (is_null($section)) return $this->sections;
        
        if (!isset($this->sections[ $section ])){
            throw new \Exception(sprintf('Section %s is not defined', $section));
        }
        
        return $this->sections[ $section ];
    }

    public function available(){
        return array_keys($this->sections);
    }


    /**
     * @throws \Exception
     */
    private function parse( ){

        if (substr(strtolower(current($this->lines)), 0, 11) !== 'scriptmain '){
            throw new \Exception('No header start section found. Excpecting scriptmain block');
        }


        $lines = $this->lines;
        $this->name = substr(array_shift($lines), 11);

        $section = false;

        foreach ($lines as $line) {

            $lline = strtolower($line);

            // we reach the script part, header is finish
            if (substr($lline, 0, 7) == "script ") break;

            if (in_array($lline, ["entity", "const", "var"])){

                $section = $lline;

                if(isset($this->sections[$section])){
                    throw new \Exception(sprintf('Section %s already defined', $section));
                }

                $this->sections[$section] = [];
                continue;
            }

            $classType = '\App\Bytecode\Mls\Struct\Header\Header' . ucfirst($section);

            if (!class_exists($classType)){
                throw new \Exception(sprintf('Unknown Header class %s', $classType));
            }

            $this->sections[$section][] = new $classType( $line );

        }
    }


}