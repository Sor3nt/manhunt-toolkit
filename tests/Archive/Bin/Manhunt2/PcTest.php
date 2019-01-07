<?php
namespace App\Tests\Archive\Bin\Manhunt2;

use App\MHT;
use App\Tests\Archive\Archive;

class PcTest extends Archive
{

    public function test()
    {

        $testFolder = explode("/tests/", __DIR__)[0] . "/tests/Resources/Archive/Bin/Manhunt2/PC";
        $outputFolder = $testFolder . "/export";

        echo "\n* BIN: Testing Manhunt 2 PC (unpack/pack) ";
        $this->unPackPack(
            $testFolder . "/strmanim_pc.bin",
            $outputFolder . "/strmanim_pc#bin",
            'execution animations',
            MHT::GAME_MANHUNT_2,
            MHT::PLATFORM_PC
        );

        $this->assertEquals(
            md5(file_get_contents($testFolder . "/strmanim_pc.bin")),
            md5(file_get_contents($outputFolder . "/strmanim_pc.bin"))
        );

        $this->rrmdir($outputFolder);
    }

}