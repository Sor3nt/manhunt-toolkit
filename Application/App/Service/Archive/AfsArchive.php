<?php

namespace App\Service\Archive;

use App\Service\File;
use App\Service\NBinary;
use Exception;

/**
 * Class AfsArchive
 * @package App\Service\Archive
 */
class AfsArchive
{

    /** @var NBinary */
    private $binary;

    private $entryCount = 0;
    private $metadataOffset = null;

    /**
     * AfsArchive constructor.
     * @param NBinary $binary
     * @throws Exception
     */
    public function __construct(NBinary $binary)
    {
        if ($binary->get(3) !== "AFS") throw new Exception('File is not a AFS Container');
        $this->binary = $binary;

        $this->entryCount = $binary->consume(4, NBinary::INT_32, 4);
    }


    /**
     * @param NBinary $binary
     * @return NBinary
     */
    private function getBlock(NBinary $binary)
    {
        $offset = $binary->consume(4, NBinary::INT_32);
        $size = $binary->consume(4, NBinary::INT_32);

        $current = $binary->current;
        $binary->current = $offset;
        $data = $binary->consume($size, NBinary::BINARY);
        $binary->current = $current;

        return new NBinary($data);
    }

    /**
     * @return File[]
     * @throws Exception
     */
    public function extract()
    {

        $count = $this->entryCount;

        $entries = [];
        $i = 1;
        while ($this->entryCount--) {


            $entry = new File($this->getBlock($this->binary, ));

            if ($entry->identify() == "afs") {
                $subAfs = new AfsArchive($entry->getContent());
                $subEntries = $subAfs->extract();

                foreach ($subEntries as $index => $subEntry) {
                    $subEntry->name = $subAfs->getName($index);
                    $entries[] = $subEntry;
                }

            } else {
                $entries[] = $entry;

            }
            $i++;
        }

        $this->metadataOffset = $this->binary->consume(4, NBinary::INT_32);
        return $entries;
    }

    public function getName($index) {
        $this->binary->current = $this->metadataOffset + ($index * 48);
        $name =  $this->binary->consume(32, NBinary::STRING);
        if(empty($name)) $name = "__generic__";

        if (strlen($name) <= 3)
            return $name;

        $number = (int) substr($name, 0, 2);

        switch ($number){
            case 1:
                return 'negative_search';
            case 2:
                return 'definite_sighting';
            case 5:
                return 'run_to_investigate';
            case 6:
                return 'walk_to_investigate';
            case 7:
                return 'stop_and_listen';
            case 8:
                return 'curiosity_no_result';
            case 9:
                return 'taunt_search';
            case 13:
                return 'taunt_chase';
            case 14:
                return 'taunt_short';
            case 15:
                return 'taunt_safe_zone';
            case 16:
                return 'taunt_boundary';
            case 17:
                return 'taunt_player_dead';
            case 18:
                return 'join_attack';
            case 19:
                return 'wait_enemy_alone';
            case 20:
                return 'wait_enemy_multiple';
            case 21:
                return 'sneak_investigate';
            case 22:
                return 'wait_in_cover';
            case 23:
                return 'surprise';
            case 24:
                return 'greetings';
            case 26:
                return 'claim_territory';
            case 29:
                return 'chat_statements';
            case 30:
                return 'chat_search';
            case 31:
                return 'chat_investigate';
            case 33:
                return 'shout_for_assistance';
            case 34:
                return 'pain_light';
            case 35:
                return 'pain_medium';
            case 36:
                return 'pain_high';
            case 37:
                return 'pain_long';
            case 38:
                return 'death_generic';
            case 40:
                return 'death_execution';
            case 41:
                return 'combat_grunt';
            case 42:
                return 'negative_chase_result';
            case 43:
                return 'begging_pleading';
            case 44:
                return 'dead_body_seen';
            case 50:
                return 'crawl_reaction';
            case 51:
                return 'specific_1';
            case 52:
                return 'specific_2';
            case 53:
                return 'specific_3';
            case 54:
                return 'flare_death';
        }


        return $name;
    }


}
