<?php
namespace App\Tests\Archive\Inst\Manhunt1;

use App\Service\Archive\Inst;
use App\Service\Resources;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class XboxTest extends KernelTestCase
{

    public function testPackUnpack()
    {
        echo "\n* INST: Testing Manhunt 1 Xbox ==> ";

        $resources = new Resources();
        $resources->workDirectory = explode("/tests/", __DIR__)[0] . "/tests/Resources";
        $resource = $resources->load('/Archive/Inst/Manhunt1/XBOX/entity.inst');

        $content = $resource->getContent();

        $inst = new Inst();

        $compressed = $inst->pack($content);

        $this->assertEquals(md5($resource->getInput()), md5($compressed));

        $uncompressed = $inst->unpack($compressed);

        $this->assertEquals($content, $uncompressed);

    }

}