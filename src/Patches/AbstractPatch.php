<?php
    namespace App\Patches;

    use App\Service\Archive\Glg;
    use Symfony\Component\Cache\Simple\FilesystemCache;

    abstract class AbstractPatch {

        public $code;
        public $description;

        public $prio = 5;


        /** @var FilesystemCache  */
        public $cache;

        /** @var Glg  */
        public $glg;

        public function __construct( FilesystemCache $cache, Glg $glg ) {
            $this->glg = $glg;
            $this->cache = $cache;
        }


        abstract public function applyPatch();
        abstract public function removePatch();
        abstract public function patchActive();
    }

