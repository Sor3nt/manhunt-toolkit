<?php
namespace App\Service\Archive;

use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Manhunt2Exe {

    const PATCH_NOT_FOUND   = 'PATCH_NOT_FOUND';
    const ALREADY_ACTIVE    = 'ALREADY_ACTIVE';
    const OFFSET_WRONG_DATA = 'OFFSET_WRONG_DATA';
    const APPLIED           = 'APPLIED';

    /** @var  NBinary */
    public $binary;

    private $patches = [
        'noJitter' => [ [ '915944' => ['84' => '85'] ] ],
//        'whitenoise' => [ [ '2527659' => ['a0a9aa4040' => '0000000000'] ] ],

        //Credits: Ermaccer
        'sixtyFrames' => [ [ '53923' => ['2841' => '5682'] ] ],

        //Credits: Ermaccer
        'noLegalScreen' => [
            [

                '1309800' => ['a00f' => '0000'],
                '1309776' => ['ff' => '00'],
                '1309807' => ['ff' => '00'],

            ]
        ],


        'globalExecutionFile' => [
            [

//                // move the pointer to get more space
//                '1598421' => ['686c856600' => 'dc967500'],
//
//
//                //rename "/global/cash_pc.tex /glo" to "../global/exec_anim.bin "
//                '2498936' => [
//                    '2f676c6f62616c2f636173685f70632e746578002f676c6f' =>
//                    '2e2e2f676c6f62616c2f657865635f616e696d2e62696e00'
//                ],


                '2524524' => ['7374726d616e696d5f70632e62696e' => '2e2e2f737472616e696d5f2e62696e']
            ]
        ],


        'globalAnimationFile' => [
            [
                // move the pointer to get more space
                '2764564' => ['20246600' => '14246600'],

                //rename "AllAnims_pc allanim" to "../global/all_anim"
                '2499604' => [
                        '416c6c416e696d735f706300616c6c616e696d'
                    =>
                        '2e2e2f676c6f62616c2f616c6c5f616e696d00'
                ]
            ]
        ],


        'funMode' => [
            [
                //set the variable to 0x20 to activate the mode
                '1503344' => ['c3cccccccccccccc' => 'c60540be760020c3'],

                //blood to flowers
                '2828005' => ['01' => '00'],

            ]
        ],


        'skipMenuSpecial' => [
            [
                '1319993' => ['75' => '74'],
            ]
        ],

        'restoreFileName' => [
            [
                //resource3.glg => EntityTypeData.ini
                '2764336' => ['5c25' => '4825'],

                //resource1.glg => levelSetup.ini
                '2764480' => ['3c24' => 'c02b'],

                //global/resource15.glg => global/weather.ini
                '2764660' => ['9423' => '8023'],

                //global/initscripts/resource27.glg => global/initscripts/Overlay.txt
                '2764672' => ['5c23' => '3c23'],

                //global/game.scc => levels/global/game.scc
                '2764588' => ['002b' => 'e82a'],

                //resource9.glg => ParticleEffects.ini
                '2764576' => ['0424' => 'f023'],

                //resource14.glg => WeaponTypeData.ini
                '2764540' => ['2c24' => '5c2b'],

                //resource14.glg => WeaponTypeData.ini
                '2764456' => ['6024' => '4c24'],

                //resource11.glg => ShotTypeData.ini
                '2764444' => ['8424' => '7024'],
                
                //	resource8.glg => EntityStateSounds.ini
                '2764432' => ['ac24' => '9424'],
                
                //	resource5.glg => AiSounds.ini
                '2764420' => ['cc24' => 'bc24'],
                
                //	resource10.glg => PhysicsTypeData.ini
                '2764348' => ['3825' => '2425'],
                
                //	resource7.glg => Communications.ini
                '2764300' => ['8c25' => '7825'],
                
                //	resource6.glg => AiTypeData.ini
                '2764288' => ['ac25' => '9c25'],
                
                //	global/initscripts/resource31.glg => global/initscripts/Swap.txt
                '2764228' => ['f825' => 'dc25'],
                
                //	global/initscripts/resource30.glg => global/initscripts/Status.txt
                '2764216' => ['3c26' => '1c26'],
                
                //	global/initscripts/resource21.glg => global/initscripts/ExecutionDecalData.txt
                '2765176' => ['e41e' => 'b81e'],
                
                //	global/initscripts/resource25.glg => global/initscripts/MeleeDecalData.txt
                '2765188' => ['941e' => '6c1e'],
                
                //	global/initscripts/resource28.glg => global/initscripts/PcExecutionData.txt
                '2765164' => ['301f' => '081f'],
                
                //	lobal/initscripts/resource29.glg => global/initscripts/Settings.txt
                '2765152' => ['741f' => '541f'],
                
                //	global/initscripts/resource23.glg => global/initscripts/Levels.txt
                '2765128' => ['d41f' => 'b41f'],
                
                //	global/initscripts/resource26.glg => global/initscripts/NEOMENU.txt
                '2765116' => ['1820' => 'f81f'],
                
                //	global/initscripts/resource19.glg => global/initscripts/_KEY3.txt
                '2764828' => ['fc21' => 'dc21'],
                
                //	global/initscripts/resource18.glg => global/initscripts/_KEY2.txt
                '2764816' => ['4022' => '2022'],
                
                //	global/initscripts/resource17.glg => global/initscripts/_KEY1.txt
                '2764804' => ['8422' => '6422'],
                
                //	global/initscripts/resource22.glg => initscripts/frontend/Hud.txt
                '2764888' => ['6026' => 'a021'],
                
                //	global/initscripts/resource24.glg => initscripts/frontend/melee.txt
                '2763952' => ['3c29' => '1c29'],
            ],
        ],
    ];

    public function getPatchesStatus(){

        $activePatches = [];
        $inActivePatches = [];

        foreach ($this->patches as $name => $patch) {

            foreach ($patch as $patchBlock) {

                foreach ($patchBlock as $offset => $replacement) {

                    foreach ($replacement as $fromHex => $toHex) {
                        $this->binary->current = $offset;

                        $sequence = $this->binary->consume(strlen($fromHex) / 2, NBinary::HEX);

                        if ($sequence == $toHex){
                            $activePatches[] = $name;
                        }else{
                            $inActivePatches[] = $name;
                        }

                        //we check just the first byte sequence
                        break 2;
                    }
                }
            }
        }

        return [$activePatches, $inActivePatches];
    }

    public function patch($name, $doPatch ){
        if (!isset($this->patches[$name])) return self::PATCH_NOT_FOUND;

        $patch = $this->patches[$name];

        foreach ($patch as $patchBlock) {

            foreach ($patchBlock as $offset => $replacement) {

                foreach ($replacement as $fromHex => $toHex) {
                    $this->binary->current = $offset;

                    $sequence = $this->binary->consume(strlen($fromHex) / 2, NBinary::HEX);

                    if ($doPatch == true){
                        if ($sequence == $toHex) return self::ALREADY_ACTIVE;
                        if ($sequence != $fromHex) return self::OFFSET_WRONG_DATA;

                        $newSequence = $toHex;
                    }else{
                        if ($sequence == $fromHex) return self::ALREADY_ACTIVE;
                        if ($sequence != $toHex) return self::OFFSET_WRONG_DATA;

                        $newSequence = $fromHex;
                    }

                    $this->binary->current = $offset;
                    $this->binary->overwrite($newSequence, NBinary::HEX);
                }
            }
        }

        return self::APPLIED;
    }
}