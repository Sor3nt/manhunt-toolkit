<?php
namespace App\Tests\Archive\Mls\Build\Manhunt2;

use App\MHT;
use App\Service\Archive\Mls\Build;
use App\Service\Archive\ZLib;
use App\Service\Compiler\Compiler;
use App\Service\Resources;
use App\Tests\Archive\Archive;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PCTest extends Archive
{

    public function testLevelScript()
    {


        $testFolder = explode("/tests/", __DIR__)[0] . "/tests/Resources/Archive/Mls/Manhunt2/PC";
        $outputFolder = $testFolder . "/export";


        echo "\n* MLS: Testing Manhunt 2 PC (unpack/pack) ";
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

        return;


        exit;


        echo "\n* MLS: Testing Manhunt 2 PC (build) ==> ";

        $resources = new Resources();
        $resources->workDirectory = explode("/tests/", __DIR__)[0] . "/tests/Resources";

        $mhls = $resources->load('/Archive/Mls/Manhunt2/PC/A01_Escape_Asylum.mls')->getContent();

        $builder = new Build();
        $binary = $builder->build($mhls);

        $this->assertEquals(
            md5($binary),
            '4ab60cd039ae6cbf7b2b55e4145a57fa'
        );

        file_put_contents('/Users/matthias/mh2/levels/A01_Escape_Asylum/A01_Escape_Asylum.mls', $binary);
    }

}