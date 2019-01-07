<?php
namespace App\Tests\Archive\Ifp\Manhunt1;

use App\MHT;
use App\Tests\Archive\Archive;

class PcTest extends Archive
{

    public function test()
    {
        echo "\n* IFP: Testing Manhunt 1 PC ==> ";


        $testFolder = explode("/tests/", __DIR__)[0] . "/tests/Resources/Archive/Ifp/Manhunt1/PC";
        $outputFolder = $testFolder . "/export";


        /*
         * Why the double unpack/pack?
         *
         * The Manhunt IFP deliver a "00 00 00 80" but translated to Little INT 32 is this a zero (0)
         * And when we convert back the zero to hex we got "00 00 00 00" (80 missed)
         */

        echo "\n* IFP: Testing Manhunt 1 PC (unpack/pack) ";
        $this->unPackPack(
            $testFolder . "/allanims.ifp",
            $outputFolder . "/allanims#ifp",
            'animations',
            MHT::GAME_MANHUNT,
            MHT::PLATFORM_PC
        );

        $this->unPackPack(
            $outputFolder . "/allanims.ifp",
            $outputFolder . "/export/allanims#ifp",
            'animations',
            MHT::GAME_MANHUNT,
            MHT::PLATFORM_PC
        );

        $this->assertEquals(
            md5(file_get_contents($outputFolder . "/allanims.ifp")),
            md5(file_get_contents($outputFolder . "/export/allanims.ifp"))
        );

        $this->rrmdir($outputFolder);



    }


}