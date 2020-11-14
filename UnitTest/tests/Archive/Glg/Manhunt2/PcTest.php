<?php
namespace App\Tests\Archive\Glg\Manhunt2;

use App\Service\Archive\ZLib;
use PHPUnit\Framework\TestCase;

class PcTest extends TestCase
{

    public function testPackUnpackMh2()
    {

        echo "\n* GLG: Testing Manhunt 2 PC (unpack/pack) ==> ";

        $file = explode("/tests/", __DIR__)[0] . "/tests/Resources/Archive/Glg/Manhunt2/PC/resource1.glg";

        $content = file_get_contents($file);

        //repack and unpack again
        $zlib = new ZLib();

        $compressed = $zlib->compress($content);

        $uncompressed2 = $zlib->uncompress($compressed);

        $this->assertEquals($content, $uncompressed2);


    }

}