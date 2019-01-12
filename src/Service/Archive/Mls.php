<?php
namespace App\Service\Archive;

use App\MHT;
use App\Service\Archive\Mls\Build;
use App\Service\Archive\Mls\Extract;
use App\Service\Compiler\Compiler;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Mls extends Archive {

    public $name = 'Levelscript';

    public static $supported = 'mls';

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
            if ($file->getExtension() == "srce") return true;
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

        if ($game == MHT::GAME_AUTO) $game = MHT::GAME_MANHUNT_2;
        if ($platform == MHT::PLATFORM_AUTO) $platform = MHT::PLATFORM_PC;

        $extractor = new Extract($binary, $game, $platform);

        return $extractor->get();
    }


    private function prepareData( Finder $finder, $game ){

        $scripts = [];

        foreach ($finder as $file) {

            preg_match('/code|data|dataraw|name|dmem|trce|entt|line|nameremain|scpt|smem|stab|srce/', $file->getExtension(), $validFile);

            if (count($validFile) == 0) continue;
            list($index, $filename) = explode("#", $file->getFilename());
            $index = (int) $index;

            list($scriptName, $section) = explode(".", $filename);

            if (!isset($scripts[$index])) $scripts[$index] = [ "NAME" => [ 'name' => $scriptName] ];

            $content = $file->getContents();

            if (strtoupper($section) !== "SRCE"){
                $content = \json_decode($content, true);
            }

            $scripts[$index][ strtoupper($section) ] = $content;

        }

        //for the supported files, we need to compile the src and generate the needed sections
        $compiler = new Compiler();

        $levelScriptCompiled = $compiler->parse($scripts[0]['SRCE'], false, $game);

        foreach ($scripts as &$script) {
            if (!isset($script['CODE'])){
                $compiler = new Compiler();
                $name = $script['NAME']['name'];
                $script = $compiler->parse($script['SRCE'], $levelScriptCompiled);
                $script['NAME'] = [ 'name' => $name];
            }
        }

        return $scripts;
    }

    /**
     * @param $scripts
     * @param $game
     * @param $platform
     * @return string
     */
    public function pack( $scripts, $game, $platform){

        if ($game == MHT::GAME_AUTO) $game = MHT::GAME_MANHUNT_2;
        if ($platform == MHT::PLATFORM_AUTO) $platform = MHT::PLATFORM_PC;

        $scripts = $this->prepareData( $scripts, $game );

        $builder = new Build();
        return $builder->build( $scripts );

    }

    public function getValidatedResults( $data ){

        $levelScript = false;

        $results = [];

        foreach ($data as $index => $mhsc) {

            $scriptName = $mhsc['NAME']['name'];

            $compiler = new Compiler();
            try{

                $compiled = $compiler->parse($mhsc['SRCE'], $levelScript);

                if ($index == 0){
                    $levelScript = $compiled;
                }

                if ($compiled['CODE'] != $mhsc['CODE']) throw new \Exception('CODE did not match');

                $results[ 'supported/' . $index . "#" . $scriptName . '.srce' ] = $mhsc['SRCE'];

            }catch(\Exception $e){


                $results[ 'not-supported/' . $index . "#" . $scriptName . '.name' ] = $mhsc['NAME'];
                $results[ 'not-supported/' . $index . "#" . $scriptName . '.code' ] = $mhsc['CODE'];
                $results[ 'not-supported/' . $index . "#" . $scriptName . '.srce' ] = $mhsc['SRCE'];
                $results[ 'not-supported/' . $index . "#" . $scriptName . '.line' ] = $mhsc['LINE'];
                $results[ 'not-supported/' . $index . "#" . $scriptName . '.trce' ] = $mhsc['TRCE'];
                $results[ 'not-supported/' . $index . "#" . $scriptName . '.scpt' ] = $mhsc['SCPT'];
                $results[ 'not-supported/' . $index . "#" . $scriptName . '.smem' ] = $mhsc['SMEM'];
                $results[ 'not-supported/' . $index . "#" . $scriptName . '.dmem' ] = $mhsc['DMEM'];
                $results[ 'not-supported/' . $index . "#" . $scriptName . '.entt' ] = $mhsc['ENTT'];

                if (isset($mhsc['DATA'])){
                    $results[ 'not-supported/' . $index . "#" . $scriptName . '.data' ] = $mhsc['DATA'];
                }

                if (isset($mhsc['STAB'])) {
                    $results[ 'not-supported/' . $index . "#" . $scriptName . '.stab' ] = $mhsc['STAB'];
                }
            }
        }

        return $results;
    }
}