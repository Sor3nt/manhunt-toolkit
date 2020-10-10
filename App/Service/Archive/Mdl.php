<?php
namespace App\Service\Archive;

use App\MHT;
use App\Service\Archive\Mdl\Build;
use App\Service\Archive\Mdl\Extract;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Mdl extends Archive {
    public $name = 'Model File (MDL)';

//    public static $supported = 'mdl';
    public static $validationMap = [
        [0, 4, NBinary::HEX, ['504d4c43', '434c4d50']]
    ];


    public $game = MHT::GAME_MANHUNT_2;
    public $platform = MHT::PLATFORM_AUTO;

    public $keepOrder = false;

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

            if (strpos($a->getFileName(), '#') !== false){
                $a = (int) explode('#', $a->getFileName())[0];
                $b = (int) explode('#', $b->getFileName())[0];
                return $a > $b;

            }

            return false;
        });

        foreach ($finder as $file) {
            $binary = new NBinary($file->getContents());
            if (strpos($file->getFilenameWithoutExtension(), "#") !== false){
                $mdls[explode("#", $file->getFilenameWithoutExtension())[1]] = current($extractor->get($binary));
            }else{
                $mdls[$file->getFilenameWithoutExtension()] = current($extractor->get($binary));
            }
        }

        return $mdls;
    }


    public function pack( $pathFilename, $game, $platform ){

        $mdls = $this->prepareData($pathFilename);

        $build = new Build();
        $build->keepOrder = $this->keepOrder;

        $binary = $build->build($mdls);

        return $binary;
    }

    public function unpack(NBinary $binary, $game, $platform){

        $this->platform = $platform;

        if ($platform == MHT::PLATFORM_WII){
            $binary->numericBigEndian = true;
        }

        $extractor = new Extract();
        $extractor->keepOrder = $this->keepOrder;

        $data = $extractor->get($binary);
        return $extractor->convertEntriesToSingleMdl( $data );

    }




}