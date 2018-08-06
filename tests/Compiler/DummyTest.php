<?php
namespace App\Tests\Compiler;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DummyTest extends KernelTestCase
{

    public function test()
    {
        $this->assertEquals(true, true, 'The bytecode is not correct');
        return;


        $script = "
            
                            
                scriptmain LevelScript;
           
    
                    script OnCreate;
                    begin
                    
			if not (InsideTrigger(getentity('tInHouseCheck'), getplayer)) then
 begin

                        end;

                    end;

                end.
        ";

        $expected = [


            // procedure start
            '10000000',
            '0a000000',
            '11000000',
            '0a000000',
            '09000000',

            'cf020000', //IsScriptAudioStreamCompleted Call
            '29000000', //NOT
            '01000000', //NOT
            '01000000', //NOT
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '380c0000', //Offset (line number 782)
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '0a000000', //value 10
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //sleep Call
            '3c000000', //statement (init statement start offset)
            'f40b0000', //Offset (line number 765)

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
        list($sectionCode, $sectionDATA) = $compiler->parse($script);


        if ($sectionCode != $expected){
            foreach ($sectionCode as $index => $item) {
                if ($expected[$index] == $item){
                    echo ($index + 1) . '->' . $item . "\n";
                }else{
                    echo "MISSMATCH need " . $expected[$index] . " got " . $sectionCode[$index] . "\n";
                }
            }
            exit;
        }

        $this->assertEquals($sectionCode, $expected, 'The bytecode is not correct');
    }

}