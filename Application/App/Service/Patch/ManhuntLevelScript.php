<?php

namespace App\Service\Patch;

use App\MHT;
use App\Service\Archive\Mls;
use App\Service\Archive\Mls\Build;
use App\Service\Resource;

class ManhuntLevelScript extends PatchAbstract
{

    public function patchHandling($patch){
        /** @var Mls $handler */
        $handler = $this->resource->getHandler();
        echo "U";
        $results = $handler->unpack($this->resource->getInput(), $this->game, $this->platform);
        $recompile = false;

        foreach ($patch['entries'] as $entry) {
            $applied = false;

            if (isset($entry['identifier']) && isset($entry['identifier']['entity'])) {
                $identifier = strtolower($entry['identifier']['entity']);

                $targetMls = false;

                foreach ($results as $mlsIndex => $mls) {
                    foreach ($mls as $blockIndex => $block) {

                        if (
                            ($blockIndex === "ENTT" && strtolower($block['name']) === $identifier) ||
                            ($blockIndex === "NAME" && strtolower($block['name']) === $identifier)
                        ) {
                            $targetMls = $mlsIndex;
                            break 2;
                        }
                    }
                }

                if ($targetMls === false)
                    die(sprintf("Error: Unable to find %s", $entry['identifier']['entity']));

                if (isset($entry['regex']) || isset($entry['batch'])) {

                    if (!isset($entry['batch'])) {
                        $entry['batch'] = [$entry];
                    }

                    foreach ($entry['batch'] as $batchEntry) {
                        if (isset($batchEntry['replace'])) {

                            preg_match($batchEntry['regex'], $results[$targetMls]['SRCE'], $matches);
                            $testReplaceTo = $batchEntry['replace'];
                            foreach ($matches as $index => $match) {
                                if ($index == 0) continue;
                                $testReplaceTo = str_replace('$' . $index, $match, $testReplaceTo);
                            }

                            if (!empty($testReplaceTo) && strpos($results[$targetMls]['SRCE'], $testReplaceTo) !== false) {
                                echo "S";
                                $this->exists[] = $batchEntry;

                            } else {
                                echo "R";
                                $results[$targetMls]['SRCE'] = preg_replace($batchEntry['regex'], $batchEntry['replace'], $results[$targetMls]['SRCE']);

                                unset($results[$targetMls]['CODE']);
                                $this->applied[] = $batchEntry;
                                $recompile = true;
                            }

                        } else {

                            if (isset($batchEntry['appendFile'])) {
                                $file = $this->patchRoot . '/' . $batchEntry['appendFile'];
                                $appendContent = file_get_contents($file);

                                if (strpos($results[$targetMls]['SRCE'], $appendContent) !== false) {
                                    echo "S";
                                    $this->exists[] = $batchEntry;
                                    continue;
                                }


                                echo "A";
                                $results[$targetMls] = [
                                    "NAME" => $results[$targetMls]["NAME"],
                                    "SRCE" => preg_replace($batchEntry['regex'], "$1\n" . $appendContent, $results[$targetMls]['SRCE'])
                                ];
                                $applied = true;

                            }else if (isset($batchEntry['prependFile'])){
                                $file = $this->patchRoot . '/' . $batchEntry['prependFile'];
                                $appendContent = file_get_contents($file);

                                if (strpos($results[$targetMls]['SRCE'], $appendContent) !== false) {
                                    echo "S";
                                    $this->exists[] = $batchEntry;
                                    continue;
                                }


                                echo "A";
                                $results[$targetMls] = [
                                    "NAME" => $results[$targetMls]["NAME"],
                                    "SRCE" => preg_replace($batchEntry['regex'], $appendContent . "\n$1", $results[$targetMls]['SRCE'])
                                ];
                                $applied = true;
                            }


                        }

                    }

                } else {
                    die(sprintf("Error: Unknown replace rule for Patch %s", $patch['name']));
                }
            } else if (isset($entry['files'])) {

                foreach ($entry['files'] as $file) {
                    $file = $this->patchRoot . '/' . $file;

                    $fileName = str_replace('.srce', '', pathinfo($file)['basename']);


                    $alreadyAdded = false;

                    foreach ($results as $resultIndex => $result) {
                        if (!isset($result['ENTT'])) continue;

                        if (strtolower($result['ENTT']['name']) === strtolower($fileName)) {

                            if ($result['SRCE'] == file_get_contents($file)) {
                                $alreadyAdded = true;
                                break;
                            }

                            $alreadyAdded = true;
                            echo "R";
                            $applied = true;
                            $results[$resultIndex] = [
                                "NAME" => $result['NAME'],
                                "SRCE" => file_get_contents($file)
                            ];
                        }
                    }

                    if ($alreadyAdded) {
                        continue;
                    }

                    echo "A";
                    $results[] = [
                        "NAME" => ["name" => $fileName],
                        "SRCE" => file_get_contents($file)
                    ];
                    $applied = true;
                }


            } else {
                die(sprintf("Error: Unknown method rule for Patch %s", $patch['name']));
            }


            if ($applied) $this->applied[] = $entry;
            else $this->exists[] = $entry;

        }

        if (count($this->applied) > 0) {
            if ($this->debug)
                echo sprintf("[DEBUG] %d patches applied\n", count($this->applied));

            if ($recompile === true) {
                //force rebuild for all files because the offset changed for all of them
                if (isset($entry['actions']) && in_array("LEVEL_VAR_MODIFIER", $entry['actions']) !== false) {
                    echo sprintf("[Note] Recompile all scripts - level_var offset changed.\n");

                    foreach ($results as $mlsIndex => $mls) {
                        unset($results[$mlsIndex]['CODE']);
                    }
                }
            }

            echo "B";

            $compiled = $handler->compileLevel($results, $this->game, $this->platform);
            $builder = new Build();
            return $builder->build($compiled, $this->game, $this->platform);
        }

        return false;
    }

    public function apply($patch)
    {
        if (isset($patch['actions']) && in_array("COMPILE", $patch['actions']) !== false) {
            /** @var Mls $handler */
            $handler = $this->resource->getHandler();
            $result = $handler->pack( $this->resource->getInput(), MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC );

            file_put_contents($outputTo, $result);
            echo "U";

        }else{
            return $this->patchHandling($patch);
        }



    }


}