<?php
namespace App\Tests\CompilerByType\Script\RgbaInt\Assign;

use App\MHT;
use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AssignGreenTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;

            script OnCreate;
                var
                	rgbaStart	:	RGBAInt;
                begin
                	rgbaStart.green	:=0;
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



            
            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '04000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '0f000000', //unknown
            '01000000', //unknown
            '32000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (function return (bool?))
            '01000000', //parameter (function return (bool?))
            '00000000', //value 0
            '0f000000', //parameter (function return (bool?))
            '02000000', //parameter (function return (bool?))
            '17000000', //unknown
            '01000000', //unknown
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
        $compiled = $compiler->parse($script, false, MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC);
//
//        foreach ($expected as $item) {
//            echo $item . "\n";
//        }
//        foreach ($compiled['CODE'] as $item) {
//            echo $item->hex . "\n";
//        }

//var_dump($expected);
//exit;
        if ($compiled['CODE'] != $expected){
            $index = 0;
            foreach ($compiled['CODE'] as $index => $item) {
                if (!isset($expected[$index])){
                    echo "TO MUCH, got " . $compiled['CODE'][$index] . " " . $compiled['CODE'][$index]->debug . "\n";

                }else{
                    if ($expected[$index] == $item){
                        echo ($index + 1) . '->' . $item . " " . $item->debug . "\n";
                    }else{
                        echo "MISSMATCH need " . $expected[$index] . " got " . $compiled['CODE'][$index] . " " . $compiled['CODE'][$index]->debug . "\n";
                    }

                }
            }

            exit;
        }
        $this->assertEquals($compiled['CODE'], $expected, 'The bytecode is not correct');
    }

}