<?php
namespace App\Service\Archive;

use App\MHT;
use App\Service\Archive\Mdl\Build;
use App\Service\Archive\Mdl\Extract;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Mdl extends Archive {
    public $name = 'Model File';

    public static $supported = 'mdl';

    public $game = MHT::GAME_MANHUNT_2;
    public $platform = MHT::PLATFORM_AUTO;

    /**
     * @param $pathFilename
     * @param $input
     * @param $game
     * @param $platform
     * @return bool
     */
    public static function canPack( $pathFilename, $input, $game, $platform ){


        if (!$input instanceof Finder) return false;

        foreach ($input as $file) {
            $ext = strtolower($file->getExtension());

            if ($ext == "mdl") return true;
        }

        return false;
    }


    private function prepareData( Finder $finder ){
        $extractor = new Extract();

        $mdls = [];
        $finder->sort(function(SplFileInfo $a, SplFileInfo$b ){

            $a = (int) explode('#', $a->getFileName())[0];
            $b = (int) explode('#', $b->getFileName())[0];
            return $a > $b;
        });
        foreach ($finder as $file) {
            $binary = new NBinary($file->getContents());
            $mdls[] = current($extractor->get($binary));
            echo ".";
        }

        return $mdls;
    }


    public function pack( $pathFilename, $game, $platform ){

        $mdls = $this->prepareData($pathFilename);

        $build = new Build();

        $binary = $build->build($mdls);

        return $binary;
    }

    public function unpack(NBinary $binary, $game, $platform){

        $this->platform = $platform;

        if ($platform == MHT::PLATFORM_WII){
            $binary->numericBigEndian = true;
        }

        $extractor = new Extract();
        $data = $extractor->get($binary);
        return $extractor->convertEntriesToSingleMdl( $data );

    }




}