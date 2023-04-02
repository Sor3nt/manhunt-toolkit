<?php

namespace App\Service\Archive;

use App\Service\AudioCodec\AdxPcma;
use App\Service\File;
use App\Service\NBinary;
use Exception;
use Symfony\Component\Finder\Finder;

class Afs extends Archive
{

    public $name = 'Container Format (AFS)';

    public static $supported = 'afs';

    /**
     * @param $pathFilename
     * @param $input
     * @param $game
     * @param $platform
     * @return bool
     */
    public static function canPack($pathFilename, $input, $game, $platform)
    {
        return false;
    }

    /**
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return array
     * @throws Exception
     */
    public function unpack(NBinary $binary, $game, $platform)
    {

        $afs = new AfsArchive($binary);
        $entries = $afs->extract();

        $hashNames = [];
        $files = [];

        $currentFoler = "unknown";

        $map = [];
        $fileIndex = 0;
        $lastBankId = 0;
        $bankIndec = 0;
        foreach ($entries as $index => $entry) {
            $content = $entry->getContent();
            $name = false;

            if ($entry->identify() === "aix") {

                $baseName = str_replace('\\', '/', $hashNames[$index - 1]);
                $baseName = substr($baseName, 0, -4);

                $aix = new AixArchive($entry->getContent());

                $aixResults = $aix->extract();
                foreach ($aixResults as $name => $data) {
                    $files[$baseName . '_' . $name  . '.' . $data->identify()] = $data->getContent()->binary;
                }

                unset($entries[$index]);
                continue;

            }else if ($entry->identify() === "vas" && count($hashNames)) {
                $name = str_replace('\\', '/', $hashNames[$index - 1]);
                $name = str_replace('/stream.vas', '', $name);
            }else if ($entry->identify() === "bank_map") {
                $content->current = 0;
                $name = new NBinary($content->consume(32, NBinary::BINARY));
                $name = $name->getString("\x00", false);
                $files[$name . '/context_map.bin'] = $content->binary;

                $values = [];
                $map = [];
                do {
                    $lastValue = $content->consume(4, NBinary::INT_32);
                    $values[] = $lastValue;
                }while($content->remain() > 0);

                $values = array_values(array_unique($values));
                foreach ($values as $vIndex => $value) {
                    if ($vIndex === count($values) - 1)
                        continue;
                    else {

                        for($a = $value; $a < $values[$vIndex + 1]; $a++){
                            $map[$a] = $value;
                        }

                    }
                }

                $fileIndex = 0;
                $currentFoler = $name;

                continue;
            }else if ($entry->identify() === "hash_name_list") {
                $hashNames = explode("\x0D\x0A", $entry->getContent()->binary);
                continue;
            }else if ($entry->identify() === "adx" && count($hashNames)) {
                $name = str_replace('\\', '/', $hashNames[$index - 1]);
                $name = str_replace('/stream.adx', '', $name);
            }

            //we have names, use it
            if (count($hashNames) && $name === false) {
                $name = str_replace('\\', '/', $hashNames[$index - 1]);
            } elseif ($name === false) {
                if ($map[$fileIndex] !== $lastBankId){
                    $lastBankId = $map[$fileIndex];
                    $bankIndec = 0;
                }else{
                    $bankIndec++;
                }

                if (isset($map[$fileIndex]))
                    $name = $entry->name . "/" . $bankIndec . "_" . $index;
                else
                    die("jmmmm");
            }

            $files[$currentFoler . '/'. $name . '.' . (new File($content))->identify()] = $content->binary;
            $fileIndex++;
        }

        return $files;

    }

    /**
     * @param Finder $pathFilename
     * @param $game
     * @param $platform
     * @return null|string
     */
    public function pack($pathFilename, $game, $platform)
    {
        return "";
    }


}
