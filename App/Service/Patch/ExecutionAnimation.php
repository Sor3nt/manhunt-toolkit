<?php

namespace App\Service\Patch;

use App\Service\Archive\Bin;
use App\Service\Archive\Bin\Build;

class ExecutionAnimation extends PatchAbstract
{

    public function apply($patch){

        /** @var Bin $handler */
        $handler = $this->resource->getHandler();
        $handler->keepOrder = true;

        $ifpEntries = $handler->unpack( $this->resource->getInput(), $this->game, $this->platform );

        foreach ($patch['entries'] as $entry) {

            if (isset($entry['files'])){

                $applied = false;
                foreach ($entry['files'] as $patchFilePath) {
                    $patchFilePathInfo = pathinfo($patchFilePath);

                    $patchTargetFolderParts = explode("/", $entry['groupPath']);
                    $patchFilename = $patchFilePathInfo["basename"];

                    $patchFilenamePlain = $patchFilePathInfo["filename"];
                    if (strpos($patchFilePathInfo["filename"], "#") !== false){
                        $patchFilenamePlain = explode("#", $patchFilePathInfo["filename"])[1];
                    }

                    $foundTargetFolder = false;

                    foreach ($ifpEntries as $ifpFilePath => $ifpEntry) {

                        $ifpFilePathParts = explode("/", $ifpFilePath);
                        $ifpFilePathPartsPlain = [];

                        foreach ($ifpFilePathParts as $ifpFilePathPart) {
                            if (strpos($ifpFilePathPart, '#') !== false){
                                $ifpFilePathPartsPlain[] = explode('#', $ifpFilePathPart)[1];
                            }else{
                                $ifpFilePathPartsPlain[] = $ifpFilePathPart;
                            }
                        }

                        if($ifpFilePathPartsPlain[0] === "envExecutions"){

                            if ($ifpFilePathPartsPlain[0] === $patchTargetFolderParts[0]){

                                //we found our target folder

                                if ($patchTargetFolderParts[1] === $ifpFilePathPartsPlain[1]){


                                    $foundTargetFolder = $ifpFilePathParts[0] . '/' . $ifpFilePathParts[1];

                                    //the file is present, update or ignore
                                    if ($patchFilenamePlain === $ifpFilePathPartsPlain[2]){

                                        $applied = true;

                                        $ifpPatch = file_get_contents($patchFilePath);
                                        if (\json_encode($ifpEntry, JSON_PRETTY_PRINT) !== $ifpPatch){
                                            $ifpEntries[$ifpFilePath] = \json_decode($ifpPatch, true);
                                        }
                                    }

                                }

                                continue;

                            }else{
                                continue;
                            }
                        }else if($patchTargetFolderParts[0] === "executions"){
                            if ($ifpFilePathPartsPlain[0] === $patchTargetFolderParts[0]){

                                if ($patchTargetFolderParts[1] === $ifpFilePathPartsPlain[1]){
                                    if ($patchTargetFolderParts[2] === $ifpFilePathPartsPlain[2]){
                                        $foundTargetFolder = $ifpFilePathParts[0] . '/' . $ifpFilePathParts[1]. '/' . $ifpFilePathParts[2];

                                        //the file is present, update or ignore
                                        if ($patchFilenamePlain === $ifpFilePathPartsPlain[3]){

                                            $applied = true;

                                            $ifpPatch = file_get_contents($patchFilePath);
                                            if (\json_encode($ifpEntry, JSON_PRETTY_PRINT) !== $ifpPatch){
                                                $ifpEntries[$ifpFilePath] = \json_decode($ifpPatch, true);
                                            }
                                        }
                                    }
                                }


                            }

                        }else{
                            die(sprintf("Error: Unknown section %s for Patch %s", $ifpFilePathPartsPlain[0], $patch['name']));

                        }

                    }

                    if ($applied === false){
                        $ifpPatch = \json_decode(file_get_contents($patchFilePath), true);

                        $patchFilenameFinal = $patchFilename;
                        if (strpos($patchFilenameFinal, '#') === false){
                            $patchFilenameFinal = "999#" . $patchFilenameFinal;
                        }

                        $patchFilenameFinal = explode(".", $patchFilenameFinal)[0];

                        //we know our target folder
                        if ($foundTargetFolder !== false){
                            $ifpEntries[$foundTargetFolder . '/' . $patchFilenameFinal] = $ifpPatch;
                            $applied = true;

                        //create a new folder
                        }else{
                            $ifpEntries[$entry['groupPath'] . '/' . $patchFilenameFinal] = $ifpPatch;
                            $applied = true;
                        }

                    }

                }

                if($applied) $this->applied[] = $entry;
                else $this->exists[] = $entry;


            }else{
                die(sprintf("Error: Unknown method rule for Patch %s", $patch['name']));
            }

        }

        if (count($this->applied) > 0){
            if ($this->debug)
                echo sprintf("[DEBUG] %d patches applied\n", count($this->applied));

            $executionSections = $this->prepareData($ifpEntries, $handler->keepOrder);

            return (new Build())->build(
                $executionSections['executions'],
                $executionSections['envExecutions'],
                $this->game,
                $this->platform
            );
        }

        return false;
    }

    private function prepareData( $data, $keepOrder ){
        $executionSections = [ 'executions' => [], 'envExecutions' => []];

        foreach ($data as $fileNamePath => $file) {

            $pathSplit = explode("/", $fileNamePath);
            $usedSection = $pathSplit[0];

            if (!isset($executionSections[$usedSection][ $pathSplit[1] ]))
                $executionSections[$usedSection][ $pathSplit[1] ] = [];

            if ($usedSection == "executions"){

                if (!isset($executionSections[$usedSection][ $pathSplit[1] ][ $pathSplit[2] ]))
                    $executionSections[$usedSection][ $pathSplit[1] ][ $pathSplit[2] ] = [];

                $fileName = $pathSplit[3];

                $executionSections[$usedSection][ $pathSplit[1] ][ $pathSplit[2] ][$fileName] = $file;

                //sort the results (thats only to reach the 100% by recompiling original game files)
                if (strpos($fileName, "#") !== false){
                    $this->keepOrder = true;
                    uksort($executionSections[$usedSection][ $pathSplit[1] ][ $pathSplit[2] ], function($a, $b){
                        return explode("#", $a)[0] > explode("#", $b)[0];
                    });
                }

            }else{
                $fileName = explode('.', $pathSplit[2])[0];

                $executionSections[$usedSection][ $pathSplit[1] ][$fileName] = $file;

                //sort the results (thats only to reach the 100% by recompiling original game files)
                if (strpos($fileName, "#") !== false) {
                    $this->keepOrder = true;
                    uksort($executionSections[$usedSection][$pathSplit[1]], function ($a, $b) {
                        return explode("#", $a)[0] > explode("#", $b)[0];
                    });
                }
            }
        }

        //sort the results (thats only to reach the 100% by recompiling original game files)

        if ($keepOrder){
            uksort($executionSections['executions'], function($a, $b){
                return explode("#", $a)[0] > explode("#", $b)[0];
            });

            uksort($executionSections['envExecutions'], function($a, $b){
                return explode("#", $a)[0] > explode("#", $b)[0];
            });

        }

        return $executionSections;
    }
}