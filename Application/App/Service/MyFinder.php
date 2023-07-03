<?php
namespace App\Service;

use App\MHT;

class MyFinder{

    private string $base;

    public ?string $game = null;
    public ?string $platform = null;

    public function __construct(string $someFilePath ) {

        $info = pathinfo($someFilePath);
        $dir = $info['dirname'];
        $deep = 5;
        do {

            if (
                file_exists($dir . '/Manhunt2.exe') ||
                file_exists($dir . '/Manhunt2R.exe')
            ) {
                $this->base = realpath($dir);
                $this->game = MHT::GAME_MANHUNT_2;
                $this->platform = MHT::PLATFORM_PC;
                break;
            }else if (
                file_exists($dir . '/SLES_548.19') ||
                file_exists($dir . '/SLUS_216.13')
            ){
                $this->base = realpath($dir);
                $this->game = MHT::GAME_MANHUNT_2;
                $this->platform = MHT::PLATFORM_PS2;
                break;
            }else if (file_exists($dir . '/PARAM.SFO')){
                $this->base = realpath($dir);
                $this->game = MHT::GAME_MANHUNT_2;
                $this->platform = MHT::PLATFORM_PSP;
                break;
            }else if (file_exists($dir . '/main.dol')){
                $this->base = realpath($dir);
                $this->game = MHT::GAME_MANHUNT_2;
                $this->platform = MHT::PLATFORM_WII;
                break;
            }

            $dir .= '/..';

        }while($deep--);

    }

    public function findFile( string $filename ){
        $finder = new \Symfony\Component\Finder\Finder();
        $finder->in($this->base)->name($filename);

        foreach ($finder as $file) {

            return $file->getPath() . DIRECTORY_SEPARATOR . $filename;
        }

        return false;


    }

}