<?php
namespace App\Service\Archive\Glg\EntityTypeData;

use App\MHT;

class Ec {

    public $class           = null;
    public $name            = null;

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

        foreach ($record as $entry) {

            if (array_key_exists($entry['attr'] , $this->map)){

                if ($this->map[ $entry['attr'] ] === false) {
                    $this->map[$entry['attr']] = true;
                }else if (is_array($this->map[ $entry['attr'] ])){
                    $this->map[$entry['attr']][] = $entry['value'];
                }else{
                    if (!isset($entry['value'])) die("eh " . $entry['attr']);
                    $this->map[$entry['attr']] = $entry['value'];
                }
            }else{
//                var_dump($this->name, $entry['value']);
                throw new \Exception(sprintf('Unknown Attribute %s for Record Class %s', $entry['attr'], $this->class));
            }

        }
    }


    public function get($name)
    {

        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $name, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        $name = strtoupper(implode('_', $ret));
//        $cc = str_replace('_', '', ucwords($name, '_'));

        if (isset($this->map[$name])) return $this->map[$name];

        if ($name == "NAME") return $this->name;
        return false;
    }

    public function __toString()
    {
        $entries = [];

        foreach ($this->map as $attr => $value) {
            if (!is_null($value)){

                if ($value === true) {
                    $entries[] = sprintf('%s', $attr);
                }else if (is_array($value)){
                    foreach ($value as $single) {
                        $entries[] = sprintf('%s %s', $attr, $single);
                    }
                }else{
                    $entries[] = sprintf('%s %s', $attr, $value);
                }

            }
        }

        $record = sprintf("\nRECORD %s\n    ", $this->name);
        $record .= implode("\n    ", $entries) . "\n";
        $record .= "END";

        return $record;
    }

}
