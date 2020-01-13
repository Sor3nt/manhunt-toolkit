<?php
namespace App\Tests\CompilerV2\Switches;

use App\MHT;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SwitchIntegerOfIntegerDefaultTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;
                
            var
                iHunterModel : integer;
                
            script OnCreate;
                VAR
                	strModelType : string[16];
                begin
                    case iHunterModel of
                        0: strModelType := 'blo_bodB';
                        1: strModelType := 'blo_bodD';
                        2: strModelType := 'blo_bodE';
                        else: strModelType := 'blo_bodB';
                    end;

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


            '34000000', //reserve bytes
            '09000000', //reserve bytes
            '14000000', //Offset in byte



            '14000000', //Read VAR from header
            '01000000', //Read VAR from header
            '04000000', //Read VAR from header
            '24000000', //iHunterModel

            '24000000', //Case 2
            '01000000', //Case 2
            '02000000', //Case 2

            '3f000000', //statement (init start offset)
            '7c000000', //Offset (line number 646)



            '24000000', //Case 1
            '01000000', //Case 1
            '01000000', //Case 1

            '3f000000', //statement (init start offset)
            'e0000000', //Offset (line number 671)



            '24000000', //Case 0
            '01000000', //Case 0
            '00000000', //Case 0

            '3f000000', //statement (init start offset)
            '44010000', //Offset (line number 696)



            '3c000000', //statement (init statement start offset)
            'a8010000', //Offset (ELSE line number)

            '3c000000', //statement (init statement start offset)
            '0c020000', //Offset (LAST line number)

            //CASE 2
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '18000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '09000000', //value 9
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '22000000', //unknown
            '04000000', //unknown
            '04000000', //unknown
            '10000000', //unknown
            '12000000', //parameter (read string array? assign?)
            '03000000', //parameter (read string array? assign?)
            '10000000', //value 16
            '10000000', //parameter (read string array? assign?)
            '04000000', //parameter (read string array? assign?)
            '10000000', //unknown
            '03000000', //unknown
            '48000000', //unknown

            '3c000000', //statement (init statement start offset)
            '0c020000', //Offset (line number 746)

            //CASE 1
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '0c000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '09000000', //value 9
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '22000000', //unknown
            '04000000', //unknown
            '04000000', //unknown
            '10000000', //unknown
            '12000000', //parameter (read string array? assign?)
            '03000000', //parameter (read string array? assign?)
            '10000000', //value 16
            '10000000', //parameter (read string array? assign?)
            '04000000', //parameter (read string array? assign?)
            '10000000', //unknown
            '03000000', //unknown
            '48000000', //unknown

            '3c000000', //statement (init statement start offset)
            '0c020000', //Offset (line number 746)

            //CASE 0
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset blo_bodB
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '09000000', //value 9
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '22000000', //unknown
            '04000000', //unknown
            '04000000', //unknown
            '10000000', //unknown
            '12000000', //parameter (read string array? assign?)
            '03000000', //parameter (read string array? assign?)
            '10000000', //value 16
            '10000000', //parameter (read string array? assign?)
            '04000000', //parameter (read string array? assign?)
            '10000000', //unknown
            '03000000', //unknown
            '48000000', //unknown

            '3c000000', //statement (init statement start offset)
            '0c020000', //Offset (line number 746)


            //ELSE CASE
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset blo_bodB
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '09000000', //value 9
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '22000000', //unknown
            '04000000', //unknown
            '04000000', //unknown
            '10000000', //unknown
            '12000000', //parameter (read string array? assign?)
            '03000000', //parameter (read string array? assign?)
            '10000000', //value 16
            '10000000', //parameter (read string array? assign?)
            '04000000', //parameter (read string array? assign?)
            '10000000', //unknown
            '03000000', //unknown
            '48000000', //unknown

            '3c000000', //statement (init statement start offset)
            '0c020000', //Offset (line number 746)


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