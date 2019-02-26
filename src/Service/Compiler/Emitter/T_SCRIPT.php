<?php
namespace App\Service\Compiler\Emitter;

use App\MHT;
use App\Service\Compiler\Evaluate;
use App\Service\Compiler\Token;
use App\Service\Helper;

class T_SCRIPT {

    static public function map( $node, \Closure $getLine, \Closure $emitter, $data ){

        $debugMsg = "[T_SCRIPT] map ";

        $code = [ ];

        /**
         * Create script start sequence
        */
        Evaluate::scriptStart($code, $getLine);

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
            Evaluate::reserveBytes($sum, $code, $getLine);
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

            Evaluate::readIndex(0, $code, $getLine);

            Evaluate::regularReturn($code, $getLine);

            Evaluate::readIndex(0, $code, $getLine);

            $code[$lastLineIndex]->hex = Helper::fromIntToHex(end($code)->lineNumber - 1);

            Evaluate::regularReturn($code, $getLine);


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

        Evaluate::scriptEnd(Token::T_SCRIPT, '00000000', $code, $getLine);

        return $code;
    }

}