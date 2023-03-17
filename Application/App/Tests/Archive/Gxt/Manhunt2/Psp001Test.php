<?php
namespace App\Tests\Archive\Gxt\Manhunt2;

use App\MHT;
use App\Tests\Archive\Archive;

class PspTest extends Archive
{

    public function test()
    {


        $testFolder = explode("/Tests/", __DIR__)[0] . "/Tests/Resources/Archive/Gxt/Manhunt2/PSP001";
        $outputFolder = $testFolder . "/export";

        echo "\n* GXT: Testing Manhunt 2 PC (unpack/pack) ==> ";

        /*
         * Why the double unpack/pack?
         *
         * The translation can contain unused text, MHT ignore these entries
         */


        $this->unPackPack(
            $testFolder . "/A01_ES.GXT",
            $outputFolder . "/A01_ES.GXT.json",
            'text translation',
            MHT::GAME_MANHUNT_2,
            MHT::PLATFORM_PSP_001
        );

        $this->unPackPack(
            $outputFolder . "/A01_ES.GXT",
            $outputFolder . "/export/A01_ES.GXT.json",
            'text translation',
            MHT::GAME_MANHUNT_2,
            MHT::PLATFORM_PSP_001
        );

        $this->assertEquals(
            md5(file_get_contents($outputFolder . "/A01_ES.GXT")),
            md5(file_get_contents($outputFolder . "/export/A01_ES.GXT"))
        );

        $this->rrmdir($outputFolder);


    }

}