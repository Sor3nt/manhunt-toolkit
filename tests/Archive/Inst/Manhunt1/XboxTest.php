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

        echo "\n* INST: Testing Manhunt 1 XBOX (unpack/pack) ==> ";
        $this->unPackPack(
            $testFolder . "/entity.inst",
            $outputFolder . "/entity#inst",
            'entity positions',
            MHT::GAME_MANHUNT,
            MHT::PLATFORM_XBOX
        );

        $this->assertEquals(
            md5(file_get_contents($testFolder . "/entity.inst")),
            md5(file_get_contents($outputFolder . "/entity.inst"))
        );

        $this->rrmdir($outputFolder);
    }

}