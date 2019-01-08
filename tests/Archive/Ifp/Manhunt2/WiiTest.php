<?php
namespace App\Tests\Archive\Ifp\Manhunt2;

use App\MHT;
use App\Service\Archive\ZLib;
use App\Tests\Archive\Archive;

class WiiTest extends Archive
{

    public function test()
    {
        $testFolder = explode("/tests/", __DIR__)[0] . "/tests/Resources/Archive/Ifp/Manhunt2/Wii";
        $outputFolder = $testFolder . "/export";

        echo "\n* IFP: Testing Manhunt 2 Wii (unpack/pack) ==> ";
        $this->unPackPack(
            $testFolder . "/allanims_wii.ifp",
            $outputFolder . "/allanims_wii#ifp",
            'animations',
            MHT::GAME_MANHUNT_2,
            MHT::PLATFORM_WII
        );

        $this->assertEquals(
            md5(ZLib::uncompress(file_get_contents($testFolder . "/allanims_wii.ifp"))),
            md5(file_get_contents($outputFolder . "/allanims_wii.ifp"))
        );

        $this->rrmdir($outputFolder);
    }


}