<?php
namespace App\Service\Archive\Glg\EntityTypeData;

use App\MHT;

class Ec {

    public $class           = null;
    public $name            = null;
    public $force            = false;
    public $index            = 0;

    protected $map = [];

    /**
     * EcBasic constructor.
     * @param $name
     * @param $record
     * @throws \Exception
     */
    public function __construct( $name,  $record )
    {

        $this->name = $name;
        $this->map = $record['options'];
        $this->force = $record['force'];
    }


    public function get($name)
    {

        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $name, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        $name = strtoupper(implode('_', $ret));
        if ($name == "NAME") return $this->name;

        foreach ($this->map as $entry) {
            if ($entry['attr'] == $name) return $entry['value'];
        }

        return false;
    }

    public function __toString()
    {
        $entries = [];

        foreach ($this->map as $index => $entry) {

            $value = isset($entry['value']) ? $entry['value'] : true;
            $attr = $entry['attr'];

            if (!is_null($value)){

                if (is_array($value)){
                    foreach ($value as $single) {
                        $entries[] = sprintf('%s %s', $attr, $single);
                    }
                }elseif ($value === true) {
                    $entries[] = sprintf('%s', $attr);
                }elseif ($value !== false) {
                    $entries[] = sprintf('%s %s', $attr, $value);
                }

            }
        }

        $record = sprintf("\nRECORD %s\n    ", $this->name);
        $record .= implode("\n    ", $entries) . "\n";
        $record .= "END";

        $record = str_replace('    #', '#', $record);

        if ($this->force){
            $record = "\n#FORCE" . $record;
        }

        return $record;
    }

}
