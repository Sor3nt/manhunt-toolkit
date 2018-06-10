<?php
namespace App\Service;


use App\Bytecode\Mls\Header;
use App\Bytecode\Mls\Script;
use App\Bytecode\Mls\Scripts;
use App\Bytecode\Mls\Srce;



/**
 * Class Bytecode
 * @package App\Service
 *
 * Developed by Sor3nt 2018
 *
 * Bytecode help: https://docs.google.com/spreadsheets/d/1HgZ_K9Yp-KobflyKdVqZiF8aF819AGHbOg_b1YUbfz0/edit#gid=1239542518
 */

class Bytecode {

    /** @var Srce  */
    private $srce;

    public function __construct( Srce $srce ) {
        $this->srce = $srce;
    }


    private function prepare( $content ){


        // replace new lines to spaces
        $clean = str_replace("\n", " ", $content);

        // remove comments
        $clean = preg_replace("/\{(.*?)\}/", "", $clean);

        // remove all chars except of the wanted
        $clean = trim(preg_replace("/[^\-a-zA-Z\(\)\'\,\.\<\> _0-9\;\{\}\=\:\[\]]/", '', $clean));

        $lines = [];
        foreach (explode(";", $clean) as $line) {

            $line = trim($line);
            $lline = strtolower($line);

            $matchSection = false;
            foreach (
                [
                    'var ',
                    'entity ',
                    'begin ',

                ] as $case) {

                if (substr($lline, 0, strlen($case)) == $case){
                    $lines[] = strtoupper(trim($case));
                    $lines[] = substr($line, strlen($case));

                    $matchSection = true;
                }
            }

            if ($matchSection) continue;

            $lines[] = $line;

        }


        return $lines;
    }

    /**
     * @param $scriptCode
     * @param $game
     * @return array
     */
    public function process( $scriptCode, $game ){

        $bytecode = [];
        $strings = [];

        /** @var Header $header */
        /** @var Scripts $scripts */

        $scriptCode = $this->prepare($scriptCode);

        list($header, $scripts) = $this->srce->parse( $scriptCode );

        $offset = 0;
        foreach ($scripts->toByteCode($game, $offset) as $code) {
            $bytecode[] = $code;
        }

        foreach ($scripts->getStrings() as $str) {
            $strings[] = $str;
        }


        return [
            $bytecode,
            $strings
        ];
    }



}