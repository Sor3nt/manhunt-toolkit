<?php
namespace App\Tests\Archive\Ifp\Manhunt1;

use App\MHT;
use App\Tests\Archive\Archive;

class Ps2Test extends Archive
{


    public function test()
    {
        $testFolder = explode("/tests/", __DIR__)[0] . "/tests/Resources/Archive/Ifp/Manhunt1/PS2";
        $outputFolder = $testFolder . "/export";

        /*
         * Why the double unpack/pack?
         *
         * The Manhunt IFP deliver a "00 00 00 80" but translated to Little INT 32 is this a zero (0)
         * And when we convert back the zero to hex we got "00 00 00 00" (80 missed)
         */
        echo "\n* IFP: Testing Manhunt 1 PS2 (unpack/pack) ";
        $this->unPackPack(
            $testFolder . "/ALLANIMS.IFP",
            $outputFolder . "/ALLANIMS#IFP",
            'animations',
            MHT::GAME_MANHUNT,
            MHT::PLATFORM_PS2
        );
        $this->unPackPack(
            $outputFolder . "/ALLANIMS.IFP",
            $outputFolder . "/export/ALLANIMS#IFP",
            'animations',
            MHT::GAME_MANHUNT,
            MHT::PLATFORM_PS2
        );

        $this->assertEquals(
            md5(file_get_contents($outputFolder . "/ALLANIMS.IFP")),
            md5(file_get_contents($outputFolder . "/export/ALLANIMS.IFP"))
        );

        $this->rrmdir($outputFolder);

    }


}