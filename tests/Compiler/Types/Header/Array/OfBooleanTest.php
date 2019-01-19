<?php
namespace App\Tests\CompilerByType\Header\Boolean\Assign;

use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class OfBooleanTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;

           
            var 	
                itemsSpawned : array[1..3] of boolean;
            
            
            script OnCreate;

                begin
                    itemsSpawned[1] := FALSE;
                    itemsSpawned[2] := FALSE;
                    itemsSpawned[3] := FALSE;

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
            '00000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result

            '12000000', //unknown
            '01000000', //unknown
            '01000000', //index count

            '34000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '12000000', //unknown
            '04000000', //unknown
            '04000000', //unknown
            '35000000', //unknown
            '04000000', //unknown
            '0f000000', //unknown
            '04000000', //unknown
            '31000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '10000000', //unknown
            '04000000', //unknown

            '12000000', //parameter (function return (bool?))
            '01000000', //parameter (function return (bool?))
            '00000000', //value 0

            '0f000000', //parameter (function return (bool?))
            '02000000', //parameter (function return (bool?))
            '17000000', //unknown
            '04000000', //unknown
            '02000000', //unknown
            '01000000', //unknown


            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //unknown
            '01000000', //unknown
            '02000000', //index count
            '34000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '12000000', //unknown
            '04000000', //unknown
            '04000000', //unknown
            '35000000', //unknown
            '04000000', //unknown
            '0f000000', //unknown
            '04000000', //unknown
            '31000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '10000000', //unknown
            '04000000', //unknown
            '12000000', //parameter (function return (bool?))
            '01000000', //parameter (function return (bool?))
            '00000000', //value 0
            '0f000000', //parameter (function return (bool?))
            '02000000', //parameter (function return (bool?))
            '17000000', //unknown
            '04000000', //unknown
            '02000000', //unknown
            '01000000', //unknown



            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //unknown
            '01000000', //unknown
            '03000000', //index count
            '34000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '12000000', //unknown
            '04000000', //unknown
            '04000000', //unknown
            '35000000', //unknown
            '04000000', //unknown
            '0f000000', //unknown
            '04000000', //unknown
            '31000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '10000000', //unknown
            '04000000', //unknown
            '12000000', //parameter (function return (bool?))
            '01000000', //parameter (function return (bool?))
            '00000000', //value 0
            '0f000000', //parameter (function return (bool?))
            '02000000', //parameter (function return (bool?))
            '17000000', //unknown
            '04000000', //unknown
            '02000000', //unknown
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