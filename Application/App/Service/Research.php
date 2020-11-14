<?php
namespace App\Service;

use App\MHT;
use App\Service\Archive\Glg\EntityTypeData\Ec;
use App\Service\Archive\Mdl\Extract;

class Research{

    public $game = MHT::GAME_MANHUNT_2;
    public $platform = MHT::PLATFORM_PC;

    /** @var Ec[] */
    private $ini;

    /** @var array */
    private $models;

    /**
     * @param $glgIniFilename
     * @throws \Exception
     */
    public function setConfiguration($glgIniFilename)
    {
        $resources = new Resources();
        $resource = $resources->load($glgIniFilename, $this->game, $this->platform);
        $handler = $resource->getHandler();
        $this->ini = $handler->unpack( $resource->getInput(), $this->game, $this->platform );
    }

    /**
     * @param $modelFilename
     * @throws \Exception
     */
    public function setModel($modelFilename)
    {
        $resources = new Resources();
        $resource = $resources->load($modelFilename, $this->game, $this->platform);
        $handler = $resource->getHandler();
        $this->models = $handler->unpack( $resource->getInput(), $this->game, $this->platform );
    }

    public function getRecordsByRecordClass($recordClassName){
        /** @var Ec[] $records */
        $records = [];

        foreach ($this->ini  as $index => $ec) {
            if ($ec->get('class') !== $recordClassName) continue;
            $records[] = $ec;
        }

        return $records;
    }

    public function getModelByModelName($model){
        if ($model === false) return false;

        foreach ($this->models as $index => $mdl) {
            $name = explode('.', $index)[0];
            if ($name !== $model) continue;

            return $mdl;
        }
        return false;
    }

    public function getTextureNamesFromModel($model){

//        if ($this->platform === MHT::PLATFORM_PC){
            $handler = new Extract();
//        }else{
//            die($this->platform . " not implemented (getTextureNamesFromModel)");
//        }
        return $handler->getTextureNames(new NBinary($model));
    }


}