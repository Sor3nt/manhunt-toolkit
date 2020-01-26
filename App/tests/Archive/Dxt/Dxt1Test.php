<?php
namespace App\Tests\Archive\Txd\Extract\Manhunt2;

use App\Service\Archive\Bmp;
use App\Service\Archive\Dxt1;
use PHPUnit\Framework\TestCase;

class Dxt1Test extends TestCase
{

    public function testDxt1()
    {
        echo "\n* DXT1: Testing DXT1 Texture ==> ";

        $file = explode("/tests/", __DIR__)[0] . "/tests/Resources/Archive/Dxt/dxt.dxt1";

        $content = file_get_contents($file);

        $dxtHandler = new Dxt1();
        $bmpRgba =$dxtHandler->decode($content, 128, 128, 'abgr');

        $bmpHandler = new Bmp();
        $bmpImage = $bmpHandler->encode($bmpRgba, 128, 128);

        $this->assertEquals('aa62051473a63a14cba17a27d1bc0ac9', md5($bmpImage));

    }

}