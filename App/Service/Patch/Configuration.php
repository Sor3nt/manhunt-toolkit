<?php

namespace App\Service\Patch;

use App\Service\Archive\Col;
use App\Service\Archive\Glg;
use App\Service\Archive\Glg\EntityTypeData;
use App\Service\NBinary;

class Configuration extends PatchAbstract
{


    public function apply($patch){

        /** @var Glg $handler */
        $handler = $this->resource->getHandler();

        echo "U";
        /** @var EntityTypeData\Ec[] $results */
        $results = $handler->unpack( $this->resource->getInput(), $this->game, $this->platform );

        foreach ($patch['entries'] as $entry) {

            if (isset($entry['files'])){

                $applied = false;
                foreach ($entry['files'] as $file) {
                    $file = $this->patchRoot . '/' . $file;

                    $fileName = str_replace('.glg', '', pathinfo($file)['basename']);

                    $alreadyAdded = false;
                    foreach ($results as $_glgPathName => $result) {

                        $glgPathName = explode("#", $_glgPathName)[1];

                        if (strtolower($glgPathName) === strtolower($fileName)){

                            /** @var EntityTypeData\Ec $patchEC */
                            $patchEC = (new EntityTypeData())->parse(new NBinary(file_get_contents($file)))[0];

                            if ($result->__toString() === $patchEC->__toString()){
                                echo "S";

                            }else{
                                echo "R";
                                $applied = true;
                                $results[$_glgPathName] = $patchEC;
                            }


                            $alreadyAdded = true;
                            break;

                        }
                    }

                    if ($alreadyAdded){
                        continue;
                    }

                    echo "A";
                    $results['999#' . $fileName . '.glg'] = (new EntityTypeData())->parse(new NBinary(file_get_contents($file)))[0];
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

            echo "B";
            $builder = new Glg();
            return $builder->pack( $results, $this->game, $this->platform );


        }

        return false;
    }


}