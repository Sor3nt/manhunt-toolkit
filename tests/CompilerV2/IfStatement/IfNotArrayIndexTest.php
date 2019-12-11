<?php
namespace App\Tests\CompilerV2\IfStatement;

use App\MHT;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IfNotArrayIndexTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;

            VAR
            	bBurnableBurnt : array[1..2] of boolean;

            script OnCreate;

                begin
                
                    if NOT bBurnableBurnt[1] then RadarPositionSetEntity(GetEntity('Warehouse_Burnable01'), MAP_COLOR_BLUE);


                end;

            end.
        ";

        $expected = [
            // script start
            '10000000',
            '0a000000',
            '11000000',
            '0a000000',
            '09000000',

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '1c000000', //Offset bBurnableBurnt

            '10000000', //nested call return result
            '01000000', //nested call return result

            '12000000', //simple value pointer
            '01000000', //simple value pointer
            '01000000', //index 1

            '34000000', //unknown
            '01000000', //unknown
            '01000000', //unknown

            '12000000', //read array position
            '04000000', //read array position
            '04000000', //offset

            '35000000', //unknown
            '04000000', //unknown

            '0f000000', //unknown
            '04000000', //unknown

            '31000000', //unknown
            '04000000', //unknown
            '01000000', //unknown

            '10000000', //unknown
            '04000000', //unknown

            '0f000000', //unknown
            '02000000', //unknown

            '18000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '02000000', //unknown

            '29000000', //NOT
            '01000000', //NOT
            '01000000', //NOT

            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)

            '3f000000', //statement (init start offset)
            'fc000000', //Offset (line number 4404)

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte

            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21

            '10000000', //nested call return result
            '01000000', //nested call return result

            '10000000', //nested string return result
            '02000000', //nested string return result

            '77000000', //GetEntity Call

            '10000000', //nested call return result
            '01000000', //nested call return result

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '06000000', //value 6

            '10000000', //nested call return result
            '01000000', //nested call return result

            'e0020000', //RadarPositionSetEntity Call




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