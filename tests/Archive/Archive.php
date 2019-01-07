<?php
namespace App\Tests\Archive;

use App\MHT;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class Archive extends KernelTestCase
{

    public function unPackPack($file1, $file2, $contains, $game, $platform ){
        $this->call(
            'unpack',
            $file1,
            $contains,
            $game,
            $platform
        );

        $this->call(
            'pack',
            $file2,
            $contains,
            $game,
            $platform
        );

    }

    public function call( $cmd, $file, $contains, $game, $platform  ){

        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find($cmd);
        $commandTester = new CommandTester($command);

        $options = [
            'command'  => $command->getName(),
            'file' => $file,
            '--game' => $game,
            '--platform' => $platform,
        ];

        $commandTester->execute($options);

        $output = strtolower($commandTester->getDisplay());

        $this->assertContains($contains, $output);
    }


    public function rrmdir($src) {
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