<?php
namespace App\Service\Compiler\Tokens;

class T_DEFINE_SECTION_TYPE {

    static public function match( $input, $current ){

        $char = strtolower(substr($input, $current, 5));

        if ($char == "type "){
            return [
                'type' => 'T_DEFINE_SECTION_TYPE',
                'value' => "type"
            ];
        }



        return false;

//
//        if ($current <= 2){
//            return false;
//        }
//
//        $beforeChar = trim(substr($input, $current - 2, 2));
//        if ($beforeChar == ":"){
//
//            $value = "";
//            while($current < strlen($input)) {
//                $char = substr($input, $current, 1);
//
//                if ($char === ";"){
//                    return [
//                        'type' => 'T_DEFINE_TYPE',
//                        'value' => $value
//                    ];
//                }else{
//                    $value .= $char;
//                }
//
//                $current++;
//            }
//
//            throw new \Exception('T_DEFINE_TYPE: Invalid Code');
//
//        }
//
//        return false;
    }

}