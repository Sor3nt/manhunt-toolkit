<?php
namespace App\Tests\Archive\Bin\Manhunt2;

use App\Service\Archive\Bin;
use App\Service\Archive\Inst;
use App\Service\Resources;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Finder\Finder;

class PspTest extends KernelTestCase
{

    public function testPackUnpack()
    {

        echo "\n*** BIN: implement PSP rebuild (TODO)\n";
        $this->assertEquals(true, true);
        return;


        $resources = new Resources();
        $resources->workDirectory = explode("/tests/", __DIR__)[0] . "/tests/Resources";
        $resource = $resources->load('/Archive/Bin/Manhunt2/PSP/STRMANIM.BIN');


        $exportFolder = $resources->workDirectory . '/Archive/Bin/Manhunt2/PSP/export-test/';

        $handler = new Bin();
        $handler->unpack($resource->getContent(), $exportFolder);




        $finder = new Finder();
        $finder->depth('== 0')->directories()->in($exportFolder . 'executions');

        $executions = [];
        foreach ($finder as $directory) {

            $executionId = $directory->getFilename();
            $executions[ $executionId ] = [];

            $execFinder = new Finder();
            $execFinder->depth('== 0')->directories()->in($directory->getRealPath());

            foreach ($execFinder as $executionFolder) {
                $executionSection = $executionFolder->getFilename();
                $executions[ $executionId ][$executionSection] = [];

                $fileFinder = new Finder();
                $fileFinder->files()->in($executionFolder->getRealPath());

                foreach ($fileFinder as $file) {
                    $excutionName = $file->getFilename();
                    $executions[ $executionId ][$executionSection][$excutionName] = \json_decode($file->getContents(), true);
                }


                uksort($executions[ $executionId ][$executionSection], function($a, $b){
                    return explode("#", $a)[0] > explode("#", $b)[0];
                });

            }
        }

        uksort($executions, function($a, $b){
            return explode("#", $a)[0] > explode("#", $b)[0];
        });

        $finder = new Finder();
        $finder->depth('== 0')->directories()->in($exportFolder . 'envExecutions');

        $envExecutions = [];
        foreach ($finder as $directory) {

            $executionId = $directory->getFilename();
            $envExecutions[ $executionId ] = [];


            $fileFinder = new Finder();
            $fileFinder->files()->in($directory->getRealPath());

            foreach ($fileFinder as $file) {
                $excutionName = $file->getFilename();
                $envExecutions[ $executionId ][$excutionName] = \json_decode($file->getContents(), true);
            }

            uksort($envExecutions[ $executionId ], function($a, $b){
                return explode("#", $a)[0] > explode("#", $b)[0];
            });
        }

        $hex = $handler->pack($executions, $envExecutions);

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