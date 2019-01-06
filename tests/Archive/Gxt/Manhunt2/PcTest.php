<?php
namespace App\Tests\Archive\Gxt\Manhunt2;

use App\Tests\Archive\Archive;

class PcTest extends Archive
{

    public function test()
    {


        $testFolder = explode("/tests/", __DIR__)[0] . "/tests/Resources/Archive/Gxt/Manhunt2/PC";
        $outputFolder = $testFolder . "/export";

        echo "\n* GXT: Testing Manhunt 2 PC (unpack/pack) ";

        /*
         * Why the double unpack/pack?
         *
         * The translation can contain unused text, MHT ignore these entries
         */


        $this->unPackPack(
            $testFolder . "/A01_Escape_Asylum.gxt",
            $outputFolder . "/A01_Escape_Asylum.gxt.json",
            'text translation'
        );

        $this->unPackPack(
            $outputFolder . "/A01_Escape_Asylum.gxt",
            $outputFolder . "/export/A01_Escape_Asylum.gxt.json",
            'text translation'
        );

        $this->assertEquals(
            md5(file_get_contents($outputFolder . "/A01_Escape_Asylum.gxt")),
            md5(file_get_contents($outputFolder . "/export/A01_Escape_Asylum.gxt"))
        );

        $this->rrmdir($outputFolder);


    }

}