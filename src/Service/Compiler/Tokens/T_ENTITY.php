<?php
namespace App\Service\Compiler\Tokens;

class T_ENTITY {

    static public function match( $input, $current ){
die("old");
exit;
        $char = strtolower(substr($input, $current, 6));

        if ($char == "entity"){
            return [
                'type' => 'T_ENTITY',
                'value' => "entity"
            ];
        }

        return false;
    }

}