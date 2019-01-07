<?php
namespace App\Tests\Archive\Dff\Manhunt1;

use App\MHT;
use App\Tests\Archive\Archive;

class Ps2Test extends Archive
{

    public function test()
    {
        $testFolder = explode("/tests/", __DIR__)[0] . "/tests/Resources/Archive/Dff/Manhunt1/PS2";
        $outputFolder = $testFolder . "/export";

        /*
         * Why the double unpack/pack?
         *
         * We did not save the original order of the entries
         * So the comparison would fail because of wrong entry orders.
         */

        echo "\n* DFF: Testing Manhunt 1 PS2 (unpack/pack) ";
        $this->unPackPack(
            $testFolder . "/MODELS.DFF",
            $outputFolder . "/MODELS#DFF",
            '3d models',
            MHT::GAME_MANHUNT,
            MHT::PLATFORM_PS2
        );

        $this->unPackPack(
            $testFolder . "/export/MODELS.DFF",
            $outputFolder . "/export/MODELS#DFF",
            '3d models',
            MHT::GAME_MANHUNT,
            MHT::PLATFORM_PS2
        );

        $this->assertEquals(
            md5(file_get_contents($outputFolder . "/MODELS.DFF")),
            md5(file_get_contents($outputFolder . "/export/MODELS.DFF"))
        );

        $this->rrmdir($outputFolder);
    }



}
