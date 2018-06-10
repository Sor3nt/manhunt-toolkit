<?php
    namespace App\Patches\Executions;

    use App\Patches\AbstractPatch;

    class NoFlash extends AbstractPatch {

        public $code = 'no-camera-flash';
        public $description = 'Remove the camera red flash effect while the execution happens';

        public function applyPatch( ){

            $records = $this->getWorkFile('glg.files.global.resource1.glg');
            $records = $this->glg->convertToRecordsArray($records);

            foreach ($records['RECORD VIDEO_EFFECT_SETTINGS'] as &$entry) {
                switch ($entry['key']){
                    case 'EXECUTION_COLRAMP_TEXTURE':
                        $entry['value'] = 'FE_';
                        break;

                    case 'EXECUTION_COLRAMP_FLASH_TIMES':
                        $entry['value'] = '0.0, 0.0, 0.0';
                        break;


                    case 'EXECUTION_COLRAMP_FADE_TIME':
                        $entry['value'] = '0.0';
                        break;


                }
            }

            $records = $this->glg->convertToRecordFile($records);
            $this->cache->set('glg.files.global.resource1.glg.patched', $records);

            file_put_contents(
                $this->cache->get('workdir').
                '/global/resource1.glg', $this->glg->pack($records)
            );
        }

        public function removePatch( ){
            $oriRecords = $this->cache->get('glg.files.global.resource1.glg');
            $records = $this->cache->get('glg.files.global.resource1.glg.patched');

            $records = $this->glg->convertToRecordsArray($records);
            $oriRecords = $this->glg->convertToRecordsArray($oriRecords);

            foreach ($records['RECORD VIDEO_EFFECT_SETTINGS'] as &$entry) {
                switch ($entry['key']){
                    case 'EXECUTION_COLRAMP_FLASH_TIMES':
                    case 'EXECUTION_COLRAMP_FADE_TIME':
                    case 'EXECUTION_COLRAMP_TEXTURE':
                        $entry['value'] = current(array_filter($oriRecords['RECORD VIDEO_EFFECT_SETTINGS'], function( $setting ) use ($entry){
                            if($setting['key'] == $entry['key']){
                                return true;
                            }
                            return false;
                        }))['value'];

                        break;
                }
            }

            $records = $this->glg->convertToRecordFile($records);
            $this->cache->set('glg.files.global.resource1.glg.patched', $records);

            file_put_contents(
                $this->cache->get('workdir').
                '/global/resource1.glg', $this->glg->pack($records)
            );
        }

    }

