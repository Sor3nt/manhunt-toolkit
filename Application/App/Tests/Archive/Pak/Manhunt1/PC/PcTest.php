<?php
namespace App\Tests\Archive\Pak\Manhunt1;

use App\MHT;
use App\Tests\Archive\Archive;

class PcTest extends Archive
{

    public function testLevelScript()
    {

        $testFolder = explode("/Tests/", __DIR__)[0] . "/Tests/Resources/Archive/Pak/Manhunt1/PC";
        $outputFolder = $testFolder . "/export";


        /*
         * Why the double unpack/pack?
         *
         * We did not save the original order of the entries
         * So the comparison would fail because of wrong entry orders.
         */

        echo "\n* PAK: Testing Manhunt 1 PC (unpack/pack) ==> ";
        $this->unPackPack(
            $testFolder . "/ManHunt.pak",
            $outputFolder . "/ManHunt#pak",
            'manhunt data container',
            MHT::GAME_MANHUNT,
            MHT::PLATFORM_PC
        );

        $this->unPackPack(
            $outputFolder . "/ManHunt.pak",
            $outputFolder . "/export/ManHunt#pak",
            'manhunt data container',
            MHT::GAME_MANHUNT,
            MHT::PLATFORM_PC
        );

        $this->assertEquals(
            md5(file_get_contents($outputFolder . "/ManHunt.pak")),
            md5(file_get_contents($outputFolder . "/export/ManHunt.pak"))
        );

        $this->rrmdir( $outputFolder );
    }
}