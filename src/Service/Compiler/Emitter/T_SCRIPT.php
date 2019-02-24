<?php
namespace App\Service\Compiler\Emitter;

use App\MHT;
use App\Service\Compiler\Token;
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

        if (isset($node['body'][0]) && $node['body'][0]['type'] == Token::T_DEFINE_SECTION_ARG){

            $code[] = $getLine('10030000', false, $debugMsg . 'argument init');
            $code[] = $getLine('24000000', false, $debugMsg . 'argument init');
            $code[] = $getLine('01000000', false, $debugMsg . 'argument init');
            $code[] = $getLine('00000000', false, $debugMsg . 'argument init');
            $code[] = $getLine('3f000000', false, $debugMsg . 'argument init');
            $code[] = $getLine('__END_OFFSET__', false, $debugMsg . 'argument end offset');
//
            $lastLineIndex = count($code) - 1;
//

            $code[] = $getLine('12000000', false, $debugMsg . 'argument init');
            $code[] = $getLine('01000000', false, $debugMsg . 'argument init');
            $code[] = $getLine('00000000', false, $debugMsg . 'argument init');
//            $code[] = $getLine(Helper::fromIntToHex($rightHandNewMapped['order']), false, $debugMsg . 'argument init');
            $code[] = $getLine('10000000', false, $debugMsg . 'argument init');
            $code[] = $getLine('01000000', false, $debugMsg . 'argument init');


            $code[] = $getLine('12000000', false, $debugMsg . 'argument init');
            $code[] = $getLine('01000000', false, $debugMsg . 'argument init');

            $code[$lastLineIndex]->hex = Helper::fromIntToHex(end($code)->lineNumber);

            $code[] = $getLine('00000000', false, $debugMsg . 'read argument fallback (offset todo)...');
            $code[] = $getLine('10000000', false, $debugMsg . 'argument init');
            $code[] = $getLine('01000000', false, $debugMsg . 'argument init');


            $code[] = $getLine('0a030000', false, $debugMsg . 'argument init');


            $code[] = $getLine('15000000', false, $debugMsg . 'argument init');
            $code[] = $getLine('04000000', false, $debugMsg . 'argument init');
            $code[] = $getLine('04000000', false, $debugMsg . 'argument init');
            $code[] = $getLine('01000000', false, $debugMsg . 'argument init');


            $code[] = $getLine('0f030000', false, $debugMsg . 'argument init 2');

//            unset($node['body'][0]);

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