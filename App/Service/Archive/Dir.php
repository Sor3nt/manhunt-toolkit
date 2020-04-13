<?php

namespace App\Service\Archive;

use App\MHT;
use App\Service\CompilerV2\Manhunt2;
use App\Service\Helper;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Dir extends Archive
{

    public $name = 'Audio Names';

    public static $supported = 'dir';

    public $possibleLevelNames = [
        "a01_escape_asylum",
        "a02_the_old_house",
        "a04_sm_nightclub",
        "a07_tolerance_zone",
        "a14_sugarfactory",
        "a07_2tolerance_zone",
        "a10_brothel",
        "a12_plaza",
        "a06_cia_trap",
        "a09_burn",
        "a11_medicine_lab",
        "a16_tv_studio",
        "a17_creepy_farm",
        "a03_neighbourhood",
        "a15_cemetery",
        "a18_manor",
    ];

    public $crc32Hashes = [];

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


    public function preCalculateHashes($game)
    {

        $speechWavName = $game == MHT::PLATFORM_WII ? 'wii_stream' : 'pc_stream';

        $speechList = explode("\n", file_get_contents(__DIR__ . '/DIR-Speech-Names.txt'));
        foreach ($this->possibleLevelNames as $levelName) {
            foreach ($speechList as $speech) {
                $hashName = sprintf("scripted\%s\%s\%s.wav", $levelName, $speech, $speechWavName);
                $this->crc32Hashes['crc_' . Helper::fromIntToHex(crc32($hashName))] = [$hashName, $speech];
            }
        }

        $weaponList = explode("\n", file_get_contents(__DIR__ . '/DIR-Execution-Names.txt'));

        foreach ($weaponList as $weapon) {
            $this->crc32Hashes['crc_' . Helper::fromIntToHex(crc32($weapon))] = [$weapon, $weapon];

            //prepare name by given rule at sub_525D60 (Thx to MAJEST1C_R3)
            if (strpos($weapon, "(") !== false){
                $name = substr($weapon, 0, strrpos($weapon, '('));
            }else{
                $name = $weapon;
            }

            if (substr($name, -1) == "_") $name = substr($name, 0, -1);
            $name = substr($name, 0, 8);

            $actionNames = ['pc_jump',  'pc_normal1',  'pc_normal2',  'pc_normal3'];

            if ($game == MHT::PLATFORM_WII)
                $actionNames = ['wii_jump', 'wii_normal1', 'wii_normal2', 'wii_normal3'];

            foreach ($actionNames as $section) {
                $hashName = sprintf("executions\%s\%s.wav", $name, $section);
                $hashName = strtolower($hashName);

                $this->crc32Hashes['crc_' . Helper::fromIntToHex(crc32($hashName))] = [$hashName, $weapon];

            }
        }
    }

    /**
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return array
     */
    public function unpack(NBinary $binary, $game, $platform)
    {

        $this->preCalculateHashes($game);

        $entries = $binary->length() / 4;

        $result = [];

        $unknown = [];
        for ($i = 0; $i < $entries; $i++) {
            $crc32 = $binary->consume(4, NBinary::HEX);

            if ($platform == MHT::PLATFORM_WII){
                $crc32 = Helper::toBigEndian($crc32);
            }

            if (isset($this->crc32Hashes['crc_' . $crc32])) {
                $result[] = $this->crc32Hashes['crc_' . $crc32];
            } else {
                $result[] = $crc32;
                $unknown[] = $crc32;
            }
        }

        echo "\nUnknown: " . number_format(count($unknown) / count($result) * 100, 2) . "٪";

//        $this->speechBruteAddNumber($unknown);
//        $this->bruteCrc($unknown);



        return $result;

    }


    private function speechBruteAddNumber($hashes){
echo "\n";

        $found = 0;
        $speechList = explode("\n", file_get_contents(__DIR__ . '/DIR-Speech-Names.txt'));
        foreach ($this->possibleLevelNames as $levelName) {
            foreach ($speechList as $speech) {
                foreach (range("a", "zzz") as $nr) {

                    $hashName = sprintf("scripted\%s\%s%s\pc_stream.wav", $levelName, $speech, $nr);
                    $crc = Helper::fromIntToHex(crc32($hashName));

                    if (in_array($crc, $hashes) !== false){
                        $found++;
                        echo $hashName . "\n";
                    }
                }
            }
        }

        var_dump($found);
        exit;
    }

    private function bruteCrc($hashes){
        for($i = "a"; 8 > strlen($i); $i++){


            foreach ($this->possibleLevelNames as $levelName) {

                $hashName = sprintf("scripted\%s\%s\pc_stream.wav", $levelName, $i);
                $crc = Helper::fromIntToHex(crc32($hashName));

                if (in_array($crc, $hashes) !== false){
                    var_dump($hashName);
                }
            }
        }

exit;
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
