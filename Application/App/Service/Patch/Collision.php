<?php

namespace App\Service\Patch;

use App\Service\Archive\Col;

class Collision extends PatchAbstract
{


    public function apply($patch){

        /** @var Col $handler */
        $handler = $this->resource->getHandler();

        echo "U";
        $results = $handler->unpack( $this->resource->getInput(), $this->game, $this->platform );

        foreach ($patch['entries'] as $entry) {

            if (isset($entry['files'])){

                $applied = false;
                foreach ($entry['files'] as $file) {
                    $file = $this->patchRoot . '/' . $file;

                    $fileName = str_replace('.json', '', pathinfo($file)['basename']);

                    $alreadyAdded = false;
                    foreach ($results as $colName => $result) {

                        if (strtolower($colName) === strtolower($fileName)){

                            $content = \json_decode(file_get_contents($file), true);
                            if ($result === $content){
                                echo "S";

                            }else{
                                echo "A";
                                $applied = true;
                                $results[$fileName. '.json'] = $content;
                            }


                            $alreadyAdded = true;
                            break;

                        }
                    }

                    if ($alreadyAdded){
                        continue;
                    }

                    echo "A";
                    $results[$fileName . '.json'] = \json_decode(file_get_contents($file), true);
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

            $builder = new Col();
            echo "B";
            return $builder->pack( $results, $this->game, $this->platform );


        }

        return false;
    }


}