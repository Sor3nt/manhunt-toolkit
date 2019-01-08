<?php
namespace App\Tests\Archive\Inst\Manhunt2;

use App\MHT;
use App\Tests\Archive\Archive;

class WiiTest extends Archive
{

    public function test()
    {
        $testFolder = explode("/tests/", __DIR__)[0] . "/tests/Resources/Archive/Inst/Manhunt2/Wii";
        $outputFolder = $testFolder . "/export";

        /*
         * Why the double unpack/pack?
         *
         * The Manhunt (1/2) INST deliver a "00 00 00 80" but translated to Little INT 32 is this a zero (0)
         * And when we convert back the zero to hex we got "00 00 00 00" (80 missed)
         */
        echo "\n* INST: Testing Manhunt 2 WII (unpack/pack) ==> ";
        $this->unPackPack(
            $testFolder . "/entity_wii.inst",
            $outputFolder . "/entity_wii.inst.json",
            'entity positions',
            MHT::GAME_MANHUNT_2,
            MHT::PLATFORM_WII
        );

        $this->unPackPack(
            $outputFolder . "/entity_wii.inst",
            $outputFolder . "/export/entity_wii.inst.json",
            'entity positions',
            MHT::GAME_MANHUNT_2,
            MHT::PLATFORM_WII
        );

        $this->assertEquals(
            md5(file_get_contents($outputFolder . "/entity_wii.inst")),
            md5(file_get_contents($outputFolder . "/export/entity_wii.inst"))
        );

        $this->rrmdir($outputFolder);
    }


}