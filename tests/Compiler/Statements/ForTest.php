<?php
namespace App\Tests\Compiler\Statements;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ForTest extends KernelTestCase
{
//
    public function test() {

        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;

            script OnCreate;
                var 
                    HunterIndex : integer;
                    HunterName : string[30];
                    Success : boolean;
                    NrHuntersInSubpack : integer;

                begin

                    for HunterIndex := 1 to AINumberInSubpack('leader(leader)', 'subTruckGuards') do
                    begin
                        Success := AIReturnSubpackEntityName('leader(leader)', 'subTruckGuards', HunterIndex, HunterName);
                        if Success = TRUE then
                        begin
                            if IsEntityAlive(HunterName) then NrHuntersInSubpack := NrHuntersInSubpack + 1;			
                        end;
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
            '2c000000',


            '12000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '15000000', //unknown
            '04000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0f000000', //value 15
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0f000000', //value 15
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '67010000', //unknown
            '13000000', //unknown
            '02000000', //unknown
            '04000000', //unknown
            '04000000', //unknown
            '23000000', //unknown
            '01000000', //unknown
            '02000000', //unknown
            '41000000', //unknown
            'c4000000', //unknown
            '3c000000', //statement (init statement start offset)
            '80020000', //Offset (line number 406)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0f000000', //value 15
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0f000000', //value 15
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '04000000', //Offset
            '10000000', //nested call return result
            '01000000', //nested call return result
            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '22000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '1e000000', //value 30
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'ec010000', //aireturnsubpackentityname call
            '15000000', //unknown
            '04000000', //unknown
            '28000000', //unknown
            '01000000', //unknown
            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '28000000', //Offset
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (temp int)
            '01000000', //parameter (temp int)
            '01000000', //value 1
            '0f000000', //parameter (temp int)
            '04000000', //parameter (temp int)
            '23000000', //statement (core)
            '04000000', //statement (core)
            '01000000', //statement (core)
            '12000000', //statement (core)
            '01000000', //statement (core)
            '01000000', //statement (core)
            '3f000000', //statement (init start offset)
            'cc010000', //Offset (line number 361)
            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '6c020000', //Offset (line number 401)
            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '22000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '1e000000', //value 30
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'aa010000', //IsEntityAlive Call
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '6c020000', //Offset (line number 401)
            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '2c000000', //Offset
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (temp int)
            '01000000', //parameter (temp int)
            '01000000', //value 1
            '0f000000', //parameter (temp int)
            '04000000', //parameter (temp int)
            '31000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '15000000', //unknown
            '04000000', //unknown
            '2c000000', //nested call return result
            '01000000', //nested call return result
            '2f000000', //unknown
            '04000000', //unknown
            '10000000', //unknown
            '3c000000', //statement (init statement start offset)
            '3c000000', //Offset (line number 261)
            '30000000', //unknown
            '04000000', //unknown
            '10000000', //unknown

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
        $compiled = $compiler->parse($script, false);

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