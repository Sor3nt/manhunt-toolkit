<?php
namespace App\Tests\CompilerByType\Script\Vec3D\Statements;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class WhileZTest extends KernelTestCase
{
//
    public function test() {
//$this->assertEquals(true, true, 'The bytecode is not correct');
//return;

        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;

            script OnCreate;
                var
                    pos : Vec3D;

                begin
                    pos := GetEntityPosition(GetEntity('real_asylum_elev'));
                    while pos.z < 20.0 do
                    begin
                        pos := GetEntityPosition(GetEntity('real_asylum_elev'));
                        sleep(10);
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

            '34000000',
            '09000000',
            '0c000000',


            '22000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '0c000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result


            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '11000000', //value 17
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '77000000', //getentity Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '78000000', //GetEntityPosition Call
            '12000000', //unknown
            '03000000', //unknown
            '0c000000', //unknown

            '0f000000', //unknown
            '01000000', //unknown
            '0f000000', //unknown
            '04000000', //unknown
            '44000000', //unknown



            '22000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '0c000000', //unknown

            '10000000', //nested call return result
            '01000000', //nested call return result

            '0f000000', //unknown
            '01000000', //unknown

            '32000000', //unknown
            '01000000', //unknown
            '08000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '0f000000', //unknown
            '02000000', //unknown
            '18000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '02000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '0000a041', //value 1101004800
            '10000000', //nested call return result
            '01000000', //nested call return result
            '4e000000', //unknown
            '12000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '3d000000', //unknown
            '20010000', //unknown
            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            'c8010000', //Offset (line number 329)

            '22000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '0c000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result


            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '11000000', //value 17
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '77000000', //getentity Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '78000000', //GetEntityPosition Call
            '12000000', //unknown
            '03000000', //unknown
            '0c000000', //unknown

            '0f000000', //unknown
            '01000000', //unknown
            '0f000000', //unknown
            '04000000', //unknown
            '44000000', //unknown


            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '0a000000', //value 10
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //sleep Call
            '3c000000', //statement (init statement start offset)
            '94000000', //Offset (line number 252)

            // script end
            '11000000',
            '09000000',
            '0a000000',
            '0f000000',
            '0a000000',
            '3b000000',
            '00000000'
        ];

        $compiler = new Compiler();
        $compiled = $compiler->parse($script);

        if ($compiled['CODE'] != $expected){
            foreach ($compiled['CODE'] as $index => $item) {
                if ($expected[$index] == $item){
                    echo ($index + 1) . '->' . $item . "\n";
                }else{
                    echo "MISSMATCH need " . $expected[$index] . " got " . $compiled['CODE'][$index] . "\n";
                }
            }
            exit;
        }

        $this->assertEquals($compiled['CODE'], $expected, 'The bytecode is not correct');
    }


}