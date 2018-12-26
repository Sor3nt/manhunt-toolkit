<?php
namespace App\Tests\Archive\Mls\Build\Manhunt2;

use App\Service\Archive\Mls\Build;
use App\Service\Compiler\Compiler;
use App\Service\Resources;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PCTest extends KernelTestCase
{

    public function testLevelScript()
    {
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