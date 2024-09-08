<?php
namespace App\Service\Archive;

use App\MHT;
use App\Service\Archive\Bin\Build;
use App\Service\Archive\Bin\Extract;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Bin extends Archive {
    public $name = 'Execution Animations';

    public $keepOrder = false;

    public static $validationMap = [
        [0, 4, NBinary::HEX, ['01000000', '00000001']]
    ];

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
            $relPath = strtolower($file->getRelativePath());

            if (
                substr( $relPath, 0, 13 ) == 'envexecutions' ||
                substr( $relPath, 0, 10 ) == 'executions'
            ) return true;
        }

        return false;
    }


    /**
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return array
     */
    public function unpack(NBinary $binary, $game, $platform){
        //force to Manhunt 2 since Manhunt 1 did not use this
        $game = MHT::GAME_MANHUNT_2;

        $handler = new Extract();
        $handler->keepOrder = $this->keepOrder;

        return $handler->get($binary, $game, $platform);
    }


    /**
     * @param $data
     * @param $game
     * @param $platform
     * @return string
     */
    public function pack( $data, $game, $platform ){

        $executionSections = $this->prepareData( $data );

        //force to Manhunt 2 since Manhunt 1 did not use this
        $game = MHT::GAME_MANHUNT_2;

        if ($platform == MHT::PLATFORM_AUTO) $platform = MHT::PLATFORM_PC;

        return (new Build())->build(
            $executionSections['executions'],
            $executionSections['envExecutions'],
            $game,
            $platform
        );

    }


    /**
     * @param Finder $data
     * @return array
     */
    private function prepareData( $data ){
        $executionSections = [ 'executions' => [], 'envExecutions' => []];

        foreach ($data as $file) {

            $pathSplit = explode(DIRECTORY_SEPARATOR, $file->getRelativePathname());
            $usedSection = $pathSplit[0];

            if (!isset($executionSections[$usedSection][ $pathSplit[1] ]))
                $executionSections[$usedSection][ $pathSplit[1] ] = [];

            if ($usedSection == "executions"){

                if (!isset($executionSections[$usedSection][ $pathSplit[1] ][ $pathSplit[2] ]))
                    $executionSections[$usedSection][ $pathSplit[1] ][ $pathSplit[2] ] = [];

                $fileName = explode('.', $pathSplit[3])[0];

                $executionSections[$usedSection][ $pathSplit[1] ][ $pathSplit[2] ][$fileName] = \json_decode($file->getContents(), true);

                //sort the results (thats only to reach the 100% by recompiling original game files)
                if (strpos($fileName, "#") !== false){
                    $this->keepOrder = true;
                    uksort($executionSections[$usedSection][ $pathSplit[1] ][ $pathSplit[2] ], function($a, $b){
                        $_a = explode("#", $a)[0];
                        $_b = explode("#", $b)[0];
                        if ($_a == $_b) return 0;

                        return $_a > $_b ? 1 : -1;
                    });
                }

            }else{
                $fileName = explode('.', $pathSplit[2])[0];

                $executionSections[$usedSection][ $pathSplit[1] ][$fileName] = \json_decode($file->getContents(), true);

                //sort the results (thats only to reach the 100% by recompiling original game files)
                if (strpos($fileName, "#") !== false) {
                    $this->keepOrder = true;
                    uksort($executionSections[$usedSection][$pathSplit[1]], function ($a, $b) {
                        $_a = explode("#", $a)[0];
                        $_b = explode("#", $b)[0];
                        if ($_a == $_b) return 0;

                        return $_a > $_b ? 1 : -1;
                    });
                }
            }
        }

        //sort the results (thats only to reach the 100% by recompiling original game files)

        if ($this->keepOrder){
            uksort($executionSections['executions'], function($a, $b){
                $_a = explode("#", $a)[0];
                $_b = explode("#", $b)[0];
                if ($_a == $_b) return 0;
                return $_a > $_b ? 1 : -1;
            });

            uksort($executionSections['envExecutions'], function($a, $b){
                $_a = explode("#", $a)[0];
                $_b = explode("#", $b)[0];
                if ($_a == $_b) return 0;
                return $_a > $_b ? 1 : -1;
            });

        }

        return $executionSections;
    }
}