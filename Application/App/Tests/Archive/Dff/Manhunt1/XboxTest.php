<?php
namespace App\Tests\Archive\Dff\Manhunt1;

use App\MHT;
use App\Tests\Archive\Archive;

class XboxTest extends Archive
{

    public function test()
    {
        $testFolder = explode("/Tests/", __DIR__)[0] . "/Tests/Resources/Archive/Dff/Manhunt1/XBOX";
        $outputFolder = $testFolder . "/export";

        /*
         * Why the double unpack/pack?
         *
         * We did not save the original order of the entries
         * So the comparison would fail because of wrong entry orders.
         */

        echo "\n* DFF: Testing Manhunt 1 XBOX (unpack/pack) ==> ";
        $this->unPackPack(
            $testFolder . "/modelsXBOX.dff",
            $outputFolder . "/modelsXBOX#dff",
            '3d models',
            MHT::GAME_MANHUNT,
            MHT::PLATFORM_XBOX
        );

        $this->unPackPack(
            $testFolder . "/export/modelsXBOX.dff",
            $outputFolder . "/export/modelsXBOX#dff",
            '3d models',
            MHT::GAME_MANHUNT,
            MHT::PLATFORM_XBOX
        );

        $this->assertEquals(
            md5(file_get_contents($outputFolder . "/modelsXBOX.dff")),
            md5(file_get_contents($outputFolder . "/export/modelsXBOX.dff"))
        );

        $this->rrmdir($outputFolder);
    }



}
