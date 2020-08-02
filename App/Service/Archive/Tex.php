<?php
namespace App\Service\Archive;

use App\MHT;
use App\Service\Archive\Textures\Image;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

/**
 * Class Tex
 * Todos:
 * - Add MipMap Support (do we need this ? maybe for packing ?)
 * - Add Alpha handling
 */
class Tex extends Archive {

    public $name = 'Textures';

    public static $supported = 'tex';

    /**
     * @param $pathFilename
     * @param $input
     * @param $game
     * @param $platform
     * @return bool
     */
    public static function canPack( $pathFilename, $input, $game, $platform ){

        if (!$input instanceof Finder) return false;
        if($input->files()->count() == 0) return false;

        foreach ($input as $file) {
            $extension = strtolower($file->getExtension());

            if ($extension !== "dds") return false;
        }

        return true;
    }

    private function parseHeader( NBinary &$binary ){

        return [
            'magic'             => $binary->consume(4,  NBinary::STRING),
            'constNumber'       => $binary->consume(4,  NBinary::INT_32),
            'fileSize'          => $binary->consume(4,  NBinary::INT_32),
            'indexTableOffset'  => $binary->consume(4,  NBinary::INT_32),
            'indexTableOffset2' => $binary->consume(4,  NBinary::INT_32),
            'numIndex'          => $binary->consume(4,  NBinary::INT_32),
            'unknown'           => $binary->consume(8,  NBinary::HEX),
            'numTextures'       => $binary->consume(4,  NBinary::INT_32),
            'firstOffset'       => $binary->consume(4,  NBinary::INT_32),
            'lastOffset'       => $binary->consume(4,  NBinary::INT_32)
        ];


    }

    private function parseTexture( $startOffset, NBinary &$binary ){

        $binary->jumpTo($startOffset);

        $texture = [
            'nextOffset'        => $binary->consume(4,  NBinary::INT_32),
            'prevOffset'        => $binary->consume(4,  NBinary::INT_32),
            'name'              => $binary->consume(32, NBinary::STRING),
            'alphaFlags'        => $binary->consume(32, NBinary::HEX),
            'width'             => $binary->consume(4,  NBinary::INT_32),
            'height'            => $binary->consume(4,  NBinary::INT_32),
            'bitPerPixel'       => $binary->consume(4,  NBinary::INT_32),
            'pitchOrLinearSize' => $binary->consume(4,  NBinary::INT_32),
            'flags'             => $binary->consume(4,  NBinary::HEX),
            'mipMapCount'       => $binary->consume(1,  NBinary::INT_8),
            'unknown'           => $binary->consume(3,  NBinary::HEX),
            'dataOffset'        => $binary->consume(4,  NBinary::INT_32),
            'paletteOffset'     => $binary->consume(4,  NBinary::INT_32),
            'size'              => $binary->consume(4,  NBinary::INT_32),
            'unknown2'          => $binary->consume(4, NBinary::HEX)
        ];

        $binary->jumpTo($texture['dataOffset']);
        $texture['data'] = $binary->consume($texture['size'], NBinary::BINARY);

        return $texture;
    }


    /**
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return array
     * @throws \Exception
     */
    public function unpack(NBinary $binary, $game, $platform){


        $header = $this->parseHeader($binary);

        $currentOffset = $header['firstOffset'];

        $textures = [];
        while($header['numTextures'] > 0) {
            $texture = $this->parseTexture($currentOffset, $binary);

            if ($texture['width'] <= 2 && $texture['height'] <= 2){
                $currentOffset = $texture['nextOffset'];

                $header['numTextures']--;
                continue;
            }

            $textures[$texture['name'] . ".dds"] = $texture['data'];

            $currentOffset = $texture['nextOffset'];

            $header['numTextures']--;
        }


        echo "Found " . count($textures) . " Textures!\n";


        return $textures;
    }











    var $offsets = [];
    var $dataOffsets = [];
    var $tableOffsets = [];

    /**
     * @param $data
     * @param $game
     * @param $platform
     */
    public function pack($data, $game, $platform ){

        $files = $this->prepareData($data);

        $result = new NBinary();

        $this->writeHeader($result, count($files));

        $dataSize = $this->writeTexturesData($result, $files);

        $this->writeTexturesHeader($result, $files);

        $tableOffset = $result->current;
        $this->writeTexturesIndexTable($result);

        $size = 48; //header size
        $size += 16; //header empty line
        $size += $dataSize;

        $size += 112 * count($files); //112 tex header
        $size += 16 * count($files); //empty line per tex header
//
        $size += (count($files) * 4 * 3) + (4 * 2); // table size

        $result->current = $this->offsets['fileSize'];
        $result->overwrite($size, NBinary::INT_32);

        $result->current = $this->offsets['firstOffset'];
        $result->overwrite($this->tableOffsets[0]['next'], NBinary::INT_32);

        $result->current = $this->offsets['lastOffset'];
        $result->overwrite($this->tableOffsets[count($this->tableOffsets) - 1]['next'], NBinary::INT_32);

        $result->current = $this->offsets['indexTableOffset'];
        $result->overwrite($tableOffset, NBinary::INT_32);
        $result->current = $this->offsets['indexTableOffset2'];
        $result->overwrite($tableOffset, NBinary::INT_32);


        return $result->binary;
    }


    private function prepareData( $finder ){

        if ($finder instanceof Finder){
            $files = [];
            foreach ($finder as $file) {
                $files[$file->getFilenameWithoutExtension()] = $file->getContents();
            }

            return $files;
        }

        return $finder;
    }

    private function writeHeader(NBinary $output, $count){
        $output->write('TCDT', NBinary::STRING);
        $output->write(1, NBinary::INT_32);

        $this->offsets['fileSize'] = $output->current;
        $output->write(1234, NBinary::INT_32);

        $this->offsets['indexTableOffset'] = $output->current;
        $output->write(1234, NBinary::INT_32);

        $this->offsets['indexTableOffset2'] = $output->current;
        $output->write(1234, NBinary::INT_32);

        //numIndex
        //12 bytes per entry + 8 bytes header entry
        $output->write(($count * 3) + 2 , NBinary::INT_32);


        $output->write(0, NBinary::INT_32);
        $output->write(0, NBinary::INT_32);

        $output->write($count, NBinary::INT_32);

        $this->offsets['firstOffset'] = $output->current;
        $output->write(1234, NBinary::INT_32);

        $this->offsets['lastOffset'] = $output->current;
        $output->write(1234, NBinary::INT_32);

        $output->write(0, NBinary::INT_32);

        //empty line to fill the block (padding?)
        $output->write(0, NBinary::INT_32);
        $output->write(0, NBinary::INT_32);
        $output->write(0, NBinary::INT_32);
        $output->write(0, NBinary::INT_32);

    }

    private function writeTexturesData(NBinary $output, $files){
        $size = 0;
        foreach ($files as $file) {
            $this->dataOffsets[] = $output->current;
            $output->write($file, NBinary::BINARY);

            $size += \mb_strlen($file, '8bit');
            if ($size % 16 !== 0){

                $size += $size % 16;
                $output->write(
                    $output->getPadding("\x00", 16),
                    NBinary::BINARY
                );
            }

        }

        return $size;
    }

    private function writeTexturesHeader(NBinary $output, $files){
        $index = 0;

        foreach ($files as $name => $data) {
            $this->tableOffsets[$index] = [];

            $data = new NBinary($data);
            $ddsHandler = new Dds();
            $ddsHeader = $ddsHandler->readHeader($data);

            $nextOffset = $output->current + 128;
            $this->tableOffsets[$index]['next'] = $output->current;
            if (count($files) == $index + 1){
                $output->write(36, NBinary::INT_32);
            }else{
                $output->write($nextOffset, NBinary::INT_32);
            }

            //prevOffset
            $this->tableOffsets[$index]['prev'] = $output->current;
            if ($index === 0){
                $output->write(36, NBinary::INT_32);
            }else{
                $output->write($nextOffset - 128, NBinary::INT_32);
            }

            $output->write(str_pad($name, 32, "\x00"), NBinary::STRING);

            if ($ddsHeader['format'] == "DXT5"){
                $output->write(str_pad("\x01", 32, "\x00"), NBinary::STRING);
            }else{
                $output->write(str_pad("\x00", 32, "\x00"), NBinary::STRING);

            }

            $output->write($ddsHeader['width'], NBinary::INT_32);
            $output->write($ddsHeader['height'], NBinary::INT_32);

            if ($ddsHeader['format'] == "DXT5") {
                $output->write(8, NBinary::INT_32);
            }else{
                $output->write(4, NBinary::INT_32);
            }

            $output->write($ddsHeader['pitchOrLinearSize'] / $ddsHeader['width'], NBinary::INT_32);

            //flags
            if ($ddsHeader['format'] == "DXT5") {
                $output->write("\x10\x00\x00\x00", NBinary::BINARY);
            }else{
                $output->write("\x08\x00\x00\x00", NBinary::BINARY);
            }

            $output->write($ddsHeader['mipMapCount'], NBinary::INT_32);

            //dataOffset
            $this->tableOffsets[$index]['data'] = $output->current;
            $output->write($this->dataOffsets[$index], NBinary::INT_32);

            //paletteOffset (unused ?)
            $output->write(0, NBinary::INT_32);


            $output->write($data->length(), NBinary::INT_32);

            $output->write(0, NBinary::INT_32);

            //empty line, looks like the mh2 parser need it
            $output->write(0, NBinary::INT_32);
            $output->write(0, NBinary::INT_32);
            $output->write(0, NBinary::INT_32);
            $output->write(0, NBinary::INT_32);


            $index++;
        }

    }

    private function writeTexturesIndexTable(NBinary $output){

        $output->write($this->offsets['firstOffset'], NBinary::INT_32);
        $output->write($this->offsets['lastOffset'], NBinary::INT_32);

        foreach ($this->tableOffsets as $tableOffset) {
            $output->write($tableOffset['next'], NBinary::INT_32);
            $output->write($tableOffset['prev'], NBinary::INT_32);
            $output->write($tableOffset['data'], NBinary::INT_32);
        }

    }

}