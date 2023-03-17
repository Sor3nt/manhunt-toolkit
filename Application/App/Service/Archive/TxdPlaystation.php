<?php
namespace App\Service\Archive;

use App\MHT;
use App\Service\Archive\Textures\Playstation;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class TxdPlaystation extends Archive {
    public $name = 'Textures (PS2/PSP)';

    public static $validationMap = [
        [0, 4, NBinary::HEX, ['54434454']]
    ];

    private $playstation;

    public function __construct()
    {
        $this->playstation = new Playstation();
    }

    public $asRaw = false;


    /**
     * @param $pathFilename
     * @param $input
     * @param $game
     * @param $platform
     * @return bool
     */
    public static function canPack( $pathFilename, $input, $game, $platform ){

        if (!$input instanceof Finder) return false;

        foreach ($input as $file) {
            if ($file->getExtension() == "json"){
                return strpos($file->getContents(), 'rasterFormat') !== false;
            }
        }

        return false;
    }

    private function parseHeader( NBinary &$binary ){

        //44 bytes
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
            'lastTOffset'       => $binary->consume(4,  NBinary::INT_32)
        ];

    }

    private function parseTextureHeader( NBinary $binary ){
        //104 bytes
        return [
            'nextOffset'        => $binary->consume(4,  NBinary::INT_32),
            'prevOffset'        => $binary->consume(4,  NBinary::INT_32),
            'name'              => $binary->consume(64, NBinary::BINARY),

            'width'             => $binary->consume(4,  NBinary::INT_32),
            'height'            => $binary->consume(4,  NBinary::INT_32),
            'bitPerPixel'       => $binary->consume(4,  NBinary::INT_32),
            'rasterFormat'      => $binary->consume(4,  NBinary::HEX),

            'pixelFormat'       => $binary->consume(4,  NBinary::INT_32),
            'numMipLevels'      => $binary->consume(1,  NBinary::U_INT_8),
            'swizzleMask'       => $binary->consume(1,  NBinary::U_INT_8),
            'pPixel'            => $binary->consume(1,  NBinary::U_INT_8),
            'renderPass'        => $binary->consume(1,  NBinary::U_INT_8),

            'dataOffset'        => $binary->consume(4,  NBinary::INT_32),
            'paletteOffset'     => $binary->consume(4,  NBinary::INT_32),
        ];
    }

    private function parseTexture( $startOffset, NBinary $binary ){

        $binary->jumpTo($startOffset);

        $texture = $this->parseTextureHeader($binary);

        $texture['name'] = $binary->unpack($texture['name'], NBinary::STRING);

        $texture['palette'] = false;

        if ($texture['paletteOffset'] > 0){
            $binary->jumpTo($texture['paletteOffset']);
            $texture['palette'] = $binary->consume($this->playstation->getPaletteSize($texture['rasterFormat'], $texture['bitPerPixel']), NBinary::BINARY);
        }

        $binary->jumpTo($texture['dataOffset']);

        if ($texture['width'] == 1) {
            $texture['data'] = false;
            return $texture;
        }

        $texture['data'] = $binary->consume(
            $this->playstation->getRasterSize($texture['rasterFormat'], $texture['width'], $texture['height'], $texture['bitPerPixel']),
            NBinary::BINARY
        );

        return $texture;
    }

    public function packRaw() {

    }

    public function unpackRaw(NBinary $binary, $game, $platform) {
        $header = $this->parseHeader($binary);

        $currentOffset = $header['firstOffset'];
        $results = [];

        while($header['numTextures'] > 0) {

            $result = [];
            $binary->jumpTo($currentOffset);

            $texture = $this->parseTextureHeader($binary);
            $texture['name'] = $binary->unpack($texture['name'], NBinary::STRING);

            $result['header'] = $texture;

            if ($texture['paletteOffset'] > 0){
                $binary->jumpTo($texture['paletteOffset']);

                $result['palette'] = $binary->consume(
                    $this->playstation->getPaletteSize($texture['rasterFormat'], $texture['bitPerPixel']),
                    NBinary::HEX
                );
            }

            $binary->jumpTo($texture['dataOffset']);

            $result['data'] = $binary->consume(
                $this->playstation->getRasterSize($texture['rasterFormat'], $texture['width'], $texture['height'], $texture['bitPerPixel']),
                NBinary::HEX
            );

            $currentOffset = $result['header']['nextOffset'];
            unset($result['header']['nextOffset']);
            unset($result['header']['prevOffset']);
            unset($result['header']['paletteOffset']);
            unset($result['header']['dataOffset']);
            unset($result['header']['padding']);

            $results[$texture['name'] === "" ? "__empty__" : $texture['name']] = $result;
            $header['numTextures']--;
        }

        return $results;
    }

    public function unpack(NBinary $binary, $game, $platform){


        if ($this->asRaw)
            return $this->unpackRaw($binary, $game, $platform);

        $header = $this->parseHeader($binary);
        $currentOffset = $header['firstOffset'];

        $textures = [];
        while($header['numTextures'] > 0) {
            $texture = $this->parseTexture($currentOffset, $binary);

            if ($texture['data'] !== false){
                $bmpRgba = $this->playstation->convertToRgba($texture, $platform);
                $image = $this->playstation->rgbaToImage($bmpRgba, $texture['width'],$texture['height']);

                $textures[$texture['name'] . '.png'] = $image;
            }

            $currentOffset = $texture['nextOffset'];

            $header['numTextures']--;
//            exit;
        }

        return $textures;
    }

    public function pack($textures, $game, $platform){

        /** @var Finder $textures */
        $textures->sortByName();

        /**
         * @type Finder $textures
         */

        $binary = new NBinary();

        $headerOffsets = [];
        $indexTable = [];

        //Create header
        {
            $binary->write('TCDT', NBinary::STRING);
            $binary->write(1, NBinary::INT_32);

            $headerOffsets['fileSize'] = $binary->current;
            $binary->write(12345, NBinary::INT_32);

            $headerOffsets['indexTableOffset'] = $binary->current;
            $binary->write(12345, NBinary::INT_32);

            $headerOffsets['indexTable2Offset'] = $binary->current;
            $binary->write(12345, NBinary::INT_32);

            $headerOffsets['numIndex'] = $binary->current;
            $binary->write(12345, NBinary::INT_32);

            //unknown
            $binary->write("\x00\x00\x00\x00", NBinary::BINARY);
            $binary->write("\x00\x00\x00\x00", NBinary::BINARY);

            //numTextures
            $binary->write(count($textures), NBinary::INT_32);

            $headerOffsets['firstOffset'] = $binary->current;
            $indexTable[] = $binary->current;
            $binary->write(12345, NBinary::INT_32);

            $headerOffsets['lastOffset'] = $binary->current;
            $indexTable[] = $binary->current;
            $binary->write(12345, NBinary::INT_32);

            //unknown
            $binary->write("\x00\x00\x00\x00", NBinary::BINARY);
        }

        $latestStartOffset = 0;
        $index = 0;

        foreach ($textures as $file) {
            if ($file->getExtension() !== "json")
                continue;

            $textureRaw = \json_decode($file->getContents(), true);


            $startOfEntry = $binary->current;
            echo "Create new Texture Header at " . $startOfEntry . "\n";
            //Create texture header
            {
                $offsets = [];

                $offsets['next'] = $binary->current;
                $indexTable[] = $offsets['next'];
                $binary->write(12345, NBinary::INT_32);

                $offsets['prev'] = $binary->current;
                $indexTable[] = $offsets['prev'];
                $binary->write(12345, NBinary::INT_32);

                $binary->write(str_pad($textureRaw['header']['name'], 64, "\x00"), NBinary::STRING);
                $binary->write($textureRaw['header']['width'], NBinary::INT_32);
                $binary->write($textureRaw['header']['height'], NBinary::INT_32);
                $binary->write($textureRaw['header']['bitPerPixel'], NBinary::INT_32);
                $binary->write($textureRaw['header']['rasterFormat'], NBinary::HEX);
                $binary->write($textureRaw['header']['pixelFormat'], NBinary::INT_32);
                $binary->write($textureRaw['header']['numMipLevels'], NBinary::U_INT_8);
                $binary->write($textureRaw['header']['swizzleMask'], NBinary::U_INT_8);
                $binary->write($textureRaw['header']['pPixel'], NBinary::U_INT_8);
                $binary->write($textureRaw['header']['renderPass'], NBinary::U_INT_8);


                $offsets['data'] = $binary->current;
                $binary->write(112345, NBinary::INT_32);

                $offsets['palette'] = $binary->current;
                $binary->write(112345, NBinary::INT_32);
                $binary->write("\x00\x00\x00\x00", NBinary::BINARY);
                $binary->write("\x00\x00\x00\x00", NBinary::BINARY);


                $indexTable[] = $offsets['data'];
                $indexTable[] = $offsets['palette'];

                $paletteOffset = $binary->current;
                $binary->write($textureRaw['palette'], NBinary::HEX);

                $dataOffset = $binary->current;
                $binary->write($textureRaw['data'], NBinary::HEX);

            }
            $endOfEntry = $binary->current;

            //Write offsets for this texture
            {

                echo "Update Data Offset to " . $dataOffset . " at " . $offsets['data'] . "\n";
                $binary->current = $offsets['data'];
                $binary->overwrite($dataOffset, NBinary::INT_32);

                $binary->current = $offsets['palette'];
                $binary->overwrite($paletteOffset, NBinary::INT_32);

                $binary->current = $offsets['next'];
                if ($index === count($textures) - 1) {
                    $binary->overwrite(36, NBinary::INT_32);

                    $binary->current = $headerOffsets['lastOffset'];
                    $binary->overwrite($startOfEntry, NBinary::INT_32);

                }else{
                    //note: the end is also the start for next entry
                    $binary->overwrite($endOfEntry, NBinary::INT_32);
                }

                $binary->current = $offsets['prev'];
                if ($index === 0) {
                    $binary->overwrite(36, NBinary::INT_32);


                    $binary->current = $headerOffsets['firstOffset'];
                    $binary->overwrite($startOfEntry, NBinary::INT_32);

                }else{
                    $binary->overwrite($latestStartOffset, NBinary::INT_32);
                }

                $binary->current = $endOfEntry;

            }

            $latestStartOffset = $startOfEntry;
            $index++;
        }

        $binary->current = $binary->length();

        $tableOffset = $binary->current;
        foreach ($indexTable as $offset) {
            $binary->write($offset, NBinary::INT_32);
        }

        $binary->current = $headerOffsets['indexTableOffset'];
        $binary->overwrite($tableOffset, NBinary::INT_32);
        $binary->current = $headerOffsets['indexTable2Offset'];
        $binary->overwrite($tableOffset, NBinary::INT_32);

        $binary->current = $headerOffsets['numIndex'];
        $binary->overwrite(count($indexTable), NBinary::INT_32);


        $binary->current = $headerOffsets['fileSize'];
        $binary->overwrite($binary->length(), NBinary::INT_32);

        return $binary->binary;

    }
}