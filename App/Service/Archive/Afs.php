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

            if ($entry->identify() === "context_map") {
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

/**
 * Class AfsArchive
 * @package App\Service\Archive
 */
class AfsArchive
{

    /** @var NBinary */
    private $binary;

    private $entryCount = 0;

    /**
     * AfsArchive constructor.
     * @param NBinary $binary
     * @throws Exception
     */
    public function __construct(NBinary $binary)
    {
        if ($binary->get(3) !== "AFS") throw new Exception('File is not a AFS Container');
        $this->binary = $binary;

        $this->entryCount = $binary->consume(4, NBinary::INT_32, 4);
    }


    /**
     * @param NBinary $binary
     * @return NBinary
     */
    private function getBlock(NBinary $binary)
    {
        $offset = $binary->consume(4, NBinary::INT_32);
        $size = $binary->consume(4, NBinary::INT_32);

        $current = $binary->current;

        $binary->current = $offset;
        $data = $binary->consume($size, NBinary::BINARY);
        $binary->current = $current;

        return new NBinary($data);
    }

    /**
     * @return File[]
     * @throws Exception
     */
    public function extract()
    {

        $entries = [];
        while ($this->entryCount--) {
            $entry = new File($this->getBlock($this->binary));

            if ($entry->identify() == "afs") {
                $subAfs = new AfsArchive($entry->getContent());
                $subEntries = $subAfs->extract();
                foreach ($subEntries as $subEntry) {
                    $entries[] = $subEntry;
                }

            } else {
                $entries[] = $entry;

            }
        }

        return $entries;
    }


}
