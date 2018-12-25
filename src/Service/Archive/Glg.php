<?php
namespace App\Service\Archive;

class Glg  {

    /** @var array  */
    public $header = [
        'start' => '5A32484D',      // the glg header
        'separator' => '78DA'       // i quess its just a separator
    ];


    public function convertToRecordFile( $records = [])
    {
        $file = [];
        foreach ($records as $name => $entries) {
            $file[] = '';
            $file[] = $name;
            foreach ($entries as $entry) {
                if (is_null($entry['value'])) continue;
                $file[] = "\t" . $entry['key'] . "\t" . $entry['value'];

            }
            $file[] = 'END';
            $file[] = '';
        }

        return implode("\n", $file);
    }


    /**
     * @param $content
     * @return array
     */
    public function convertToRecordsArray( $content ){

        //remove any comments from the records
        $content = preg_replace('/#.*\n/', '', $content);

        $blocks = [];
        $currentBlock = false;
        foreach (explode("\n", $content) as $line) {

            if (empty(trim($line))) continue;


//
            switch (substr(bin2hex($line), 0, 2)){

                // attributes
                case '09':  // tab
                case '20':  // space
                    $line = trim($line);
                    $line = preg_replace('/\t/', ' ', $line);
                    $line = preg_replace('/  /', ' ', $line);

                    if (strpos($line, ' ') !== false){
                        $key = substr($line, 0, strpos($line, ' '));
                        $value = substr($line, strpos($line, ' ') + 1);
                        $blocks[$currentBlock][] =  [
                            'key' => $key,
                            'value' => $value
                        ];
                    }else{
                        $blocks[$currentBlock][] =  [
                            'key' => $line,
                            'value' => ''
                        ];
                    }

                    break;

                // record
                case '52':
                    $currentBlock = trim($line);
                    $blocks[$currentBlock] = [];
                    break;

                //end
                case '45':
                    $currentBlock = false;
                    break;

                default:
                    die("Hmm kein record");

            }


        }

        return $blocks;
    }
}