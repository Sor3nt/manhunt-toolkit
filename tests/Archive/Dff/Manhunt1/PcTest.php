<?php
namespace App\Tests\Archive\Dff\Manhunt1;

use App\MHT;
use App\Tests\Archive\Archive;

class PcTest extends Archive
{

    public function test()
    {
        $testFolder = explode("/tests/", __DIR__)[0] . "/tests/Resources/Archive/Dff/Manhunt1/PC";
        $outputFolder = $testFolder . "/export";

        /*
         * Why the double unpack/pack?
         *
         * We did not save the original order of the entries
         * So the comparison would fail because of wrong entry orders.
         */

        echo "\n* DFF: Testing Manhunt 1 PC (unpack/pack) ";
        $this->unPackPack(
            $testFolder . "/modelspc.dff",
            $outputFolder . "/modelspc#dff",
            '3d models',
            MHT::GAME_MANHUNT,
            MHT::PLATFORM_PC
        );

        $this->unPackPack(
            $testFolder . "/export/modelspc.dff",
            $outputFolder . "/export/modelspc#dff",
            '3d models',
            MHT::GAME_MANHUNT,
            MHT::PLATFORM_PC
        );

        $this->assertEquals(
            md5(file_get_contents($outputFolder . "/modelspc.dff")),
            md5(file_get_contents($outputFolder . "/export/modelspc.dff"))
        );

        $this->rrmdir($outputFolder);
    }



}