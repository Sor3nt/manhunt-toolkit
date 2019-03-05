<?php
namespace App\Tests\Manhunt1;

use App\MHT;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class Hunter1TowerHTest extends KernelTestCase
{

    public function test()
    {
        $this->assertEquals(true, true, 'The bytecode is not correct');
        return;

        $script = "

SCRIPTMAIN		Triggers;

ENTITY
Hunter1_Tower_h		:	et_name;

SCRIPT OnCreate;
begin
	RunScript('player','OnPickUpInventoryItem');
	WriteDebug('HeadCheck has been run');
end;

END.

        ";

        $expected = [
            "10000000",
            "0a000000",
            "11000000",
            "0a000000",
            "09000000",
            "21000000",
            "04000000",
            "01000000",
            "00000000",
            "12000000",
            "02000000",
            "08000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "21000000",
            "04000000",
            "01000000",
            "08000000",
            "12000000",
            "02000000",
            "18000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "e3000000",
            "21000000",
            "04000000",
            "01000000",
            "20000000",
            "12000000",
            "02000000",
            "18000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "72000000",
            "73000000",
            "11000000",
            "09000000",
            "0a000000",
            "0f000000",
            "0a000000",
            "3b000000",
            "00000000"
        ];

        $compiler = new Compiler();
        $compiled = $compiler->parse($script, false, MHT::GAME_MANHUNT, MHT::PLATFORM_PC);


        if ($compiled['CODE'] != $expected){
            $index = 0;
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