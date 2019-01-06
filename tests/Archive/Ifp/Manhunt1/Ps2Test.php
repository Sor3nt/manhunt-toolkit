<?php
namespace App\Tests\Archive\Ifp\Manhunt1;

use App\Service\Archive\Bin;
use App\Service\Archive\Ifp;
use App\Service\Archive\Inst;
use App\Service\Resources;
use App\Tests\Archive\Archive;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Finder\Finder;

class Ps2Test extends Archive
{


    public function test()
    {
        $testFolder = explode("/tests/", __DIR__)[0] . "/tests/Resources/Archive/Ifp/Manhunt1/PS2";
        $outputFolder = $testFolder . "/export";

        echo "\n* IFP: Testing Manhunt 1 PS2 (unpack/pack) ";
        $this->unPackPack(
            $testFolder . "/ALLANIMS.IFP",
            $outputFolder . "/ALLANIMS#IFP",
            'animations',
            'mh1'
        );

        $this->assertEquals(
            md5(file_get_contents($testFolder . "/ALLANIMS.IFP")),
            md5(file_get_contents($outputFolder . "/ALLANIMS.IFP"))
        );

        $this->rrmdir($outputFolder);

    }


}