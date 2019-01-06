<?php
namespace App\Tests\Archive;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class Archive extends KernelTestCase
{

    public function unPackPack($file1, $file2, $contains, $game = null){
        $this->call(
            'unpack',
            $file1,
            $contains,
            $game
        );

        $this->call(
            'pack',
            $file2,
            $contains,
            $game
        );

    }

    public function call( $cmd, $file, $contains, $game = null  ){

        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find($cmd);
        $commandTester = new CommandTester($command);

        $options = [
            'command'  => $command->getName(),
            'file' => $file
        ];

        if ($game != null){
            $options['--game'] = $game;
        }

        $commandTester->execute($options);

        $output = strtolower($commandTester->getDisplay());

        $this->assertNotContains('error', $output);
        $this->assertNotContains('warning', $output);
        $this->assertNotContains('notice', $output);
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