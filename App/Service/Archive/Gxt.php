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

    public function unpack(NBinary $binary, $game, $platform){


        // a empty translation file
        if ($binary->length() <= 8) return [];

        $isWIiCheck = $binary->get(4, 4);
        if ($binary->unpack($isWIiCheck, NBinary::INT_32 ) > 100000){
            $platform = MHT::PLATFORM_WII;
            $binary->numericBigEndian = true;
        }

        $indexHeader = [
            'fourCC'    => $binary->consume(4, NBinary::STRING),
            'blockSize' => $binary->consume(4, NBinary::INT_32),
        ];

        $indexBlock = [];
//
//
//        $test = $binary->consume(4, NBinary::INT_32, 16);
//
//        if ($game == MHT::GAME_AUTO){
//            if ($test > 100000){
//                $game = MHT::GAME_MANHUNT;
//            }else{
//                $game = MHT::GAME_MANHUNT_2;
//            }
//        }
//
//        $binary->jumpTo(8);

        $entrySize = 20; //mh2 default;

        if($game == MHT::GAME_MANHUNT_2 && ($platform == MHT::PLATFORM_PS2 || $platform == MHT::PLATFORM_PSP)) {
            $entrySize = 16;
        }else if($game == MHT::GAME_MANHUNT){
            $entrySize = 12;
        }

        for( $i = 0; $i < $indexHeader['blockSize'] / $entrySize; $i++ ){

            $keySizes = $game == MHT::GAME_MANHUNT ? 8 : 12;
            if($game == MHT::GAME_MANHUNT_2 && $platform == MHT::PLATFORM_PS2) {
                $keySizes = 8;
            }

            $entry = [
                'offset' => $binary->consume(4,  NBinary::INT_32),
                'key'    => $binary->consume($keySizes, NBinary::STRING)
            ];

            if ($game == MHT::GAME_MANHUNT_2){
                $entry['id'] = $binary->consume(4,  NBinary::INT_32);
            }
            $indexBlock[] = $entry;
        }

        //skip fourCC and blockSize
        $binary->current += 8;

        $results = [];

        foreach ($indexBlock as $entry) {
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

                $result['text'] .= "\x00\x00\x00";

                $result['text'] = iconv('UTF-16LE', 'UTF-8', $result['text']);
                $result['text'] = trim($result['text']);

                //MH2 only
                if (isset($entry['id'])){
                    $result['id'] = $entry['id'];
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
            $game = isset($records[0]['id']) ? MHT::GAME_MANHUNT_2 : MHT::GAME_MANHUNT;
        }

        $binary = new NBinary();

        if ($platform == MHT::PLATFORM_WII){
            $binary->numericBigEndian = true;
        }

        $binary->write('TKEY', NBinary::STRING);
        $binary->write(count($records) * ($game == MHT::GAME_MANHUNT ? 12 : 20), NBinary::INT_32);

        $data = new NBinary();
        $offsets = [];
        $offset = 0;

        foreach ($records as $index => $record) {
            $offsets[] = $offset;

            $utf16Text = mb_convert_encoding($record['text'], "utf-16");
            $utf16Text = bin2hex($utf16Text);
            $utf16Text = substr($utf16Text, 2);
            $utf16Text .= '000000';

            $data->write($utf16Text, NBinary::HEX);

            $offset += strlen($utf16Text) / 2;
        }

        foreach ($records as $index => $record) {
            $binary->write($offsets[$index], NBinary::INT_32);
            $binary->write($record['key'], NBinary::STRING);
            $binary->write($binary->getPadding("\x00", $game == MHT::GAME_MANHUNT ? 8 : 12, $record['key']), NBinary::BINARY);

            if (isset($record['id'])){
                $binary->write($record['id'], NBinary::INT_32);
            }
        }

        $binary->write('TDAT', NBinary::STRING);
        $binary->write($data->length(), NBinary::INT_32);


        $binary->concat($data);

        return $binary->binary;
    }


}