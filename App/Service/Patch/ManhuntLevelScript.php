<?php

namespace App\Service\Patch;

use App\MHT;
use App\Service\Archive\Mls;
use App\Service\Archive\Mls\Build;
use App\Service\Resource;

class ManhuntLevelScript extends PatchAbstract
{


    public function apply($patch){

        /** @var Mls $handler */
        $handler = $this->resource->getHandler();
        echo "U";
        $results = $handler->unpack( $this->resource->getInput(), $this->game, $this->platform );
        $recompile = false;

        foreach ($patch['entries'] as $entry) {

            if (isset($entry['identifier']) && isset($entry['identifier']['entity'])){
                $identifier = strtolower($entry['identifier']['entity']);

                $targetMls = false;

                foreach ($results as $mlsIndex => $mls) {
                    foreach ($mls as $blockIndex => $block) {

                        if ($blockIndex === "ENTT" && strtolower($block['name']) === $identifier){
                            $targetMls = $mlsIndex;
                            break 2;
                        }
                    }
               }

                if ($targetMls === false)
                    die(sprintf("Error: Unable to find %s", $entry['identifier']['entity']));

                if (isset($entry['regex'])){

                    preg_match($entry['regex'], $results[$targetMls]['SRCE'], $matches);
                    $testReplaceTo = $entry['replace'];
                    foreach ($matches as $index => $match) {
                        if ($index == 0 ) continue;
                        $testReplaceTo = str_replace('$' . $index, $match, $testReplaceTo);
                    }

                    if (!empty($testReplaceTo) && strpos($results[$targetMls]['SRCE'], $testReplaceTo) !== false){
                        echo "S";
                        $this->exists[] = $entry;

                    }else{
                        echo "R";
                        $results[$targetMls]['SRCE'] = preg_replace($entry['regex'], $entry['replace'], $results[$targetMls]['SRCE']);

                        unset($results[$targetMls]['CODE']);
                        $this->applied[] = $entry;
                        $recompile = true;
                    }


                }else{
                    die(sprintf("Error: Unknown replace rule for Patch %s", $patch['name']));
                }
            }

            else if (isset($entry['files'])){

                $applied = false;
                foreach ($entry['files'] as $file) {
                    $file = $this->patchRoot . '/' . $file;

                    $fileName = str_replace('.srce', '', pathinfo($file)['basename']);


                    $alreadyAdded = false;
                    foreach ($results as $result) {
                        if ($result['NAME']['name'] === $fileName){
                            $alreadyAdded = true;
                            break;

                        }
                    }

                    if ($alreadyAdded){
                        continue;
                    }

                    echo "A";
                    $results[] = [
                        "NAME" => [ "name" => $fileName],
                        "SRCE" => file_get_contents($file)
                    ];
                    $applied = true;
                }

                if($applied) $this->applied[] = $entry;
                else $this->exists[] = $entry;


            }

            else{
                die(sprintf("Error: Unknown method rule for Patch %s", $patch['name']));
            }

        }

        if (count($this->applied) > 0){
            if ($this->debug)
                echo sprintf("[DEBUG] %d patches applied\n", count($this->applied));

            if ($recompile === true){
                //force rebuild for all files because the offset changed for all of them
                if (isset($entry['actions']) && in_array("LEVEL_VAR_MODIFIER", $entry['actions']) !== false ){
                    echo sprintf("[Note] Recompile all scripts - level_var offset changed.\n");

                    foreach ($results as $mlsIndex => $mls) {
                        unset($results[$mlsIndex]['CODE']);
                    }
                }
            }

            echo "B";

            $compiled = $handler->compileLevel($results, $this->game, $this->platform);
            $builder = new Build();
            return $builder->build( $compiled, $this->game, $this->platform );
        }

        return false;
    }


}