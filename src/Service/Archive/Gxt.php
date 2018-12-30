<?php
namespace App\Service\Archive;

use App\Service\NBinary;

class Gxt {


    public function unpack($data){

        $binary = new NBinary($data);

        $indexHeader = [
            'fourCC'    => $binary->consume(4, NBinary::STRING),
            'blockSize' => $binary->consume(4, NBinary::INT_32),
        ];

        $indexBlock = [];

        for( $i = 0; $i < $indexHeader['blockSize'] / 20; $i++ ){
            $indexBlock[] = [
                'offset' => $binary->consume(4,  NBinary::INT_32),
                'key'    => $binary->consume(12, NBinary::STRING),
                'id'     => $binary->consume(4,  NBinary::INT_32),
            ];
        }

        //skip fourCC and blockSize
        $binary->current += 8;

        $result = [];

        foreach ($indexBlock as $entry) {
            $binary->jumpTo($indexHeader['blockSize'] + $entry['offset'] + 16 );

            $result[] = [
                'id' => $entry['id'],
                'key' => $entry['key'],
                'text' => str_replace("\x00", "", $binary->getString("\x00\x00\x00", false))
            ];


        }

        return $result;
    }

    public function pack( $records ){

        $binary = new NBinary();
        $binary->write('TKEY', NBinary::STRING);
        $binary->write(count($records) * 20, NBinary::INT_32);

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
            $binary->write($binary->getPadding("\x00", 12, $record['key']), NBinary::BINARY);
            $binary->write($record['id'], NBinary::INT_32);
        }

        $binary->write('TDAT', NBinary::STRING);
        $binary->write($data->length(), NBinary::INT_32);


        $binary->concat($data);

        return $binary->binary;
    }


}