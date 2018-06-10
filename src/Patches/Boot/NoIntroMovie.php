<?php
    namespace App\Patches\Boot;

    use App\Patches\AbstractPatch;

    class NoIntroMovie extends AbstractPatch {

        public $code = 'no-intro-movie';
        public $description = '';

        public function patchActive(){
            return file_exists($this->cache->get('workdir') . '/movies/stinger.bik_off');
        }

        public function applyPatch( ){

            if (file_exists($this->cache->get('workdir') . '/movies/stinger.bik')){
                rename(
                    $this->cache->get('workdir') . '/movies/stinger.bik',
                    $this->cache->get('workdir') . '/movies/stinger.bik_off'
                );
            }

            if (file_exists($this->cache->get('workdir') . '/movies/stinger_wide.bik')){
                rename(
                    $this->cache->get('workdir') . '/movies/stinger_wide.bik',
                    $this->cache->get('workdir') . '/movies/stinger_wide.bik_off'
                );
            }

        }

        public function removePatch( ){

            if (file_exists($this->cache->get('workdir') . '/movies/stinger.bik_off')){
                rename(
                    $this->cache->get('workdir') . '/movies/stinger.bik_off',
                    $this->cache->get('workdir') . '/movies/stinger.bik'
                );

            }

            if (file_exists($this->cache->get('workdir') . '/movies/stinger_wide.bik_off')){
                rename(
                    $this->cache->get('workdir') . '/movies/stinger_wide.bik_off',
                    $this->cache->get('workdir') . '/movies/stinger_wide.bik'
                );
            }

        }

    }

