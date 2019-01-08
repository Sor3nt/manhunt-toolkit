<?php
namespace App\Tests\Archive\Ifp\Manhunt1;

use App\MHT;
use App\Tests\Archive\Archive;

class XboxTest extends Archive
{


    public function test()
    {
        $testFolder = explode("/tests/", __DIR__)[0] . "/tests/Resources/Archive/Ifp/Manhunt1/XBOX";
        $outputFolder = $testFolder . "/export";

        /*
         * Why the double unpack/pack?
         *
         * The Manhunt IFP deliver a "00 00 00 80" but translated to Little INT 32 is this a zero (0)
         * And when we convert back the zero to hex we got "00 00 00 00" (80 missed)
         */

        echo "\n* IFP: Testing Manhunt 1 XBOX (unpack/pack) ==> ";
        $this->unPackPack(
            $testFolder . "/AllAnims.ifp",
            $outputFolder . "/AllAnims#ifp",
            'animations',
            MHT::GAME_MANHUNT,
            MHT::PLATFORM_PS2
        );
        $this->unPackPack(
            $outputFolder . "/AllAnims.ifp",
            $outputFolder . "/export/AllAnims#ifp",
            'animations',
            MHT::GAME_MANHUNT,
            MHT::PLATFORM_PS2
        );

        $this->assertEquals(
            md5(file_get_contents($outputFolder . "/AllAnims.ifp")),
            md5(file_get_contents($outputFolder . "/export/AllAnims.ifp"))
        );

        $this->rrmdir($outputFolder);

    }

}