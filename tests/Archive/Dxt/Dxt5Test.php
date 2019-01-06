<?php
namespace App\Tests\Archive\Txd\Extract\Manhunt2;

use App\Service\Archive\Bmp;
use App\Service\Archive\Dxt5;
use App\Service\Resources;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class Dxt5Test extends KernelTestCase
{

    public function testDxt5()
    {

        echo "\n* DXT5: Testing DXT5 Texture ==> ";

        $file = explode("/tests/", __DIR__)[0] . "/tests/Resources/Archive/Dxt/dxt.dxt5";

        $content = file_get_contents($file);

        $dxtHandler = new Dxt5();
        $bmpRgba =$dxtHandler->decode($content, 128, 128, 'abgr');

        $bmpHandler = new Bmp();
        $bmpImage = $bmpHandler->encode($bmpRgba, 128, 128);

        $this->assertEquals('192de14bdee1a89a6127545d0648aa1e', md5($bmpImage));

    }

}