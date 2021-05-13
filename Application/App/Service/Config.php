<?php

namespace App\Service;


class Config
{
    public $config = [
        'games' => []
    ];

    public function __construct(){
        if (!file_exists('config.json'))
            file_put_contents('config.json', \json_encode($this->config));
        else
            $this->config = \json_decode(file_get_contents('config.json'), true);
    }

    public function save(){
        file_put_contents('config.json', \json_encode($this->config, JSON_PRETTY_PRINT));
    }

    public function get( $attr ){
        return $this->config[$attr];
    }

    public function set( $attr, $val, $autosave = false ){
        $this->config[$attr] = $val;

        if ($autosave == true) $this->save();
    }

    public function getGame($id){
        return $this->config['games'][$id];
    }

    public function addGame($game, $platform, $path){

        foreach ($this->config['games'] as $index => $entry) {
            if ($entry['path'] == $path)
                return $index;
        }

        $this->config['games'][] = [
            'id' => count($this->config['games']),
            'game' => $game,
            'platform' => $platform,
            'path' => $path
        ];

        return count($this->config['games']) - 1;
    }

}