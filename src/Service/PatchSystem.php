<?php
namespace App\Service;

use App\Patches\AbstractPatch;
use App\Service\Archive\Glg;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;

class PatchSystem {

    /** @var Glg  */
    private $glg;

    /** @var FilesystemCache  */
    private $cache;

    private $patchesInfo;

    private $patches;


    public function __construct($patches,  Glg $glg ) {
        $this->patches = $patches;
        $this->glg = $glg;
        $this->cache = new FilesystemCache;
    }

    public function applyPatches(){

        $activePatches = $this->cache->get('patches.active', []);

        uksort($activePatches, function($a, $b){
            $patchA = $this->get( $a );
            $patchB = $this->get( $b );

            return $patchA->prio > $patchB->prio;
        });

        foreach ($activePatches as $patchCode => $status) {

            $patch = $this->get( $patchCode );
            if ($patch == false) continue;

            if ($status){
                $patch->applyPatch();
            }else{
                $patch->removePatch();
                unset($activePatches[$patchCode]);
            }
        }

        $this->cache->set('patches.active', $activePatches);
    }

    public function activatePatch( $patchCode ){

        $activePatches = $this->cache->get('patches.active', []);

        $activePatches[$patchCode] = true;

        $this->cache->set('patches.active', $activePatches);
    }

    public function removePatch( $patchCode ){

        $activePatches = $this->cache->get('patches.active', []);
        if (!isset($activePatches[$patchCode])) return false;

        $activePatches[$patchCode] = false;

        $this->cache->set('patches.active', $activePatches);

        return true;
    }

    public function isPatchActive( $patchCode ){
        $activePatches = $this->cache->get('patches.active', []);
        return isset($activePatches[$patchCode]) ? $activePatches[$patchCode] : false;
    }

    public function exists( $patchCode ){

        foreach ($this->patches as $patch) {
            $patch = new $patch( $this->cache, $this->glg );
            if ($patch->code == $patchCode) return true;
        }

        return false;
    }


    public function getAvailable( ){

        $patches = [];
        foreach ($this->patches as $patch) {

            /** @var AbstractPatch $patch */
            $patch = new $patch( $this->cache, $this->glg );
            $patches[] = [
                'code' => $patch->code,
                'description' => $patch->description,
            ];
        }

        return $patches;
    }

    public function get( $patchCode ){

        foreach ($this->patches as $patch) {

            /** @var AbstractPatch $patch */
            $patch = new $patch( $this->cache, $this->glg );

            if ($patchCode == $patch->code) return $patch;
        }

        return false;
    }


}