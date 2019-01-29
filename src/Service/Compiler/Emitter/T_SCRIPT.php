<?php
namespace App\Service\Compiler\Emitter;

use App\MHT;
use App\Service\Helper;

class T_SCRIPT {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $debugMsg = "[T_SCRIPT] map ";

        $code = [ ];

        /**
         * Create script start sequence
         *
         * Note: we have here no names, its calculated by the offset inside the todo... section
         */
        $code[] = $getLine('10000000', false, $debugMsg . 'script start');
        $code[] = $getLine('0a000000', false, $debugMsg . 'script start');
        $code[] = $getLine('11000000', false, $debugMsg . 'script start');
        $code[] = $getLine('0a000000', false, $debugMsg . 'script start');
        $code[] = $getLine('09000000', false, $debugMsg . 'script start');

        /**
         * generate the needed bytes for the script
         */
        $sum = 0;
        foreach ($data['variables'] as $variable) {

            if (
                $variable['section'] == "script"
            ){

                // cleanup, das sollte HIER nicht passieren....
                if ($variable['size'] % 4 !== 0){
                    $variable['size'] += $variable['size'] % 4;
                }

                $sum += $variable['size'];
            }
        }

        if ($sum > 0){
            $code[] = $getLine('34000000', false, $debugMsg . 'reserve bytes');
            $code[] = $getLine('09000000', false, $debugMsg . 'reserve bytes');
            $code[] = $getLine(Helper::fromIntToHex($sum), false, $debugMsg . 'reserve bytes ' . $sum);
        }

        foreach ($node['body'] as $node) {

            $resultCode = $emitter( $node );

            if (is_null($resultCode)){
                throw new \Exception('Return was null, a emitter missed a return statement ?');
            }

            foreach ($resultCode as $line) {
                $line->debug = $debugMsg . ' ' . $line->debug;
                $code[] = $line;
            }
        }

        /**
         * Create script end sequence
         */
        if ($data['game'] == MHT::GAME_MANHUNT_2){
            $code[] = $getLine('11000000', false, $debugMsg . 'script end');
            $code[] = $getLine('09000000', false, $debugMsg . 'script end');
            $code[] = $getLine('0a000000', false, $debugMsg . 'script end');
            $code[] = $getLine('0f000000', false, $debugMsg . 'script end');
            $code[] = $getLine('0a000000', false, $debugMsg . 'script end');
            $code[] = $getLine('3b000000', false, $debugMsg . 'script end');
            $code[] = $getLine('00000000', false, $debugMsg . 'script end');

        }else{
            $code[] = $getLine('11000000', false, $debugMsg . 'script end');
            $code[] = $getLine('09000000', false, $debugMsg . 'script end');
            $code[] = $getLine('0a000000', false, $debugMsg . 'script end');
            $code[] = $getLine('0f000000', false, $debugMsg . 'script end');
            $code[] = $getLine('0a000000', false, $debugMsg . 'script end');
            $code[] = $getLine('3b000000', false, $debugMsg . 'script end');
            $code[] = $getLine('00000000', false, $debugMsg . 'script end');

        }


        return $code;
    }

}