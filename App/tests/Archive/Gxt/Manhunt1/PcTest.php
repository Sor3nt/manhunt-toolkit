<?php
namespace App\Tests\Archive\Gxt\Manhunt1;

use App\MHT;
use App\Tests\Archive\Archive;

class PcTest extends Archive
{

    public function test()
    {


        $testFolder = explode("/tests/", __DIR__)[0] . "/tests/Resources/Archive/Gxt/Manhunt1/PC";
        $outputFolder = $testFolder . "/export";

        echo "\n* GXT: Testing Manhunt 1 PC (unpack/pack) ==> ";

        /*
         * Why the double unpack/pack?
         *
         * The translation can contain unused text, MHT ignore these entries
         */

        $this->unPackPack(
            $testFolder . "/pc_asylum.gxt",
            $outputFolder . "/pc_asylum.gxt.json",
            'text translation',
            MHT::GAME_MANHUNT,
            MHT::PLATFORM_PC
        );

        $this->unPackPack(
            $outputFolder . "/pc_asylum.gxt",
            $outputFolder . "/export/pc_asylum.gxt.json",
            'text translation',
            MHT::GAME_MANHUNT,
            MHT::PLATFORM_PC
        );

        $this->assertEquals(
            md5(file_get_contents($outputFolder . "/pc_asylum.gxt")),
            md5(file_get_contents($outputFolder . "/export/pc_asylum.gxt"))
        );

        $this->rrmdir($outputFolder);

    }

}