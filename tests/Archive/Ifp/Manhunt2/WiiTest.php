<?php
namespace App\Tests\Archive\Ifp\Manhunt2;

use App\Service\Archive\Bin;
use App\Service\Archive\Ifp;
use App\Service\Archive\Inst;
use App\Service\Archive\ZLib;
use App\Service\Resources;
use App\Tests\Archive\Archive;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Finder\Finder;

class WiiTest extends Archive
{

    public function test()
    {
        $testFolder = explode("/tests/", __DIR__)[0] . "/tests/Resources/Archive/Ifp/Manhunt2/Wii";
        $outputFolder = $testFolder . "/export";

        echo "\n* IFP: Testing Manhunt 2 Wii (unpack/pack) ";
        $this->unPackPack(
            $testFolder . "/allanims_wii.ifp",
            $outputFolder . "/allanims_wii#ifp",
            'animations',
            'mh2-wii'
        );

        $this->assertEquals(
            md5(ZLib::uncompress(file_get_contents($testFolder . "/allanims_wii.ifp"))),
            md5(file_get_contents($outputFolder . "/allanims_wii.ifp"))
        );

        $this->rrmdir($outputFolder);
    }


}