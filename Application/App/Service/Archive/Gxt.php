<?php
namespace App\Service\Archive;

use App\MHT;
use App\Service\NBinary;

class Gxt extends Archive {
    public $name = 'Text Translation';

    public static $supported = 'gxt';

    /**
     * @param $pathFilename
     * @param $input
     * @param $game
     * @param $platform
     * @return bool
     */
    public static function canPack( $pathFilename, $input, $game, $platform ){

        if (!$input instanceof NBinary) return false;

        if (
            strpos($input->binary, 'key') !== false &&
            strpos($input->binary, 'text') !== false)
            return true;

        return false;
    }

    public function getEntrySize($game, $platform) {

        switch ($game){
            case MHT::GAME_MANHUNT_2:

                switch ($platform) {
                    case MHT::PLATFORM_PSP_001:
                        return 12;
                    case MHT::PLATFORM_PSP:
                    case MHT::PLATFORM_PS2:
                        return 16;

                    default:
                        return 20;
                }

            case MHT::GAME_MANHUNT:
            default:
                return 12;

        }
    }

    public function getKeySize($game, $platform) {

        switch ($game){
            case MHT::GAME_MANHUNT_2:

                switch ($platform) {
                    case MHT::PLATFORM_PSP_001:
                    case MHT::PLATFORM_PSP:
                    case MHT::PLATFORM_PS2:
                        return 8;

                    default:
                        return 12;
                }

            case MHT::GAME_MANHUNT:
            default:
                return 8;

        }
    }

    public function unpack(NBinary $binary, $game, $platform){

        // a empty translation file
        if ($binary->length() <= 8) return [];

        $isWIiCheck = $binary->get(4, 4);
        if ($binary->unpack($isWIiCheck, NBinary::INT_32 ) > 100000){
            $platform = MHT::PLATFORM_WII;
            $binary->numericBigEndian = true;
        }

        $binary->current = 0;

        $indexHeader = [
            'fourCC'    => $binary->consume(4, NBinary::STRING),
            'blockSize' => $binary->consume(4, NBinary::INT_32),
        ];

        $indexBlock = [];

        $count = $indexHeader['blockSize'] / $this->getEntrySize($game, $platform);

        for( $i = 0; $i < $count; $i++ ){


            $entry = [
                'offset' => $binary->consume(4,  NBinary::INT_32),
                'key'    => $binary->consume($this->getKeySize($game, $platform), NBinary::STRING)
            ];

            if (
                $game == MHT::GAME_MANHUNT_2 &&
                $platform !== MHT::PLATFORM_PSP_001
            ){
                $entry['duration'] = $binary->consume(4,  NBinary::INT_32);
            }

            $indexBlock[] = $entry;
        }

        //skip fourCC and blockSize
        $binary->current += 8;

        $results = [];

        foreach ($indexBlock as $index => $entry) {
            $offset = $indexHeader['blockSize'] + $entry['offset'] + 16;

            if ($offset > $binary->length()){
                //unused translation keys
                continue;
            }else{
                $binary->jumpTo($offset);

                $result = [
                    'key' => $entry['key'],
                    'text' => $binary->getString("\x00\x00\x00", false)
                ];

                $result['text'] .= "\x00";

                $result['text'] = iconv(
                    $platform === MHT::PLATFORM_WII ? 'UTF-16' : 'UTF-16LE',
                    'UTF-8',
                    $result['text']
                );

                //MH2 only
                if (isset($entry['duration'])){
                    $result['duration'] = $entry['duration'];
                }

                $results[] = $result;
            }

        }


        return $results;
    }

    /**
     * @param $records
     * @param $game
     * @param $platform
     * @return null|string
     */
    public function pack( $records, $game, $platform ){

        $records = \json_decode($records->binary, true);

        if ($game == MHT::GAME_AUTO){
            $game = isset($records[0]['duration']) ? MHT::GAME_MANHUNT_2 : MHT::GAME_MANHUNT;
        }

        $binary = new NBinary();

        if ($platform == MHT::PLATFORM_WII){
            $binary->numericBigEndian = true;
        }

        $binary->write('TKEY', NBinary::STRING);
        $binary->write(count($records) * $this->getEntrySize($game, $platform), NBinary::INT_32);

        $data = new NBinary();
        $offsets = [];
        $textSizes = [];
        $offset = 0;

        foreach ($records as $record) {
            $offsets[] = $offset;

            $utf16Text = iconv(
                'UTF-8',
                $platform === MHT::PLATFORM_WII ? "utf-16" : "utf-16le",
                $record['text']
            );

            $utf16Text .= "\x00\x00";
            $utf16Text = bin2hex($utf16Text);

            $data->write($utf16Text, NBinary::HEX);

            $textSizes[] = strlen($utf16Text) / 2;

            $offset += strlen($utf16Text) / 2;
        }

        foreach ($records as $index => $record) {
            $binary->write($offsets[$index], NBinary::INT_32);
            $binary->write($record['key'], NBinary::STRING);
            $binary->write($binary->getPadding("\x00", $this->getKeySize($game, $platform), $record['key']), NBinary::BINARY);

            if (isset($record['duration'])){
                $binary->write($record['duration'], NBinary::INT_32);
            } else if ($game === MHT::GAME_MANHUNT_2 && $platform !== MHT::PLATFORM_PSP_001) {
                $binary->write($textSizes[$index] << 6, NBinary::INT_32);

            }
        }

        $binary->write('TDAT', NBinary::STRING);
        $binary->write($data->length(), NBinary::INT_32);

        $binary->concat($data);

        return $binary->binary;
    }
}