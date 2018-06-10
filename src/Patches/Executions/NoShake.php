<?php
    namespace App\Patches\Executions;

    use App\Patches\AbstractPatch;

    class NoShake extends AbstractPatch {

        public $code = 'no-camera-shake';
        public $description = 'Remove the camera shaking effect while the execution happens';

        public function applyPatch( ){

            $records = $this->getWorkFile('glg.files.global.resource1.glg');
            $records = $this->glg->convertToRecordsArray($records);

            foreach ($records['RECORD CAM_SETTINGS'] as &$entry) {
                switch ($entry['key']){
                    case 'CAM_SHAKE_LAT_STAND':
                    case 'CAM_SHAKE_ROLL_STAND':
                    case 'CAM_SHAKE_LAT_SNEAKWALK':
                    case 'CAM_SHAKE_ROLL_SNEAKWALK':
                    case 'CAM_SHAKE_LAT_SNEAKRUN':
                    case 'CAM_SHAKE_ROLL_SNEAKRUN':
                    case 'CAM_SHAKE_LAT_SPRINT':
                    case 'CAM_SHAKE_ROLL_SPRINT':
                    case 'CAM_SHAKE_LAT_CRAWL':
                    case 'CAM_SHAKE_ROLL_CRAWL':
                    case 'CAM_SHAKE_LAT_EXECUTION':
                    case 'CAM_SHAKE_ROLL_EXECUTION':
                    case 'CAM_SHAKE_LAT_SCRIPTED_DEFAULT':
                    case 'CAM_SHAKE_ROLL_SCRIPTED_DEFAULT':
                        $entry['value'] = null;
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

            foreach ([
                'CAM_SHAKE_LAT_STAND',
                'CAM_SHAKE_ROLL_STAND',
                'CAM_SHAKE_LAT_SNEAKWALK',
                'CAM_SHAKE_ROLL_SNEAKWALK',
                'CAM_SHAKE_LAT_SNEAKRUN',
                'CAM_SHAKE_ROLL_SNEAKRUN',
                'CAM_SHAKE_LAT_SPRINT',
                'CAM_SHAKE_ROLL_SPRINT',
                'CAM_SHAKE_LAT_CRAWL',
                'CAM_SHAKE_ROLL_CRAWL',
                'CAM_SHAKE_LAT_EXECUTION',
                'CAM_SHAKE_ROLL_EXECUTION',
                'CAM_SHAKE_LAT_SCRIPTED_DEFAULT',
                'CAM_SHAKE_ROLL_SCRIPTED_DEFAULT',
             ] as $key) {

                $records['RECORD CAM_SETTINGS'][] = [
                    'key' => $key,
                    'value' => current(array_filter($oriRecords['RECORD CAM_SETTINGS'], function( $setting ) use ($key){
                        if($setting['key'] == $key){
                            return true;
                        }
                        return false;
                    }))['value']
                ];
            }


            $records = $this->glg->convertToRecordFile($records);

            $this->cache->set('glg.files.global.resource1.glg.patched', $records);

            file_put_contents(
                $this->cache->get('workdir').
                '/global/resource1.glg', $this->glg->pack($records)
            );
        }

    }

