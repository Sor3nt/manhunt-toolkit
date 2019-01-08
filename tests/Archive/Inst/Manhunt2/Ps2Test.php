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

        /*
         * Why the double unpack/pack?
         *
         * The Manhunt (1/2) INST deliver a "00 00 00 80" but translated to Little INT 32 is this a zero (0)
         * And when we convert back the zero to hex we got "00 00 00 00" (80 missed)
         */
        echo "\n* INST: Testing Manhunt 2 PS2 (unpack/pack) ==> ";
        $this->unPackPack(
            $testFolder . "/ENTINST.BIN",
            $outputFolder . "/ENTINST.BIN.json",
            'entity positions',
            MHT::GAME_MANHUNT_2,
            MHT::PLATFORM_PS2
        );
        $this->unPackPack(
            $outputFolder . "/ENTINST.BIN",
            $outputFolder . "/export/ENTINST.BIN.json",
            'entity positions',
            MHT::GAME_MANHUNT_2,
            MHT::PLATFORM_PS2
        );

        $this->assertEquals(
            md5(file_get_contents($outputFolder . "/ENTINST.BIN")),
            md5(file_get_contents($outputFolder . "/export/ENTINST.BIN"))
        );

        $this->rrmdir($outputFolder);
    }
}