<?php
    namespace App\Patches\Executions;

    use App\Patches\AbstractPatch;

    class Distance extends AbstractPatch {

        public $code = 'distance';
        public $description = '';

        public $distance = '5.5';

        public $prio = 10;

        public function patchActive(){
            return file_exists($this->cache->get('workdir') . '/movies/stinger.bik_off');
        }

        public function applyPatch( ){

            $records = file_get_contents(
                $this->cache->get('workdir') . '/global/ini/resource13.glg'
            );

            $records = $this->glg->convertToRecordsArray($records);

            foreach ($records as $index => &$entries) {
                foreach ($entries as &$entry) {

                    switch ($entry['key']){
                        case 'VECPAIR':
                        case 'VECPAIR@':


                            $parts = explode(' ', $entry['value']);

                            if (isset($parts[7]) && $parts[7] == "0.5"){
                                $parts[7] = $this->distance;
                            }

                            $entry['value'] = implode(' ', $parts);

                        break;
                    }

                }

            }

            $records = $this->glg->convertToRecordFile($records);

            file_put_contents(
                $this->cache->get('workdir') . '/global/ini/resource13.glg',
                $this->glg->compress($records)
            );
        }

        public function removePatch( ){
            $records = $this->cache->get('glg.files.global.ini.resource13.glg.patched');

            $records = $this->glg->convertToRecordsArray($records);

            foreach ($records as $index => &$entries) {
                foreach ($entries as &$entry) {

                    switch ($entry['key']){
                        case 'VECPAIR':
                        case 'VECPAIR@':
                            $parts = explode(' ', $entry['value']);

                            if (isset($parts[7]) && $parts[7] == $this->distance){
                                $parts[7] = "0.5";
                            }

                            $entry['value'] = implode(' ', $parts);

                            break;
                    }

                }

            }

            $records = $this->glg->convertToRecordFile($records);

            $this->cache->set('glg.files.global.ini.resource13.glg.patched', $records);

            file_put_contents(
                $this->cache->get('workdir').
                '/global/ini/resource13.glg', $this->glg->pack($records)
            );
        }

    }

