<?php
namespace App\Tests\Archive\Ifp\Manhunt2;

use App\MHT;
use App\Service\Archive\ZLib;
use App\Tests\Archive\Archive;

class PcTest extends Archive
{

    public function test()
    {
        $testFolder = explode("/tests/", __DIR__)[0] . "/tests/Resources/Archive/Ifp/Manhunt2/PC";
        $outputFolder = $testFolder . "/export";

        echo "\n* IFP: Testing Manhunt 2 PC (unpack/pack) ==> ";
        $this->unPackPack(
            $testFolder . "/allanims_pc.ifp",
            $outputFolder . "/allanims_pc#ifp",
            'animations',
            MHT::GAME_MANHUNT_2,
            MHT::PLATFORM_PC
        );

        $this->assertEquals(
            md5(ZLib::uncompress(file_get_contents($testFolder . "/allanims_pc.ifp"))),
            md5(file_get_contents($outputFolder . "/allanims_pc.ifp"))
        );

        $this->rrmdir($outputFolder);

    }

}