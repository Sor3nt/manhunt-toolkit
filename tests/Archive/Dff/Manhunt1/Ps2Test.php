<?php
namespace App\Tests\Archive\Dff\Manhunt1;

use App\Service\Archive\Bin;
use App\Service\Archive\Dff;
use App\Service\Archive\Inst;
use App\Service\Resources;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Finder\Finder;

class Ps2Test extends KernelTestCase
{

    public function testPackUnpack()
    {

        $resources = new Resources();
        $resources->workDirectory = explode("/tests/", __DIR__)[0] . "/tests/Resources";
        $resource = $resources->load('/Archive/Dff/Manhunt1/PS2/MODELS.DFF');

        $entries = $resource->getContent();


        $exportFolder = $resources->workDirectory . '/Archive/Dff/Manhunt1/PS2/export-test/';
        @mkdir($exportFolder, 0777, true);

        foreach ($entries as $entry) {
            file_put_contents($exportFolder . $entry['name'] . '.dff', $entry['data']);
        }

        $finder = new Finder();
        $finder->name('*.dff')->in($exportFolder);

        $data = [];
        foreach ($finder as $file) {
            $data[ $file->getFilename() ] = $file->getContents();
        }

        $dff = new Dff();
        $repacked = $dff->pack( $data );

        $this->assertEquals(
            mb_strlen($repacked),
            mb_strlen($resource->getBinary())
        );

        $this->rrmdir($exportFolder);
    }


    private function rrmdir($src) {
        $dir = opendir($src);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                $full = $src . '/' . $file;
                if ( is_dir($full) ) {
                    $this->rrmdir($full);
                }
                else {
                    unlink($full);
                }
            }
        }
        closedir($dir);
        rmdir($src);
    }



}