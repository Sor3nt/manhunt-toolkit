<?php
namespace App\Service\Archive;

use App\Service\NBinary;

class ContextMapBin extends Archive {

    public $name = 'Manhunt Audio Context Map';

    public static $supported = ['context_map.bin'];

    /**
     * @param $pathFilename
     * @param $input
     * @param $game
     * @param $platform
     * @return bool
     */
    public static function canPack( $pathFilename, $input, $game, $platform ){
        return false;
    }

    public function unpack(NBinary $binary, $game, $platform){

        $bankName = $binary->consume(32, NBinary::STRING);

        $result = [];
        $lastAudioCount = 0;
        for($bankIndex = 0; $bankIndex < 58; $bankIndex++){
            $count = $binary->consume(4, NBinary::INT_32);
            if ($count === $lastAudioCount)
                continue;

//            $amountOfAudiosInBank = $count - $lastAudioCount;

            $eventName = $this->getNameByBankIndex($bankIndex);

            for($x = $lastAudioCount; $x < $count; $x++){
                $result[] = [
                    'index' => $x,
                    'name' => $eventName,
                ];
            }

            $lastAudioCount = $count;

        }

        return ['name' => $bankName, 'result' => $result];
    }

    private function getNameByBankIndex(int $index): string {

        switch ($index){
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
            case 10:
                return 'positive_taunt_search';
            case 11:
                return 'negative_taunt_search';


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
            case 25:
                return 'player_';


            case 26:
                return 'claim_territory';
            case 27:
                return 'generic_ind';
            case 28:
                return 'whistli';


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



            case 47:
                return 'failed_search';
            case 48:
                return 'crawlspace_';
            case 49:
                return 'jump_reaction';
            case 50:
                return 'crawl_reaction';
            case 51:
                return 'chat_question';
            case 52:
                return 'chat_position';
            case 53:
                return 'chat_negative';
            case 54:
                return 'flare_death';
            case 55:
                return 'gascan_death';
        }


        return 'unknown_' . $index;
    }

    public function pack($data, $game, $platform)
    {
        // TODO: Implement pack() method.
    }
}