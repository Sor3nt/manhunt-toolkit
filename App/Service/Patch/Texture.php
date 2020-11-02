<?php

namespace App\Service\Patch;

use App\Service\Archive\Tex;

class Texture extends PatchAbstract
{


    public function apply($patch){

        /** @var Tex $handler */
        $handler = $this->resource->getHandler();

        $results = $handler->unpack( $this->resource->getInput(), $this->game, $this->platform );

        foreach ($patch['entries'] as $entry) {

            if (isset($entry['files'])){

                $applied = false;
                foreach ($entry['files'] as $file) {

//                    $fileName = str_replace('.dds', '', pathinfo($file)['basename']);
                    $fileName = pathinfo($file)['basename'];

                    $alreadyAdded = false;
                    foreach ($results as $modelName => $result) {

                        if (strtolower($modelName) === strtolower($fileName)){

                            $content = file_get_contents($file);
                            if ($result === $content){

                            }else{
                                $applied = true;
                                $results[$fileName] = $content;
                            }

                            $alreadyAdded = true;
                            break;

                        }
                    }

                    if ($alreadyAdded){
                        continue;
                    }

                    $results[$fileName . '.dds'] = file_get_contents($file);
                    $applied = true;
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

            $builder = new Tex();
            return $builder->pack( $results, $this->game, $this->platform );


        }

        return false;
    }


}