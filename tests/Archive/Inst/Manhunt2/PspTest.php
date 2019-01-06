<?php
namespace App\Tests\Archive\Inst\Manhunt2;

use App\Service\Archive\Inst;
use App\Service\Resources;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PspTest extends KernelTestCase
{

    public function testPackUnpackMh2()
    {
        echo "\n* INST: Testing Manhunt 2 PSP ==> ";

        $resources = new Resources();
        $resources->workDirectory = explode("/tests/", __DIR__)[0] . "/tests/Resources";
        $resource = $resources->load('/Archive/Inst/Manhunt2/PSP/ENTINST.BIN');

        $content = $resource->getContent();

        $inst = new Inst();

        $compressed = $inst->pack($content);

        $this->assertEquals(md5($resource->getInput()), md5($compressed));

        $uncompressed = $inst->unpack($compressed);

        $this->assertEquals($content, $uncompressed);


    }

}