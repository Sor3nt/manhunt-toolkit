<?php
namespace App\Tests\Script;

use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ArgumentsTest extends KernelTestCase
{

    public function test()
    {

//        return true;

        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;

            var 
                state : integer;
            

            script SetButtonState; 
                arg 
                    i : integer; 
            begin 
                state := i; 
            end;
            
            
            end.
        ";

        $expected = [
            '10000000', //Script start block
            '0a000000', //Script start block
            '11000000', //Script start block
            '0a000000', //Script start block
            '09000000', //Script start block


            '34000000', //reserve script var bytes
            '09000000', //reserve script var bytes
            '04000000', //Offset in byte




            '10030000', // initialize argument reading (?) fixed value
            '24000000', // initialize argument reading (?) fixed value
            '01000000', // initialize argument reading (?) fixed value
            '00000000', // initialize argument reading (?) fixed value
            '3f000000', // initialize argument reading (?) fixed value
            '15000000', //Offset (line number 521)

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '00000000', //value 0 (argument 0 ?)
            '10000000', //nested call return result
            '01000000', //nested call return result

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '00000000', //value 0 (no fallback ?)
            '10000000', //nested call return result
            '01000000', //nested call return result

            '0a030000', //fixed value

            '15000000', //unknown
            '04000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '0f030000', // fixed valued (arg read done?)  ( offset von oben pointed hier her ? )


            //assign state :=
            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '04000000', //Offset (script var i)

            '16000000', //assign to header
            '04000000', //assign to header
            '00000000', //offset from header var state
            '01000000', //assign to header


            '11000000', //Script end block
            '09000000', //Script end block
            '0a000000', //Script end block
            '0f000000', //Script end block
            '0a000000', //Script end block
            '3b000000', //Script end block
            '00000000', //Script end block

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