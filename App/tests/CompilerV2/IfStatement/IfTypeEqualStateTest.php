<?php
namespace App\Tests\CompilerV2\IfStatement;

use App\MHT;
use PHPUnit\Framework\TestCase;

class IfTypeEqualStateTest extends TestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;
   
            TYPE
                tLevelState = ( StartOfLevel, GasCan, Chase, EndChase );

            var
                lLevelState : tLevelState;


            script OnCreate;
                VAR
                    pos: vec3d;
                begin
                
                    if lLevelState <> EndChase then
                    begin
                        SetVector(pos, 0.0, 1.0, 0.0);
                    end;

                end;
            
                        
                
            end.
        ";

        $expected = [
            '10000000', //Script start block
            '0a000000', //Script start block
            '11000000', //Script start block
            '0a000000', //Script start block
            '09000000', //Script start block

            '34000000', //reserve bytes
            '09000000', //reserve bytes
            '0c000000', //Offset in byte

            '14000000', //Read VAR from header
            '01000000', //Read VAR from header
            '04000000', //Read VAR from header
            '00000000', //Offset

            '10000000', //nested call return result
            '01000000', //nested call return result

            '12000000', //parameter (temp int)
            '01000000', //parameter (temp int)
            '03000000', //value 3

            '0f000000', //parameter (temp int)
            '04000000', //parameter (temp int)

            '23000000', //statement (core)
            '04000000', //statement (core)
            '01000000', //statement (core)
            '12000000', //statement (core)
            '01000000', //statement (core)
            '01000000', //statement (core)

            '40000000', //statement (core)(operator un-equal)
            '78000000', //statement (core)( Offset )
            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            'e4000000', //Offset (line number 12571)

            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '0c000000', //Offset in byte

            '10000000', //nested call return result
            '01000000', //nested call return result

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '00000000', //value 0

            '10000000', //nested call return result
            '01000000', //nested call return result

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '0000803f', //value 1065353216

            '10000000', //nested call return result
            '01000000', //nested call return result

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '00000000', //value 0

            '10000000', //nested call return result
            '01000000', //nested call return result

            '84010000', //SetVector Call




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
                    echo $index . " " . $newCode['code'] . ' -> ' . $newCode['msg'] . "\n";

                }else{
                    echo "MISMATCH: Need: " . $expected[$index] . ' Got: ' . $newCode['code'] . ' -> ' . $newCode['msg']. "\n";

                }
            }
        }else{
            $this->assertEquals(true,true);
        }
    }

}