<?php

namespace App\Service\Archive;

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

        foreach ($entries as $index => $entry) {
            $content = $entry->getContent();

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

            }else if ($entry->identify() === "context_map") {
                $name = $content->getString();
                $files[$name . '/context_map.bin'] = $content->binary;
                continue;
            }else if ($entry->identify() === "hash_name_list") {
                $hashNames = explode("\x0D\x0A", $entry->getContent()->binary);
                continue;
            }

            //we have names, use it
            if (count($hashNames)) {
                $name = str_replace('\\', '/', $hashNames[$index - 1]);
            } else {
                $name = $index;
            }

            $files[$name . '.' . (new File($content))->identify()] = $content->binary;

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
