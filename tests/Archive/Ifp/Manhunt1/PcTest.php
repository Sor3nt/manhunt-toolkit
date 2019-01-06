<?php
namespace App\Tests\Archive\Ifp\Manhunt1;

use App\Service\Archive\Bin;
use App\Service\Archive\Ifp;
use App\Service\Archive\Inst;
use App\Service\Resources;
use App\Tests\Archive\Archive;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Finder\Finder;

class PcTest extends Archive
{

    public function test()
    {
        echo "\n* IFP: Testing Manhunt 1 PC ==> ";


        $testFolder = explode("/tests/", __DIR__)[0] . "/tests/Resources/Archive/Ifp/Manhunt1/PC";
        $outputFolder = $testFolder . "/export";

        echo "\n* IFP: Testing Manhunt 1 PC (unpack/pack) ";
        $this->unPackPack(
            $testFolder . "/allanims.ifp",
            $outputFolder . "/allanims#ifp",
            'animations',
            'mh1'
        );

        $this->assertEquals(
            md5(file_get_contents($testFolder . "/allanims.ifp")),
            md5(file_get_contents($outputFolder . "/allanims.ifp"))
        );

        $this->rrmdir($outputFolder);



    }


}