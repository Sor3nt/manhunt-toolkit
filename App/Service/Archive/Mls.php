<?php
namespace App\Service\Archive;

use App\MHT;
use App\Service\Archive\Mls\Build;
use App\Service\Archive\Mls\Extract;
use App\Service\CompilerV2\Compiler;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Mls extends Archive {

    public $name = 'Levelscript';

    public static $supported = ['mls', 'scc'];

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
        if ($game == MHT::GAME_AUTO){
            die("\n\nNo Autodetection available, please provide the game with --game=mh2\n");
        }

        if ($platform == MHT::PLATFORM_AUTO) $platform = MHT::PLATFORM_PC;

        $extractor = new Extract($binary, $game, $platform);

        return $extractor->get();
    }


    /**
     * @param Finder $finder
     * @param $game
     * @param $platform
     * @return array
     * @throws \Exception
     */
    private function prepareData( Finder $finder, $game, $platform ){

        $scripts = [];

        $finder->sortByName();

        foreach ($finder as $file) {

            preg_match('/code|data|dataraw|name|dmem|trce|entt|line|nameremain|scpt|smem|stab|srce/', $file->getExtension(), $validFile);

            if (count($validFile) == 0) continue;
            list($index, $filename) = explode("#", $file->getFilename());

            list($scriptName, $section) = explode(".", $filename);
            $index = $index.$scriptName;

            if (!isset($scripts[$index])) $scripts[$index] = [ "NAME" => [ 'name' => $scriptName] ];

            $content = $file->getContents();

            if (strtoupper($section) !== "SRCE"){
                $content = \json_decode($content, true);
            }

            $scripts[$index][ strtoupper($section) ] = $content;

        }

        $scripts = $this->compileLevel($scripts,$game, $platform);
//
//        $firstScript = current($scripts);
//
//        //for the supported files, we need to compile the src and generate the needed sections
//        $levelScriptCompiler = new Compiler($firstScript['SRCE'], $game, $platform);
//        $levelScriptCompiled = $levelScriptCompiler->compile();
//
////        $levelScriptCompiled = $compiler->parse($scripts[0]['SRCE'], false, $game, $platform);
//
//        foreach ($scripts as &$script) {
//            if (!isset($script['CODE'])){
//                $compiler = new Compiler($script['SRCE'], $game, $platform);
//                $compiler->levelScript = $levelScriptCompiler;
//
//                $name = $script['NAME']['name'];
//                $script = $compiler->compile();
//                $script['NAME'] = [ 'name' => $name];
//            }
//        }

        return $scripts;
    }

    public function compileLevel($scripts, $game, $platform){
        $firstScript = current($scripts);

        //for the supported files, we need to compile the src and generate the needed sections
        $levelScriptCompiler = new Compiler($firstScript['SRCE'], $game, $platform);
        $levelScriptCompiled = $levelScriptCompiler->compile();

//        $levelScriptCompiled = $compiler->parse($scripts[0]['SRCE'], false, $game, $platform);

        foreach ($scripts as &$script) {
            if (!isset($script['CODE'])){
                $compiler = new Compiler($script['SRCE'], $game, $platform);
                $compiler->levelScript = $levelScriptCompiler;

                $name = $script['NAME']['name'];
                $script = $compiler->compile();
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
     * @throws \Exception
     */
    public function pack( $scripts, $game, $platform){

        if ($game == MHT::GAME_AUTO){
            die("\n\nNo Autodetection available, please provide the game with --game=mh2\n");
        }

        if ($platform == MHT::PLATFORM_AUTO) $platform = MHT::PLATFORM_PC;

        $scripts = $this->prepareData( $scripts, $game, $platform );

        $builder = new Build();
        return $builder->build( $scripts, $game, $platform );

    }

    public function getValidatedResults( $data, $game, $platform ){

        $levelScript = null;

        $results = [];

        foreach ($data as $mhscIndex => $mhsc) {




            $scriptName = $mhsc['NAME']['name'];

            try{
                $compiler = new Compiler($mhsc['SRCE'], $game, $platform);
                $compiler->levelScript = $levelScript;
                $subMls = $compiler->compile();

                if ($mhscIndex == 0){
                    //todo: check ENTT not the index
                    $levelScript = $compiler;
                }

                if(!$compiler->validateCode($mhsc['CODE'])){
                    throw new \Exception('CODE did not match');
                }


                foreach ($subMls as $index => $section) {

#                    //only used inside the compiler
                    if ($index == "CODE") continue;
                    if ($index == "extra") continue;

                    //memory is not correct but works...
                    if ($index == "DMEM") continue;
                    if ($index == "SMEM") continue;
                    if ($index == "TRCE") continue;

                    //we do not generate the LINE (debug stuff)
                    if ($index == "LINE") continue;

                    if ($index == "STAB") continue;

                    if ($index == "DATA"){

                        if (!isset($mhsc[$index])){

                            if (
                                count($section['const']) == 0 &&
                                count($section['strings']) == 0
                            ){
                                continue;
                            }
                        }

                        if ($mhsc[$index] != $section){
                            unset($mhsc[$index]['byteReserved']);

                        }
                    }

                    if ($index == "STAB"){
                        foreach ($mhsc[$index] as &$mhl) {
                            unset($mhl['nameGarbage']);
                        }
                    }

                    if ($mhsc[$index] != $section){
                        throw new \Exception($index . ' did not match');
                    }
                }

                $results[ 'supported/' . $mhscIndex . "#" . $scriptName . '.srce' ] = $mhsc['SRCE'];

            }catch(\Exception $e){

                $results[ 'not-supported/' . $mhscIndex . "#" . $scriptName . '.error' ] = $e->getMessage();
                $results[ 'not-supported/' . $mhscIndex . "#" . $scriptName . '.name' ] = $mhsc['NAME'];
                $results[ 'not-supported/' . $mhscIndex . "#" . $scriptName . '.code' ] = $mhsc['CODE'];
                $results[ 'not-supported/' . $mhscIndex . "#" . $scriptName . '.srce' ] = $mhsc['SRCE'];
                $results[ 'not-supported/' . $mhscIndex . "#" . $scriptName . '.line' ] = $mhsc['LINE'];
                $results[ 'not-supported/' . $mhscIndex . "#" . $scriptName . '.trce' ] = $mhsc['TRCE'];
                $results[ 'not-supported/' . $mhscIndex . "#" . $scriptName . '.scpt' ] = $mhsc['SCPT'];
                $results[ 'not-supported/' . $mhscIndex . "#" . $scriptName . '.smem' ] = $mhsc['SMEM'];
                $results[ 'not-supported/' . $mhscIndex . "#" . $scriptName . '.dmem' ] = $mhsc['DMEM'];
                $results[ 'not-supported/' . $mhscIndex . "#" . $scriptName . '.entt' ] = $mhsc['ENTT'];

                if (isset($mhsc['DATA'])){
                    $results[ 'not-supported/' . $mhscIndex . "#" . $scriptName . '.data' ] = $mhsc['DATA'];
                }

                if (isset($mhsc['STAB'])) {
                    $results[ 'not-supported/' . $mhscIndex . "#" . $scriptName . '.stab' ] = $mhsc['STAB'];
                }
            }
        }

        return $results;
    }
}