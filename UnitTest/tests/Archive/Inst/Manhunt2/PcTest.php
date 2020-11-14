<?php
namespace App\Tests\Archive\Inst\Manhunt2;

use App\MHT;
use App\Tests\Archive\Archive;

class PcTest extends Archive
{

    public function test()
    {
        $testFolder = explode("/tests/", __DIR__)[0] . "/tests/Resources/Archive/Inst/Manhunt2/PC";
        $outputFolder = $testFolder . "/export";


        echo "\n* INST: Testing Manhunt 2 PC (unpack/pack) ==> ";
        $this->unPackPack(
            $testFolder . "/entity_pc.inst",
            $outputFolder . "/entity_pc#inst",
            'entity positions',
            MHT::GAME_MANHUNT_2,
            MHT::PLATFORM_PC
        );
        $this->assertEquals(
            md5(file_get_contents($testFolder . "/entity_pc.inst")),
            md5(file_get_contents($outputFolder . "/entity_pc.inst"))
        );

        $this->rrmdir($outputFolder);
    }



}