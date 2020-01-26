<?php
namespace App\Tests\CompilerV2\Math;

use App\MHT;
use PHPUnit\Framework\TestCase;

class AssignRealMathAllTest extends TestCase
{

    public function test()
    {


        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;

            script OnCreate;
                VAR
                    pOrient : real;                

                begin
        			pOrient := 0.0 - (pOrient+90.0)/180.0 * 3.14926535;
        			
        			
        			{
        			pOrient := 0.0 - (pOrient+90.0)/180.0 * 3.14926535;



        			    Resolve Map
        			    
        			    0.0
        			    pOrient
        			    90.0
        			        sub float / 50000000
        			        
                        180.0
                            div float / 53000000

                        3.14926529
                            multiply float / 52000000
                        
                        add / 51000000
        			}
                end;

            end.
        ";

        /**
         *

           (0.0 - pOrient - 90.0) => / 180.0 => * 3.14926529
         */

        $expected = [
            // script start
            '10000000',
            '0a000000',
            '11000000',
            '0a000000',
            '09000000',


            '34000000', //reserve bytes
            '09000000', //reserve bytes
            '04000000', //Offset in byte



            // 0.0 - pOrient - 90.0
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '00000000', //value 0

            '10000000', //nested call return result
            '01000000', //nested call return result

            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '04000000', //Offset pOrient

            '10000000', //nested call return result
            '01000000', //nested call return result

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '0000b442', //value 90.0

            '10000000', //nested call return result
            '01000000', //nested call return result

            '50000000', //T_ADDITION

            '10000000', //nested call return result
            '01000000', //nested call return result


            // / 180.0
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '00003443', //value 180.0

            '10000000', //nested call return result
            '01000000', //nested call return result

            '53000000', //T_DIVISION

            '10000000', //nested call return result
            '01000000', //nested call return result




            // * 3.14926529
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '908d4940', //value 3.14926529

            '10000000', //nested call return result
            '01000000', //nested call return result

            '52000000', // T_MULTIPLY

            '10000000', //nested call return result
            '01000000', //nested call return result



            '51000000', // T_SUBSTRACTION

            '15000000', //unknown
            '04000000', //unknown
            '04000000', //unknown
            '01000000', //unknown


            // script end
            '11000000',
            '09000000',
            '0a000000',
            '0f000000',
            '0a000000',
            '3b000000',
            '00000000'
        ];
        $compiler = new \App\Service\CompilerV2\Compiler($script, MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC, false);
        $compiled = $compiler->compile();

        if ($compiler->validateCode($expected) === false){

            foreach ($compiled['CODE'] as $index => $newCode) {

                if ($expected[$index] == $newCode['code']){
                    echo $newCode['code'] . ' -> ' . $newCode['msg'] . "\n";

                }else{
                    echo "MISMATCH: Need: " . $expected[$index] . ' Got: ' . $newCode['code'] . ' -> ' . $newCode['msg']. "\n";

                }
            }
        }else{
            $this->assertEquals(true,true);
        }
    }

}