<?php

namespace App\Service\Patch;

use App\MHT;
use App\Service\Archive\Mdl;
use App\Service\Archive\Mdl\Build;
use App\Service\Resource;
use App\Service\Resources;

class Model extends PatchAbstract
{


    public function apply($patch){

        /** @var Mdl $handler */
        $handler = $this->resource->getHandler();
        $handler->keepOrder = true;

        $results = $handler->unpack( $this->resource->getInput(), $this->game, $this->platform );

        foreach ($patch['entries'] as $entry) {

            if (isset($entry['files'])){

                $applied = false;
                foreach ($entry['files'] as $file) {

                    $fileName = str_replace('.mdl', '', pathinfo($file)['basename']);


                    $alreadyAdded = false;
                    foreach ($results as $modelName => $result) {

                        $modelRealName = explode(".", explode("#", $modelName)[1])[0];
                        $modelRealName = strtolower($modelRealName);

                        if ($modelRealName === strtolower($fileName)){
                            $alreadyAdded = true;
                            break;

                        }
                    }

                    if ($alreadyAdded){
                        continue;
                    }

                    $results['9999#' . $fileName . '.mdl'] = file_get_contents($file);
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

            $builder = new Mdl();
            return $builder->pack( $results, $this->game, $this->platform );


        }

        return false;
    }


}