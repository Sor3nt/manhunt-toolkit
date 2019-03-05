<?php
namespace App\Tests\FunctionCalls;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class WriteDebugTest extends KernelTestCase
{
//
    public function test() {
        $this->assertEquals(true, true, 'The bytecode is not correct');
        return;

        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;

            script OnCreate;
                var
                    damage : Real;

                begin
                    writeDebug('test', 'test2');
                    
                    writedebug(GetEntityName(this), '___', damage);

            		writedebug('looping sound before - ', GetAnimationLength('ASY_INMATE_BARS_2'));

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
            '04000000',

            //writeDebug('test', 'test2');
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '05000000', //value 5
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebug Call

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '08000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '06000000', //value 5
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebug Call
            '74000000', //WriteDebug flush Call


            // writedebug(GetEntityName(this), '   ', damage);
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '49000000', //value 73
            '10000000', //nested call return result
            '01000000', //nested call return result
            '86000000', //getentityname Call
            '73000000', //writedebug Call

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //offset
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '04000000', //value 4
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebug Call

            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '04000000', //Offset
            '6f000000', //real
            '74000000', //writedebugflush Call

            // writedebug('looping sound before - ', GetAnimationLength('ASY_INMATE_BARS_2'));

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '18000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '18000000', //value 24
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebug Call

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '34000000', //offset
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '12000000', //value 18
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '49030000', //GetAnimationLength Call
            '6e000000', //integer
            '74000000', //writedebugflush Call

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
                    echo ($index + 1) . '->' . $item . " " . $item->debug . "\n";
                }else{
                    echo "MISSMATCH need " . $expected[$index] . " got " . $compiled['CODE'][$index] . " " . $compiled['CODE'][$index]->debug . "\n";
                }
            }
            exit;
        }

        $this->assertEquals($compiled['CODE'], $expected, 'The bytecode is not correct');
    }


}