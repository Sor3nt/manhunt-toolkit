<?php

namespace App\Service\Archive;

use App\MHT;
use App\Service\CompilerV2\Manhunt2;
use App\Service\Helper;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;
//https://github.com/qaisjp/green-candy/blob/807e79bac9296225ab3c162b713f2461c1542e46/MTA10/game_sa/CParticleDataSA.cpp#L233

class Fxb extends Archive
{

    public $name = 'Effects';

    public static $supported = 'fxb';

    public $keyTable = [
        0x00000000, 0x77073096, 0xEE0E612C, 0x990951BA,
        0x076DC419, 0x706AF48F, 0xE963A535, 0x9E6495A3,
        0x0EDB8832, 0x79DCB8A4, 0xE0D5E91E, 0x97D2D988,
        0x09B64C2B, 0x7EB17CBD, 0xE7B82D07, 0x90BF1D91,
        0x1DB71064, 0x6AB020F2, 0xF3B97148, 0x84BE41DE,
        0x1ADAD47D, 0x6DDDE4EB, 0xF4D4B551, 0x83D385C7,
        0x136C9856, 0x646BA8C0, 0xFD62F97A, 0x8A65C9EC,
        0x14015C4F, 0x63066CD9, 0xFA0F3D63, 0x8D080DF5,
        0x3B6E20C8, 0x4C69105E, 0xD56041E4, 0xA2677172,
        0x3C03E4D1, 0x4B04D447, 0xD20D85FD, 0xA50AB56B,
        0x35B5A8FA, 0x42B2986C, 0xDBBBC9D6, 0xACBCF940,
        0x32D86CE3, 0x45DF5C75, 0xDCD60DCF, 0xABD13D59,
        0x26D930AC, 0x51DE003A, 0xC8D75180, 0xBFD06116,
        0x21B4F4B5, 0x56B3C423, 0xCFBA9599, 0xB8BDA50F,
        0x2802B89E, 0x5F058808, 0xC60CD9B2, 0xB10BE924,
        0x2F6F7C87, 0x58684C11, 0xC1611DAB, 0xB6662D3D,
        0x76DC4190, 0x01DB7106, 0x98D220BC, 0xEFD5102A,
        0x71B18589, 0x06B6B51F, 0x9FBFE4A5, 0xE8B8D433,
        0x7807C9A2, 0x0F00F934, 0x9609A88E, 0xE10E9818,
        0x7F6A0DBB, 0x086D3D2D, 0x91646C97, 0xE6635C01,
        0x6B6B51F4, 0x1C6C6162, 0x856530D8, 0xF262004E,
        0x6C0695ED, 0x1B01A57B, 0x8208F4C1, 0xF50FC457,
        0x65B0D9C6, 0x12B7E950, 0x8BBEB8EA, 0xFCB9887C,
        0x62DD1DDF, 0x15DA2D49, 0x8CD37CF3, 0xFBD44C65,
        0x4DB26158, 0x3AB551CE, 0xA3BC0074, 0xD4BB30E2,
        0x4ADFA541, 0x3DD895D7, 0xA4D1C46D, 0xD3D6F4FB,
        0x4369E96A, 0x346ED9FC, 0xAD678846, 0xDA60B8D0,
        0x44042D73, 0x33031DE5, 0xAA0A4C5F, 0xDD0D7CC9,
        0x5005713C, 0x270241AA, 0xBE0B1010, 0xC90C2086,
        0x5768B525, 0x206F85B3, 0xB966D409, 0xCE61E49F,
        0x5EDEF90E, 0x29D9C998, 0xB0D09822, 0xC7D7A8B4,
        0x59B33D17, 0x2EB40D81, 0xB7BD5C3B, 0xC0BA6CAD,
        0xEDB88320, 0x9ABFB3B6, 0x03B6E20C, 0x74B1D29A,
        0xEAD54739, 0x9DD277AF, 0x04DB2615, 0x73DC1683,
        0xE3630B12, 0x94643B84, 0x0D6D6A3E, 0x7A6A5AA8,
        0xE40ECF0B, 0x9309FF9D, 0x0A00AE27, 0x7D079EB1,
        0xF00F9344, 0x8708A3D2, 0x1E01F268, 0x6906C2FE,
        0xF762575D, 0x806567CB, 0x196C3671, 0x6E6B06E7,
        0xFED41B76, 0x89D32BE0, 0x10DA7A5A, 0x67DD4ACC,
        0xF9B9DF6F, 0x8EBEEFF9, 0x17B7BE43, 0x60B08ED5,
        0xD6D6A3E8, 0xA1D1937E, 0x38D8C2C4, 0x4FDFF252,
        0xD1BB67F1, 0xA6BC5767, 0x3FB506DD, 0x48B2364B,
        0xD80D2BDA, 0xAF0A1B4C, 0x36034AF6, 0x41047A60,
        0xDF60EFC3, 0xA867DF55, 0x316E8EEF, 0x4669BE79,
        0xCB61B38C, 0xBC66831A, 0x256FD2A0, 0x5268E236,
        0xCC0C7795, 0xBB0B4703, 0x220216B9, 0x5505262F,
        0xC5BA3BBE, 0xB2BD0B28, 0x2BB45A92, 0x5CB36A04,
        0xC2D7FFA7, 0xB5D0CF31, 0x2CD99E8B, 0x5BDEAE1D,
        0x9B64C2B0, 0xEC63F226, 0x756AA39C, 0x026D930A,
        0x9C0906A9, 0xEB0E363F, 0x72076785, 0x05005713,
        0x95BF4A82, 0xE2B87A14, 0x7BB12BAE, 0x0CB61B38,
        0x92D28E9B, 0xE5D5BE0D, 0x7CDCEFB7, 0x0BDBDF21,
        0x86D3D2D4, 0xF1D4E242, 0x68DDB3F8, 0x1FDA836E,
        0x81BE16CD, 0xF6B9265B, 0x6FB077E1, 0x18B74777,
        0x88085AE6, 0xFF0F6A70, 0x66063BCA, 0x11010B5C,
        0x8F659EFF, 0xF862AE69, 0x616BFFD3, 0x166CCF45,
        0xA00AE278, 0xD70DD2EE, 0x4E048354, 0x3903B3C2,
        0xA7672661, 0xD06016F7, 0x4969474D, 0x3E6E77DB,
        0xAED16A4A, 0xD9D65ADC, 0x40DF0B66, 0x37D83BF0,
        0xA9BCAE53, 0xDEBB9EC5, 0x47B2CF7F, 0x30B5FFE9,
        0xBDBDF21C, 0xCABAC28A, 0x53B39330, 0x24B4A3A6,
        0xBAD03605, 0xCDD70693, 0x54DE5729, 0x23D967BF,
        0xB3667A2E, 0xC4614AB8, 0x5D681B02, 0x2A6F2B94,
        0xB40BBE37, 0xC30C8EA1, 0x5A05DF1B, 0x2D02EF8D,
        0x67AE40
    ];

    public $nameMap = [
        'BURNFLA',
        'LBLOOD',
        'LBLOODA',
        'LBLOODF',
        'LBLOOD2',
        'LBLOOD4',
        'LBLOOD7',
        'LBLOODJ',
        'LBLOOD8',
        'LBLOODK',
        'LBLOODP',
        'BURN01',
        'BURN02',
        'BURN03',
        'BURN04',
        'BURN05',
        'BURN06',
        'BURN07',
        'BURN08',
        'BURNHAY',
        'FXRAIN',
        '6SHOOT',
        'GLOCK',
        'DEAGLE',
        'UZI',
        'SHOTGUN',
        'SNIPER1',
        'TRANQUIL',
        'COLTCOM',
        'DRUMEXPL',
        'EXPLSN',
        'FISTSH',
        'FISTSQ',
        'FLAGUN',
        'FLAREX',
        'EXTING',
        'BLADEH',
        'BLADEQ',
        'CONVEY',
        'KATANA',
        'LFLARE',
        'LSMOKE',
        'MANHOL',
        'PMPKIN',
        'PSIREN',
        'RAINCO',
        'RAINPA',
        'REDSIR',
        'SMOKEX',
        'SHARDS',
        'BLUESIREN',
        'BLOODX',
        'BLUESIREN',
        'DRUMEXPLODE',
        'FLM001',
        'FX000',
        'FX001',
        'FXB001',
        'FXB002',
        'FXB003',
        'FXB004',
        'FXB005',
        'FXB007',
        'FXBCERM',
        'FXBMUD',
        'FXBPAPE',
        'FXBPLAS',
        'FXBSTON',
        'FXBTMET',
        'FXBTMET2',
        'FXBTURF',
        'FXBWOOD',
        'FXE000',
        'FXP001',
        'FXP002',
        'FXP003',
        'FXPISS',
        'FXRAIN',
        'FXRAT1',
        'FXSAW1',
        'FXSMOK1',
        'FXSMOK3',
        'FXSMOK4',
        'FXSPARK',
        'FXGLASS',
        'FXWATER',
        'FXWCHIP',
        'CROSSBOW'
    ];

    private $hash2Name = [];

    public function __construct(){

        foreach ($this->nameMap as $item) {
            $this->nameMap[] = $item . '_X';
        }

        foreach ($this->nameMap as $name) {
            $hash = $this->getHash($name);
            $this->hash2Name[$hash] = $name;
        }
    }

    /**
     * @param $pathFilename
     * @param $input
     * @param $game
     * @param $platform
     * @return bool
     */
    public static function canPack($pathFilename, $input, $game, $platform)
    {
        return false;
    }

    private function getHash($name){

        $hash = 0xFFFFFFFF;

        $name = strtoupper($name);
        for ($i = 0; $i < strlen($name); $i++) {
            $key = ord($name[$i]) ^ ($hash & 0xFF);
            $hash = ($hash >> 8) ^ $this->keyTable[$key];
        }

        return $hash;
    }

    private function bruteforce($hashes){
        $pre = "FX";
        for($i = "aaaa"; 6 > strlen($i); $i++){

            for($x = 0; $x < 6; $x++){
                $hash = $this->getHash($pre . $i . $x);

                if (in_array($hash, $hashes) !== false){
                    echo strtoupper($pre . $i . $x) . "\n";
                }

            }

        }
exit;
        for($i = "aaaaaa"; 7 > strlen($i); $i++){

            $hash = $this->getHash($i);

            if (in_array($hash, $hashes) !== false){
                echo $i . "\n";
            }
        }

        exit;
    }

    /**
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return array
     */
    public function unpack(NBinary $binary, $game, $platform)
    {

        $fourCC = $binary->consume(4, NBinary::BINARY);
        $count = $binary->consume(4, NBinary::INT_32);
        $files = [];
        $missedHashes = [];
        $nameMissed = 0;
        $nameFound = 0;
        while($count--){
            $entry = $this->parseEntry($binary);
            $appendix = 0;
            while (isset($files[$entry['name'] . '_' . $appendix . '.json'])){
                $appendix++;
            }
            $files[$entry['name'] . '_' . $appendix . '.json'] = $entry;

            if (is_numeric($entry['name'])) {
                $missedHashes[] = $entry['name'];
                $nameMissed++;
            }
            else
                $nameFound++;

        }

//        $this->bruteforce($missedHashes);

        echo sprintf(
            "Percent: %s%% translated (%s/%s)\n",
            number_format(100 - ($nameMissed / ($nameFound+$nameMissed)) * 100, 2),
            $nameFound,
            ($nameFound+$nameMissed)
        );

//        var_dump($this->tmpMap);
//        exit;

        return $files;
    }

    private function parseEntry(NBinary $binary){
        $fourCC = $binary->consume(4, NBinary::BINARY);
        if ($fourCC !== "_ys_") die(__LINE__ . ' invalid');

        $hash = $binary->consume(4, NBinary::INT_32);
        $name = $hash;

        if (isset($this->hash2Name[$hash])){
            $name = $this->hash2Name[$hash];
        }
//        var_dump($hash );
//if($hash == $this->getHash("BURNFLA")) die("ss");
//exit;
        $length = $binary->consume(4, NBinary::FLOAT_32);
        $playmode = $binary->consume(1, NBinary::INT_8);
        $cullDist = $binary->consume(2, NBinary::INT_16);


        //boolean , is/has sphere, spere
        $hasBoundingSphere = $binary->consume(4, NBinary::INT_32);
        $boundingSphere = null;
        if ($hasBoundingSphere > 0){
            $unknownFloat2 = $binary->consume(4, NBinary::FLOAT_32);
            $boundingSphere = $binary->readXYZW(4, NBinary::FLOAT_32);

        }

        $numPrims = $binary->consume(1, NBinary::INT_8);
        $results = [];
        while($numPrims--){
            $result = [
                'blocks' => []
            ];

            $result['base'] = $this->parsePrimsFxEmitter($binary, $name);

            if ($binary->remain() < 4)
                die("missed 1");

            $fourCC = $binary->consume(4, NBinary::BINARY);
            if ($fourCC !== "_mi_") die(__LINE__ . ' invalid');

            if ($binary->remain() < 4)
                die("missed 2");

            $numInfos = $binary->consume(4, NBinary::INT_32);

            while($numInfos--){
                $block = $this->getBlock($binary);
//                if (!isset($result['infos'][$block['category']]))
//                    $result['infos'][$block['category']] = [];
//                $result['infos'][$block['category']][] = [ 'duration' => $block['duration'],  'values' => $block['data']];
                $result['infos'][] = $block;
            }

            $results[] = $result;
        }

//
        return [
            'hash' => $hash,
            'name' => $name,
            'length' => $length,
            'playmode' => $playmode,
            'cullDist' => $cullDist,
            'hasBoundingSphere' => $hasBoundingSphere,
            'sphere' => $boundingSphere ? [
                'bounding' => $boundingSphere,
                'unknownFloat2' => $unknownFloat2
            ] : [],
            'prims' => $results
        ];
    }

    private $tmpMap = [
        '_fi_' => [],
        '_4i_' => [],
        '_ui_' => [],
        '_si_' => [],
    ];

    private function getBlock(NBinary $binary){
        $fxInfoId = $binary->consume(2, NBinary::INT_16);

        $fourCC = $binary->consume(4, NBinary::BINARY);

        //byte 2
        $isComplexValue = $binary->consume(4, NBinary::INT_32);

        //byte 6
        $factor = $binary->consume(1, NBinary::INT_8);
        //byte 7
        $zeroShort = $binary->consume(1, NBinary::INT_8);

        //byte 8
        $zeroShort2 = $binary->consume(1, NBinary::INT_8);

    //        if ($zeroShort2 === 1) die("J");
        $duration = [];
        if ($factor > 1){

            $c = $factor;
            while($c-- - 1){
                $duration[] = $binary->consume(2 , NBinary::INT_16) / 256;

            }
        }

        //byte 9
        $count = $binary->consume(1, NBinary::INT_8);
        $entries = $count * $factor;
        $data = [];


        $dataStart = $binary->current;

        while($entries--){

            if ($fourCC === "_fi_"){
                $data[] = (float)$binary->consume(4, NBinary::FLOAT_32);
            }else if ($fourCC === "_ui_"){
//                $data[] = $binary->consume(1, NBinary::U_INT_8);
//                $data[] = $binary->consume(1, NBinary::U_INT_8);
                $data[] = $binary->consume(2, NBinary::INT_16);
            }else if ($fourCC === "_4i_"){
                $value = $binary->consume(2, NBinary::INT_16); // / 1000;
                if ($isComplexValue === 0) {
                    $value /= 1000;
                }
                $data[] = $value;
            }else if ($fourCC === "_si_"){
                $data[] = $binary->consume(2, NBinary::INT_16);
            }else{
                die("unknown fourcc " . $fourCC);
            }
        }

        //see wii=> 801FDED8
        $category = "unknown";
        switch ($fxInfoId){
            case 8224:
                $category = "FX_INFO_GROUNDCOLLIDE_DATA";
                break;
            case 16400:
                $category = "FX_INFO_TEXCOORDS_DATA";
                break;
            case 16512:
                $category = "FX_INFO_ANIMTEXTURE_DATA";
                break;
            case 16896:
                $category = "FX_INFO_ROTATEOFFSET_DATA";
                break;
            case 16640:
                $category = "FX_INFO_COLOURRANGE_DATA";
                break;
            case 16448:
                $category = "FX_INFO_DIR_DATA";
                break;
            case 16416:
                $category = "FX_INFO_FLAT_DATA";
                break;
            case 16386:
                $category = "FX_INFO_SIZE_DATA";
                break;
            case 16392:
                $category = "FX_INFO_ROTATE_DATA";
                break;
            case 16388:
                $category = "FX_INFO_SPRITERECT_DATA";
                break;
            case 8320:
                $category = "FX_INFO_JITTER_DATA";
                break;
            case 16385:
                $category = "FX_INFO_COLOUR_DATA";
                $data = array_map(function ($entry){
                    return $entry / 255;
                }, $data);
                break;
            case 8256:
                $category = "FX_INFO_WIND_DATA";
                break;
            case 4160:
                $category = "FX_INFO_EMLIFE_DATA";

                if (count($data) !== 2)
                    break;

                $data = [
                    'life' => $data[0] / 255,
                    'lifeBias' => $data[1] / 255
                ];

                break;
            case 8196:
                $category = "FX_INFO_FRICTION_DATA";
                break;
            case 8208:
                $category = "FX_INFO_ATTRACTLINE_DATA";
                break;
            case 8200:
                $category = "FX_INFO_ATTRACTPT_DATA";
                break;
            case 8193:
                $category = "FX_INFO_NOISE_DATA";
                break;
            case 8194:
                $category = "FX_INFO_FORCE_DATA";

                if (count($data) !== 3)
                    break;

                $data = [
                    'x' => $data[0],
                    'y' => $data[1],
                    'z' => $data[2],
                ];

                break;
            case 4224:
                $category = "FX_INFO_EMPOS_DATA";
                break;
            case 4104:
                $category = "FX_INFO_EMSPEED_DATA";
                break;


            case 4112:
                $category = "FX_INFO_EMDIR_DATA";

                if (count($data) !== 3)
                    break;

                $data = [
                    'x' => $data[0],
                    'y' => $data[1],
                    'z' => $data[2],
                ];

                break;
            case 4100:
                $category = "FX_INFO_EMSIZE_DATA";
                break;
            case 4097:
                $category = "FX_INFO_EMRATE_DATA";
                break;
            case 4128:
                $category = "FX_INFO_EMANGLE_DATA";

                if (count($data) !== 2)
                    break;

                $data = [
                    'angleMin' => $data[0],
                    'angleMax' => $data[1],
                ];


//                if (count($data) === 2){
//                }

                break;

        }
        var_dump($count . " " . $factor . ' ' . $category);

        if (in_array($category, $this->tmpMap[$fourCC]) === false){
            $this->tmpMap[$fourCC][] = $category;
        }

//        $data = array_chunk($data, $factor);


        return [
            'category' => $category,
            'someBool' => $isComplexValue,
            'zeroShort' => $zeroShort,
            'someBool2' => $zeroShort2,
            'duration' => $duration,
            'data' => $data
        ];


    }

    private function getName(NBinary $binary){
        $size = $binary->consume(4, NBinary::INT_32);
        return $binary->consume($size, NBinary::STRING);

    }

    private function parsePrimsFxEmitter(NBinary $binary, $name){
        $fourCC = $binary->consume(4, NBinary::BINARY);
        if ($fourCC !== "_me_") die(__LINE__ . ' invalid ' . $fourCC);

        $unknownData = $binary->consume(2, NBinary::INT_16);
        $unknownShort = $binary->consume(3, NBinary::INT_16);

        $srcBlendId = $binary->consume(1, NBinary::INT_8);
        $dstBlendId = $binary->consume(1, NBinary::INT_8);
        $alphaOn = $binary->consume(1, NBinary::INT_8);

        $unknownData2 = $binary->consume(3, NBinary::HEX);
        if ($unknownData2 !== "000000") die("invalid");
        $unknownFlag = $binary->consume(4, NBinary::INT_32);

        $unknownData3 = false;
        if ($unknownFlag > 0){
            //4 floats ?
            $unknownData3 = [
                $binary->consume(4, NBinary::INT_32),
                $binary->consume(4, NBinary::INT_32),
                $binary->consume(4, NBinary::INT_32),
                $binary->consume(4, NBinary::INT_32),
                $binary->consume(4, NBinary::INT_32),
                $binary->consume(4, NBinary::INT_32),
            ];
//            $unknownData3 = $binary->consume(3 * 8, NBinary::HEX);
        }

        $textureId1 = $binary->consume(4, NBinary::INT_32);
        $textureId2 = $binary->consume(4, NBinary::INT_32);
        $textureId3 = $binary->consume(4, NBinary::INT_32);
        $textureId4 = $binary->consume(4, NBinary::INT_32);


        $textures = [$this->getName($binary)];
        if ($textureId2 > 0) $textures[] = $this->getName($binary);
        if ($textureId3 > 0) $textures[] = $this->getName($binary);
        if ($textureId4 > 0) $textures[] = $this->getName($binary);

        return [
            'unknownData' => $unknownData,
//            'unknownData2' => $unknownData2,
            'unknownFlag' => $unknownFlag,
            'unknownData3' => $unknownData3,
            'unknownShort' => $unknownShort,
            'srcBlendId' => $srcBlendId,
            'dstBlendId' => $dstBlendId,
            'alphaOn' => $alphaOn,
            'textures' => $textures
        ];

    }

    /**
     * @param Finder $pathFilename
     * @param $game
     * @param $platform
     * @return null|string
     */
    public function pack($pathFilename, $game, $platform)
    {
    }


}
