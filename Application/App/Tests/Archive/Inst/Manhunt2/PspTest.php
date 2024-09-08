<?php
namespace App\Tests\Archive\Inst\Manhunt2;

use App\MHT;
use App\Tests\Archive\Archive;

class PspTest extends Archive
{

    public function test()
    {
        $testFolder = explode("/Tests/", __DIR__)[0] . "/Tests/Resources/Archive/Inst/Manhunt2/PSP";
        $outputFolder = $testFolder . "/export";

        echo "\n* INST: Testing Manhunt 2 PSP (unpack/pack) ==> ";
        $this->unPackPack(
            $testFolder . "/ENTINST.BIN",
            $outputFolder . "/ENTINST#BIN",
            'entity positions',
            MHT::GAME_MANHUNT_2,
            MHT::PLATFORM_PSP
        );

        $this->assertEquals(
            md5(file_get_contents($testFolder . "/ENTINST.BIN")),
            md5(file_get_contents($outputFolder . "/ENTINST.BIN"))
        );

        $this->rrmdir($outputFolder);
    }


}