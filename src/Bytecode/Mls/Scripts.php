<?php

namespace App\Bytecode\Mls;


use App\Service\Binary;
use Psr\Log\LoggerInterface;

class Scripts {


    private $lines = [];

    private $scripts = [];

    public function __construct($lines, LoggerInterface $logger)
    {
        $this->lines = $lines;


        $this->logger = $logger;

        $this->parseScripts();
    }

    public function getScripts(){
        return $this->scripts;
    }

    public function getStrings(){
        $strings = [];

        /** @var Scripts $scripts */
        foreach ($this->scripts as $script) {

            foreach ($script->getStrings() as $str) {
                $strings[] = $str;
            }
        }

        return $strings;
    }


    public function toByteCode( $game , &$offset){
        $bytecode = [];

        /** @var Scripts $scripts */
        foreach ($this->scripts as $script) {

            foreach ($script->toByteCode( $game, $offset ) as $code) {
                $bytecode[] = $code;
            }

        }

        return $bytecode;
    }


    /**
     * @return Script[]
     * @throws \Exception
     */
    public function parseScripts(){

        $this->logger->debug('Extract Scripts');

        $script = false;

        $stack = [];

        $scripts = [];

        foreach ($this->lines as $index => $line) {

            $line = trim($line);
            $lline = strtolower($line);

            if (empty($lline)) continue;

            if (substr($lline, 0, 7) === "script "){

                if ($script !== false){
                    throw new \Exception(sprintf('Script Block %s is still open, unable to process %s', $script, substr($lline, 7, -1)));
                }

                $script = substr($lline, 7);
                $stack[] = $script;

                $this->logger->debug('');
                $this->logger->debug(sprintf('Start a new script %s', $script));

                if(isset($scripts[ $script ]) && is_array($scripts[ $script ]) && count($scripts[ $script ])){
                    throw new \Exception(sprintf('Script %s already defined', $script));
                }

                $scripts[ $script ] = [];

                continue;
            }

            if ($script !== false){

                if (count($scripts[ $script ]) === 0 && $lline == "begin") continue;

                if (substr($lline, 0, 1) === "{"){
                    if (substr($lline, -1) !== "}" ){
                        throw new \Exception(sprintf('Script %s includes a invalid comment, multi line comments are not supported', $script));
                    }

                    $this->logger->debug(str_repeat('    ', count($stack)) . sprintf('Removing comment block', $line));

                    continue;
                }


                if (substr($lline, 0, 3) == "if " || substr($lline, 0, 3) == "if(" ) {
                    $this->logger->debug(str_repeat('    ', count($stack)) . 'Enter IF Statement');

                    $stack[] = 'if';
                    $scripts[$script][] = $line;
                    continue;

                }else if (substr($lline, 0, 6) == "while "){
                    $this->logger->debug(str_repeat('    ', count($stack)) . 'Enter WHILE Statement');

                    $stack[] = 'while';
                    $scripts[ $script ][] = $line;
                    continue;
                }

                if (substr($lline, 0, 3) == "end"){

                    if (count($stack) == 1){

                        $currentStack = array_pop($stack);
                        $this->logger->debug(str_repeat('    ', count($stack))  . "End " . $currentStack);

                        $script = false;
                        continue;
                    }else{

                        $currentStack = array_pop($stack);
                        $this->logger->debug(str_repeat('    ', count($stack))  . "End " . $currentStack);

                        $scripts[ $script ][] = $line;

                    }
                }else {
                    $scripts[ $script ][] = $line;

                }
            }

        }

        foreach ($scripts as $scriptName => &$script) {
            $script = new Script($scriptName, $script, $this->logger);
        }
        unset($script);

        $this->scripts = $scripts;
    }



}