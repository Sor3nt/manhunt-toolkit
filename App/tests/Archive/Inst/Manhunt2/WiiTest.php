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

        echo "\n* INST: Testing Manhunt 2 WII (unpack/pack) ==> ";
        $this->unPackPack(
            $testFolder . "/entity_wii.inst",
            $outputFolder . "/entity_wii#inst",
            'entity positions',
            MHT::GAME_MANHUNT_2,
            MHT::PLATFORM_WII
        );

        $this->assertEquals(
            md5(file_get_contents($testFolder . "/entity_wii.inst")),
            md5(file_get_contents($outputFolder . "/entity_wii.inst"))
        );

        $this->rrmdir($outputFolder);
    }


}