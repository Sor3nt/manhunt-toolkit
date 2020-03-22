<?php
namespace App\Service\Archive;

use App\Service\Archive\Inst\Build;
use App\Service\Archive\Inst\Extract;
use App\Service\Helper;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Inst extends Archive {

    public $name = 'Entity Positions';

    /**
     * Thanks to ERMACCER
     */
    public static $weapons = [
        "pipe" => 0,
        "cleaver" => 1,
        "wooden baseball bat" => 2,
        "knife" => 3,
        "baseball bat 1" => 4,
        "baseball bat 2" => 5,
        "not defined" => 6,
        "crowbar" => 7,
        "small bat" => 8,
        "nightstick" => 9,
        "axe" => 10,
        "icepick" => 11,
        "machete" => 12,
        "sickle" => 13,
        "baseball bat 3" => 14,
        "spiked bat" => 15,
        "chainsaw" => 16,
        "syringe" => 17,
        "shovel" => 18,
        "sledgehammer" => 19,
        "stunprod" => 20,
        "pen" => 21,
        "acid bottle" => 22,
        "1h firearm" => 23,
        "2h firearm" => 24,
        "razor" => 25,
        "blowtorch" => 26,
        
    ];

    /**
     * Thanks to MAJESTIC_R5
     */
    public static $availableParams = [
        "HP%_" => "0028d4bc",
        "Weapon" => "cff66cea",
        "Weapon2" => "cec40a5c",
        "AI Type" => "3844a80c",
        "Drop_Ammo" => "20d5b47e",
        "Closest %" => "280139a2",
        "Light Type" => "5da669ba",
        "Attenuation Radius" => "b7ea39b4",
        "Lensflare Intensity" => "01b65783",
        "Light Effect Type" => "b0d906a7",
        "Effect Duration" => "fa4f8f73",
        "Flicker/Strobe On Time in ms" => "463390a1",
        "Flicker/Strobe Off Time in ms" => "3bcec87f",
        "Fade In Time in ms" => "edc90a4d",
        "Fade Out Time" => "72994537",
        "Type" => "002f9502",
        "Cone Angle" => "0c15620c",
        "Radius" => "c405c7e8",
        "Intensity" => "1c4d3507",
        "AI_No_Anim" => "1dfb30aa",
        "AI_Piss_Here" => "786ce72b",
        "AI_Smoke_Here" => "103d8d6b",
        "AI_Vending_Machine" => "dec340a8",
        "AI_Check_Crawlspace" => "3735da2b",
        "ASYLUM_DOOR" => "1856b00e",
        "ASYLUM_PEER_ANIM" => "ab05968a",
        "ASYLUM_SPEAK_ANIM" => "d0039412",
        "ASYLUM_MONITOR_ANIM" => "a3ee4b46",
        "WATCHDOG_SMOKE_ANIM" => "9dea4ff3",
        "WATCHDOG_CHECK_CAM_ANIM" => "e72e4882",
        "WATCHDOG_WINDOW_ANIM" => "6768896c",
        "LEGION_I_KNOW_ANIM" => "0e635168",
        "LEGION_KICK_PROP_ANIM" => "9e97fb43",
        "LEGION_TALK_PROP_ANIM" => "f16ffe4d",
        "FREAKS_PISS_ANIM" => "5a64657e",
        "REAKS_VENDING_ANIM" => "80d6d7a4",
        "FREAKS_VOMIT_ANIM" => "d738d98e",
        "GENERIC_TALK_ANIM" => "4b7ef84c",
        "Stream Id" => "97d0ea19",
        "Bank Name" => "2a6c8dbd",
        "Volume" => "ce5c5678",
        "Trigger Probability" => "5827d3f5",
        "Execution Type" => "c2912616",
        "LOD" => "00014dbf",
        "LODNear" => "978c0905",
        "Max distance" => "05762dd1",
        "Min distance" => "07f22c8f",
        "MaxOpenAngleIn" => "e048e996",
        "MaxOpenAngleOut" => "e9663717",
        "Colour: Red" => "f65225e9",
        "Colour: Green" => "d2b364ff",
        "Colour: Blue" => "c08e3d36",
        "Lensflare Size" => "1afd6ad7",
        "Size" => "002ec5db",
        "Trigger Timeout" => "a13e5b7b",
        "Occlusion Ignorance" => "2f820985",
        "Detection Radius in Metres" => "56bbcd4e",
        "Detection Height in Metres" => "fc4a0dff",
        "HunterStart X" => "59b9cc7c",
        "HunterStart Y" => "59b9cc7d",
        "HunterStart Z" => "59b9cc7e",
        "HunterLook X" => "8d37c9c3",
        "HunterLook Z" => "8d37c9c5",
        "PlayerStart X" => "6aa37173",
        "PlayerStart Y" => "6aa37174",
        "PlayerStart Z" => "6aa37175",
        "PlayerLook X" => "76752bda",
        "PlayerLook Z" => "76752bdc",
        "Not Climbable" => "d9b0bdec",
        "Use Default AI" => "af09137c",
        "Line of sight" => "13ac7e3c",
        "Force to zone" => "802ac3ae",
        "Locked" => "b703a532",
        "Lockable" => "867f89bd",
        "Is Real Light" => "04b34658",
        "Switch On By Default" => "238de64f",
        "Affects Objects" => "7624ff66",
        "Affects Map" => "6e9304fa",
        "Creates Character Shadows" => "b56e06cd",
        "Has Lensflare" => "fc8e6578",
        "Light Fog" => "fb1451f4",
        "Has Searchlight Cone" => "fd7f33ef",
        "Switch Off After Duration" => "a2577825",
        "Fade Continously" => "e4e190d7",
        "Entity Light" => "f791cfb5",
        "Scene Light" => "bd0c0b46",
        "Shadows" => "a1d3fdf9",
        "Static" => "c7b14a28",
        "Flickering" => "e81a8fde",
        "Lens Flare" => "d9a0adbc",
        "Is Streamed" => "862b6551",
        "AdjacentDoor" => "e9c7a64e",
        "Name in Samplebank" => "d97f84f6",
        "Execution Object" => "9e25c38b",
        "Object Animation" => "b0d5e537",
        "React_to_Light" => "6bd8ebe8",
        "Slot1" => "0608f9b3",
        "Slot2" => "0608f9b4",
        "Slot3" => "0608f9b5",
        "Material" => "64569a8f",
        "Physics" => "bc9fb163",
        "Transparent" => "51fa4852",
        "Animation_Block" => "847db5aa",
        "Cushions" => "2b2d558c",
        "Blocks" => "9f8038be",
        "Smashable" => "2686eff0",
        "Kickable" => "cc604856",
        "Lod_Data1" => "d2480989",
        "Lod_Data2" => "d248098a",
        "Lod_Data3" => "d248098b",
        "Lod_Data4" => "d248098c",
//        "XPDCR" => "066574e1",
//        "XPDCT" => "066574e3",


    ];

    public static $supported = [
        'entity.inst',
        'entity2.inst',
        'entity_pc.inst',
        'entity_wii.inst',
        'entinst.bin'
    ];


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
            $ext = strtolower($file->getExtension());
            if ($ext !== "json") return false;

            $content = $file->getContents();
            return strpos($content, 'record') !== false &&
                strpos($content, 'internalName') !== false &&
                strpos($content, 'entityClass') !== false;
        }

        return false;
    }

    /**
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return array
     */
    public function unpack(NBinary $binary, $game, $platform){
        return (new Extract())->get($binary);
    }

    /**
     * @param Finder $pathFilename
     * @param $game
     * @param $platform
     * @return null|string
     */
    public function pack( $pathFilename, $game, $platform){

        return (new Build())->build( $pathFilename, $platform );
    }



    /**
     * From Manhunt2.exe 005890A0
     * Found and converted by MAJESTIC_R5
     * Ported to PHP by Sor3nt
     *
     * Convert the given string into a hash value
     * this value is used in the parameters / option section in the INST entries.
     *
     * @param $str
     * @return string
     */
    private function calcHash($str){
        $str = strtoupper($str);
        $hash = 0;

        for($c = 0; $c < strlen($str); $c++){
            $hash = ($hash * 33 + ((((ord($str[$c]) - 97)) & 0xff  <= 25) ? ord($str[$c]) - 32 : ord($str[$c]))) << 32 >> 32;
        }

        return Helper::fromIntToHex($hash);
//        return Helper::toBigEndian(Helper::fromIntToHex($hash));
    }

    public static function getWeaponNameById( $id ){
        foreach (self::$weapons as $name => $weaponId) {
            if ($id == $weaponId) return $name;
        }

        return false;
    }

    public static function getOptionHashByName( $name ){
        if (isset(self::$availableParams[$name])){

            $hash = Helper::toBigEndian(self::$availableParams[$name]);

            return $hash;
        }

        $hash = Helper::toBigEndian($name);
        return $hash;
    }

    public static function getOptionNameByHash( $hash ){

        $hash = Helper::toBigEndian($hash);

        foreach (self::$availableParams as $name => $optionHash) {
            if ($hash == $optionHash) return $name;
        }

        return $hash;
    }

}
