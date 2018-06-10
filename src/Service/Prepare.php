<?php
namespace App\Service;

use App\Service\Archive\Glg;
use App\Service\Archive\Inst;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\Finder\Finder;

class Prepare {

    /** @var Glg  */
    private $glg;

    /** @var Inst */
    private $inst;

    /** @var FilesystemCache  */
    private $cache;

    public function __construct( Glg $glg, Inst $inst ) {
        $this->glg = $glg;
        $this->inst = $inst;
        $this->cache = new FilesystemCache;
    }


    public function cacheGlgContent(){

        $finder = new Finder();
        $finder->name('*.glg')->files()->in( $this->cache->get('workdir') );

        foreach ($finder as $file) {

            $unpacked = $this->glg->uncompress( $file->getContents() );

            $cachePath = str_replace(
                [$this->cache->get('workdir'), '/'],
                ['', '.'],
                $file->getRealPath()
            );

            $this->cache->set('glg.files' . $cachePath, $unpacked);
        }

    }


    public function cacheInstContent(){

        $finder = new Finder();
        $finder->name('*.inst')->files()->in( $this->cache->get('workdir') );

        foreach ($finder as $file) {

            $unpacked = $this->inst->unpack( $file->getContents() );

            $cachePath = str_replace(
                [$this->cache->get('workdir'), '/'],
                ['', '.'],
                $file->getRealPath()
            );

            $this->cache->set('inst.files' . $cachePath, $unpacked);
        }

    }


}