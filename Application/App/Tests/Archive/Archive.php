<?php
namespace App\Tests\Archive;

use PHPUnit\Framework\TestCase;

class Archive extends TestCase
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

        $mht = __DIR__ . '/../../../mht.php';

        $output = shell_exec(sprintf(
            'php %s %s %s %s %s', $mht, $cmd, $file, $game, $platform
        ) );

        $this->assertContains($contains, strtolower($output));
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