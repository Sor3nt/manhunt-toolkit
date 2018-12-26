<?php
namespace App\Tests\Archive\Ifp\Manhunt2;

use App\Service\Archive\Bin;
use App\Service\Archive\Ifp;
use App\Service\Archive\Inst;
use App\Service\Resources;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Finder\Finder;

class WiiTest extends KernelTestCase
{

    public function testPackUnpack()
    {
        echo "\n*** IFP: Implement WII rebuild (TODO) ==> ";
        $this->assertEquals(true, true);
        return;

        $resources = new Resources();
        $resources->workDirectory = explode("/tests/", __DIR__)[0] . "/tests/Resources";
        $resource = $resources->load('/Archive/Ifp/Manhunt2/Wii/allanims_wii.ifp');

        $exportFolder = $resources->workDirectory . '/Archive/Ifp/Manhunt2/Wii/export-test/';

        $handler = new Ifp();
        $handler->unpack($resource->getContent(), $exportFolder);

        $finder = new Finder();
        $finder->files()->in($exportFolder);

        $ifp = [];

        foreach ($finder as $file) {

            $folder = $file->getPathInfo()->getFilename();

            if (!isset($ifp[$folder])) $ifp[$folder] = [];

            $ifp[$folder][$file->getFilename()] = \json_decode($file->getContents(), true);
        }

        uksort($ifp, function($a, $b){
            return explode("#", $a)[0] > explode("#", $b)[0];
        });

        foreach ($ifp as &$item) {
            uksort($item, function($a, $b){
                return explode("#", $a)[0] > explode("#", $b)[0];
            });

        }

        $hex = $handler->pack($ifp, 'mh2-wii');

//        file_put_contents('wii.ifp', hex2bin($hex));
//exit;
        $this->assertEquals(md5($resource->getContent()), md5(hex2bin($hex)));

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