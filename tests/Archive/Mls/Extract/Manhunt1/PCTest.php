<?php
namespace App\Tests\Archive\Mls\Extract\Manhunt1;

use App\Service\Resources;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PCTest extends KernelTestCase
{

    public function testLevel1()
    {
        echo "\n* MLS: Testing Manhunt 1 PC (extract) ==> ";

        $resources = new Resources();
        $resources->workDirectory = explode("/tests/", __DIR__)[0] . "/tests/Resources";
        $mlsContent = $resources->load('/Archive/Mls/Manhunt1/PC/asylum.mls', [
            'game' => 'mh1'
        ]);

        $mhls = $mlsContent->getContent();

        //98 scripts inside level 1
        $this->assertEquals(56, count($mhls));


        $this->assertEquals(true, isset($mhls[0]['SCPT']));
        $this->assertEquals(true, isset($mhls[0]['NAME']));
        $this->assertEquals(true, isset($mhls[0]['ENTT']));
        $this->assertEquals(true, isset($mhls[0]['CODE']));
        $this->assertEquals(true, isset($mhls[0]['DATA']));
        $this->assertEquals(true, isset($mhls[0]['SMEM']));
        $this->assertEquals(true, isset($mhls[0]['STAB']));

        $this->assertEquals('levelscript', $mhls[0]['NAME']);
        $this->assertEquals('objectscript', $mhls[1]['NAME']);
        $this->assertEquals('playerscript', $mhls[55]['NAME']);


    }

}