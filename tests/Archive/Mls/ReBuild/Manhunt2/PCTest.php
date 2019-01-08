<?php
namespace App\Tests\Archive\Mls\ReBuild\Manhunt2;

use App\MHT;
use App\Tests\Archive\Archive;

class PCTest extends Archive
{

    public function testLevelScript()
    {

        $testFolder = explode("/tests/", __DIR__)[0] . "/tests/Resources/Archive/Mls/Manhunt2/PC";
        $outputFolder = $testFolder . "/export";

        echo "\n* MLS: Testing Manhunt 2 PC (unpack/pack) ==> ";
        $this->unPackPack(
            $testFolder . "/A01_Escape_Asylum.mls",
            $outputFolder . "/A01_Escape_Asylum#mls",
            'levelscript',
            MHT::GAME_MANHUNT_2,
            MHT::PLATFORM_PC
        );

        $this->unPackPack(
            $outputFolder . "/A01_Escape_Asylum.mls",
            $outputFolder . "/export/A01_Escape_Asylum#mls",
            'levelscript',
            MHT::GAME_MANHUNT_2,
            MHT::PLATFORM_PC
        );

        $this->assertEquals(
            md5(file_get_contents($outputFolder . "/A01_Escape_Asylum.mls")),
            md5(file_get_contents($outputFolder . "/export/A01_Escape_Asylum.mls"))
        );

        $this->rrmdir( $outputFolder );
    }

}