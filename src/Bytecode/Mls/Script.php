<?php

namespace App\Bytecode\Mls;

use App\Bytecode\Helper;
use App\Bytecode\Mls\Struct\Script\ScriptAbstract;
use App\Bytecode\Mls\Struct\Script\ScriptFunction;
use App\Service\Binary;
use Psr\Log\LoggerInterface;

class Script extends Helper {

    /** @var LoggerInterface  */
    private $logger;

    protected $name = 'unknown';

    protected $lines = [];

    private $strings = [];

    private $stack = [];

    public function __construct( $name, array $lines = [], LoggerInterface $logger ) {
        $this->name = $name;
        $this->lines = $lines;
        $this->logger = $logger;

        $this->parse();
    }

    public function getStrings(){
        return $this->strings;
    }

    private function parse(){

        foreach ($this->lines as $line) {

            $lline = strtolower($line);

            //we have: if / while or function call(s)
            if (strpos($line, '(') !== false){

                if (substr($lline, 0, 3) === 'if ' || substr($lline, 0, 3) === 'if(') {
                    throw new \Exception(sprintf('IF statements not implemented yet', $line));
                }else if (substr($lline, 0, 6) == "while "){
                    throw new \Exception(sprintf('WHILE statements not implemented yet', $line));
                }else{

                    $this->stack[] = $this->parseCommandCalls($line);
                }


            }else{
                throw new \Exception(sprintf('Unable to detect line definition for: %s', $line));
            }

        }

    }

    /**
     * @param $line
     * @return ScriptAbstract[]
     */
    private function parseCommandCalls( $line ){

        list($stringMap, $line) = $this->extractAndReplaceStrings($line);

        //append strings to the list
        foreach ($stringMap as $string) {
            $this->strings[] = $string;
        }

        $callSplit = $this->splitInnerToOuter($line);

        $callStack = [];

        foreach ($callSplit as $call) {
            $callStack[] = new ScriptFunction( $call['function'], $call['parameters'], $stringMap );
        }

        return $callStack;
    }

    public function __toString()
    {
        return sprintf('Script %s', $this->name);
    }

    public function toByteCode( $game, &$offset = 0 ){

        $bytecode = [];

        // initialize script block
        $bytecode[] = "\x10";
        $bytecode[] = "\x0a";
        $bytecode[] = "\x11";
        $bytecode[] = "\x0a";
        $bytecode[] = "\x09";


        /** @var ScriptAbstract[] $entryStack */

        foreach ($this->stack as $entryStack) {

            foreach ($entryStack as $entry) {

                $code = $entry->toByteCode( $game, $offset );

                foreach ($code as $item) {
                    $bytecode[] = $item;
                }
            }

        }

        // end script block
        $bytecode[] = "\x11";
        $bytecode[] = "\x09";
        $bytecode[] = "\x0a";
        $bytecode[] = "\x0f";
        $bytecode[] = "\x0a";
        $bytecode[] = "\x3b";
        $bytecode[] = "\x00";


        // convert any sequence into 8-byte blocks
        foreach ($bytecode as &$code) {
            $code = hex2bin($this->pad(bin2hex($code)));
        }

        return $bytecode;
    }


}