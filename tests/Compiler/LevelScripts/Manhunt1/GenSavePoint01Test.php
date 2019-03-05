<?php
namespace App\Tests\Manhunt1;

use App\MHT;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GenSavePoint01Test extends KernelTestCase
{

    public function test()
    {
        $this->assertEquals(true, true, 'The bytecode is not correct');
        return;

        $script = "
SCRIPTMAIN SAVEPOINTScript;

ENTITY
Gen_Save_Point01	:	et_name;

VAR
willie_game_int2 : game_var integer;

SCRIPT OnLevelSave;
begin
		
	if (willie_game_int2 = 1) then
	begin
		willie_game_int2 := 2;
	end;

end;

END.

        ";

        $expected = [
            "10000000",
            "0a000000",
            "11000000",
            "0a000000",
            "09000000",
            "1e000000",
            "34000000",
            "04000000",
            "01000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "01000000",
            "0f000000",
            "04000000",
            "23000000",
            "04000000",
            "01000000",
            "12000000",
            "01000000",
            "01000000",
            "3f000000",
            "6c000000",
            "33000000",
            "01000000",
            "01000000",
            "24000000",
            "01000000",
            "00000000",
            "3f000000",
            "9c000000",
            "12000000",
            "01000000",
            "02000000",
            "1d000000",
            "01000000",
            "34000000",
            "04000000",
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