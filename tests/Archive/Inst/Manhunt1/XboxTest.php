<?php
namespace App\Tests\Archive\Inst\Manhunt1;

use App\MHT;
use App\Tests\Archive\Archive;

class XboxTest extends Archive
{


    public function test()
    {
        $testFolder = explode("/tests/", __DIR__)[0] . "/tests/Resources/Archive/Inst/Manhunt1/XBOX";
        $outputFolder = $testFolder . "/export";

        /*
         * Why the double unpack/pack?
         *
         * The Manhunt (1/2) INST deliver a "00 00 00 80" but translated to Little INT 32 is this a zero (0)
         * And when we convert back the zero to hex we got "00 00 00 00" (80 missed)
         */
        echo "\n* INST: Testing Manhunt 1 XBOX (unpack/pack) ==> ";
        $this->unPackPack(
            $testFolder . "/entity.inst",
            $outputFolder . "/entity.inst.json",
            'entity positions',
            MHT::GAME_MANHUNT,
            MHT::PLATFORM_XBOX
        );

        $this->unPackPack(
            $outputFolder . "/entity.inst",
            $outputFolder . "/export/entity.inst.json",
            'entity positions',
            MHT::GAME_MANHUNT,
            MHT::PLATFORM_XBOX
        );

        $this->assertEquals(
            md5(file_get_contents($outputFolder . "/entity.inst")),
            md5(file_get_contents($outputFolder . "/export/entity.inst"))
        );

        $this->rrmdir($outputFolder);
    }

}