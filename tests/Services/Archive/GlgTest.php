<?php
namespace App\Tests\Command;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GlgTest extends KernelTestCase
{

    public function testPackUnpackMh2()
    {

        $content = file_get_contents(__DIR__ . '/../../Resources/resource1.glg');

        $this->assertEquals('eb999094e1e7ba4c4c16569cf7643083', md5($content));

        $glg = new Glg();

        $uncompressed = $glg->uncompress($content);

        $this->assertEquals('73dd7624109de1269989f646c5694c51', md5($uncompressed));

        $compressed = $glg->compress($uncompressed);
        $uncompressed2 = $glg->uncompress($compressed);

        $this->assertEquals('73dd7624109de1269989f646c5694c51', md5($uncompressed2));
        $this->assertEquals($uncompressed, $uncompressed2);


    }

}