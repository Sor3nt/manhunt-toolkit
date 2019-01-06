<?php
namespace App\Service\Archive;

use App\Service\NBinary;

class Gxt extends Archive {
    public $name = 'Text Translation';

    public static $supported = 'gxt';

    /**
     * @param $pathFilename
     * @param NBinary $input
     * @param null $game
     * @return bool
     */
    public static function canPack( $pathFilename, $input, $game = null ){

        if (!$input instanceof NBinary) return false;

        if (
            strpos($input->binary, 'key') !== false &&
            strpos($input->binary, 'text') !== false)
            return true;

        return false;
    }

    public function unpack(NBinary $binary, $game = null){

        $indexHeader = [
            'fourCC'    => $binary->consume(4, NBinary::STRING),
            'blockSize' => $binary->consume(4, NBinary::INT_32),
        ];

        $indexBlock = [];


        $test = $binary->consume(8, NBinary::STRING, 16);

        if (ctype_alnum($test)){
            $game = "mh1";
        }else{
            $game = "mh2";
        }

        $binary->jumpTo(8);

        for( $i = 0; $i < $indexHeader['blockSize'] / ($game == 'mh1' ? 12 : 20); $i++ ){
            $entry = [
                'offset' => $binary->consume(4,  NBinary::INT_32),
                'key'    => $binary->consume($game == 'mh1' ? 8 : 12, NBinary::STRING)
            ];

            if ($game == 'mh2'){
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
                    'text' => str_replace("\x00", "", $binary->getString("\x00\x00\x00", false))
                ];

                //MH2 only
                if (isset($entry['id'])){
                    $result['id'] = $entry['id'];
                }

                $results[] = $result;
            }

        }


        return $results;
    }

    public function pack( $records, $game = null ){

        $records = \json_decode($records->binary, true);

        $game = isset($records[0]['id']) ? "mh2" : "mh1";

        $binary = new NBinary();
        $binary->write('TKEY', NBinary::STRING);
        $binary->write(count($records) * ($game == "mh1" ? 12 : 20), NBinary::INT_32);

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
            $binary->write($binary->getPadding("\x00", $game == "mh1" ? 8 : 12, $record['key']), NBinary::BINARY);

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