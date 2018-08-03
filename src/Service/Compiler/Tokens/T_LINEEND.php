<?php
namespace App\Service\Compiler\Tokens;

class T_LINEEND {

    static public function match( $input, $current, $tokens ){

        $chars = substr($input, $current, 1);

        if ($chars == ";"){

            return [
                'type' => 'T_LINEEND',
                'value' => ";"
            ];

        }

        return false;

    }

}