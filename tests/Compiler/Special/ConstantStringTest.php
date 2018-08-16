<?php
namespace App\Tests\Special;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ConstantStringTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;

            const
                playerName = 'player(player)';

            script OnCreate;
                begin

                	AIDefineGoalHuntEnemy('goalHuntPlayer1', playerName, true, 4);

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
'10000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value
'10000000', //parameter (Read String var)
'01000000', //parameter (Read String var)
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'00000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value
'10000000', //parameter (Read String var)
'01000000', //parameter (Read String var)
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //Bool true / int 1
'10000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'04000000', //value
'10000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'58010000', //aidefinegoalhuntenemy Call

            // script end
            '11000000',
            '09000000',
            '0a000000',
            '0f000000',
            '0a000000',
            '3b000000',
            '00000000',

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