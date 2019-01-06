<?php
namespace App\Service\Archive;

use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Pak extends Archive {

    public $name = 'Manhunt Data Container';

    public static $supported = 'pak';



    /**
     * @param $pathFilename
     * @param Finder $input
     * @param null $game
     * @return bool
     */
    public static function canPack( $pathFilename, $input, $game = null ){

        if (!$input instanceof Finder) return false;

        foreach ($input as $file) {
            if ($file->getFilename() == "WEATHER.INI") return true;
        }

        return false;
    }

    private function xorCrypt($text) {

        $key = "\x7f";
        $result = '';

        for($i=0; $i<strlen($text); )
        {
            for($j=0; ($j<strlen($key) && $i<strlen($text)); $j++,$i++)
            {
                $result .= $text{$i} ^ $key{$j};
            }
        }

        return $result;
    }

    public function unpack(NBinary $binary, $game = null){

        $count = $binary->consume(4, NBinary::INT_32, 8);

        $entries = [];

        // read the entries from index block
        for($i = 0; $i < $count; $i++){

            $entries[] = [
                'name'    => $binary->consume(260, NBinary::STRING),
                'size'    => $binary->consume(4,   NBinary::INT_32),
                'offset'  => $binary->consume(4,   NBinary::INT_32),
                'unknown' => $binary->consume(4,   NBinary::INT_32),
                'crc2'    => $binary->consume(4,   NBinary::INT_32)
            ];
        }

        // read the actual file content
        foreach ($entries as &$entry) {
            $binary->jumpTo($entry['offset']);

            $entry['data'] = $binary->consume($entry['size'], NBinary::BINARY);

            //encrypt the content
            $entry['data'] = $this->xorCrypt($entry['data']);

        }

        $results = [];

        foreach ($entries as $entry) {
            $results[ substr($entry['name'], 2) ] = $entry['data'];
        }

        return $results;
    }

    private function prepareData( Finder $finder){
        $files = [];

        foreach ($finder as $file) {
            $files[ $file->getRelativePath() ] = $file->getContents();
        }

        return $files;
    }

    /**
     * @param Finder $files
     * @param null $game
     * @return null|string
     */
    public function pack( $files, $game = null ){

        $files = $this->prepareData($files);

        $binary = new NBinary();

        $binary->write('MHPK', NBinary::STRING);
        $binary->write('00000200', NBinary::HEX);

        $binary->write(count($files), NBinary::INT_32);

        $data = new NBinary();

        //start is [header] + [entries * index block size]
        $offset = 12 + (count($files) * 276);

        foreach ($files as $fileName => $content) {

            //crypt the content
            $contentXor = $this->xorCrypt($content);

            $data->write($contentXor, NBinary::BINARY);

            /**
             * a block has a size of 260+4+4+4+4 == 276
             */
            $binary->write($fileName, NBinary::STRING);
            $binary->write(
                $binary->getPadding("\x00", 260, $fileName),
                NBinary::BINARY
            );

            $binary->write(strlen($contentXor), NBinary::INT_32);
            $binary->write($offset, NBinary::INT_32);

            //add unknown (active/inactive flag?)
            $binary->write(1, NBinary::INT_32);

            //crc is wrong, crc based on the encrypted content ?
            $binary->write($data->pack(crc32($content), NBinary::INT_32), NBinary::INT_32);

            $offset += strlen($content);
        }

        $binary->concat($data);

        return $binary->binary;
    }
}