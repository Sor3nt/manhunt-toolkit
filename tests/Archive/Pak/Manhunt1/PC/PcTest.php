<?php
namespace App\Tests\Archive\Pak\Manhunt1;

use App\Service\Archive\Bin;
use App\Service\Archive\Dff;
use App\Service\Archive\Inst;
use App\Service\Archive\Pak;
use App\Service\Resources;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Finder\Finder;

class PcTest extends KernelTestCase
{

    public function testPackUnpack()
    {
        echo "\n* PAK: Testing Manhunt 1 PC ==> ";

        $resources = new Resources();
        $resources->workDirectory = explode("/tests/", __DIR__)[0] . "/tests/Resources";
        $resource = $resources->load('/Archive/Pak/Manhunt1/PC/ManHunt.pak');

        $entries = $resource->getContent();


        $exportFolder = $resources->workDirectory . '/Archive/Pak/Manhunt1/PC/export-test/';
        @mkdir($exportFolder, 0777, true);

        foreach ($entries as $entry) {

            $pathInfo = pathinfo(substr($entry['name'], 2));

            @mkdir($exportFolder . $pathInfo['dirname'], 0777, true);

            file_put_contents($exportFolder . substr($entry['name'], 2), $entry['data']);
        }

        $finder = new Finder();
        $finder->name('*.*')->in($exportFolder);

        $data = [];
        foreach ($finder as $file) {
            $data[ './' . $file->getRelativePathname() ] = $file->getContents();
        }

        $dff = new Pak();

        $repacked = $dff->pack( $data );
        $data1 = $dff->unpack( $repacked );

        $this->assertEquals(
            count($data1),
            count($entries)
        );


        $data = [];
        foreach ($data1 as $entry) {
            $data[ $entry['name'] ] = $entry['data'];
        }

        $repacked = $dff->pack( $data );
        $data2 = $dff->unpack( $repacked );

        $this->assertEquals(
            md5(print_r($data1, true)),
            md5(print_r($data2, true))
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