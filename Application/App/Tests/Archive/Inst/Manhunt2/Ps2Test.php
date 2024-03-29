<?php
namespace App\Tests\Archive\Inst\Manhunt2;

use App\MHT;
use App\Tests\Archive\Archive;

class Ps2Test extends Archive
{

    public function test()
    {
        $testFolder = explode("/tests/", __DIR__)[0] . "/tests/Resources/Archive/Inst/Manhunt2/PS2";
        $outputFolder = $testFolder . "/export";

        echo "\n* INST: Testing Manhunt 2 PS2 (unpack/pack) ==> ";
        $this->unPackPack(
            $testFolder . "/ENTINST.BIN",
            $outputFolder . "/ENTINST#BIN",
            'entity positions',
            MHT::GAME_MANHUNT_2,
            MHT::PLATFORM_PS2
        );


        $this->assertEquals(
            md5(file_get_contents($testFolder . "/ENTINST.BIN")),
            md5(file_get_contents($outputFolder . "/ENTINST.BIN"))
        );

        $this->rrmdir($outputFolder);
    }
}