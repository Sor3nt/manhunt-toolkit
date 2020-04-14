<?php

namespace App\Service\Archive;

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

            if ($content->length() == 264) {
                $name = $content->getString();
                $files[$name . '/context_map.bin'] = $content->binary;
                continue;
            }

            if ($entry->getId() === "scri") {
                $hashNames = explode("\x0D\x0A", $entry->getContent()->binary);
                continue;
            }

            $adx = new Adx($content);

            switch ($adx->getEncodingType()) {
                case 'standard':
                    $extension = "adx";
                    break;
                case 'ahx':
                    $extension = "ahx";
                    break;
                default:
                    throw new Exception(sprintf('Encoding type %s is not supported', $adx->getEncodingType()));

            }


            if (count($hashNames)) {
                $name = $hashNames[$index - 1];
                $name = str_replace('\\', '/', $name);
            } else {
                $name = $index;
            }

            $files[$name . '.' . $extension] = $content->binary;

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
     * @return AfsArchiveEntry[]
     * @throws Exception
     */
    public function extract()
    {

        $entries = [];
        while ($this->entryCount--) {
            $entry = new AfsArchiveEntry($this->getBlock($this->binary));

            if ($entry->getId() == "AFS") {

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


class AfsArchiveEntry
{

    /** @var NBinary */
    private $binary;

    private $id = "UNK";

    public function __construct(NBinary $binary)
    {
        $this->binary = $binary;
        $this->id = trim($this->binary->getFromPos(0, 4, NBinary::STRING));
        $this->binary->current = 0;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return NBinary
     */
    public function getContent()
    {
        return $this->binary;
    }

}

class Adx
{
    /** @var NBinary */
    private $binary;

    /**
     * Adx constructor.
     * @param NBinary $binary
     * @throws Exception
     */
    public function __construct(NBinary $binary)
    {
        if ($binary->getFromPos(0, 2, NBinary::HEX) !== "8000") {
            throw new Exception("Not a ADX Format");
        }

        $this->binary = $binary;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getEncodingType()
    {
        $code = (int)$this->binary->getFromPos(4, 1, NBinary::HEX);

        if ($code == 3) return 'standard';
        if ($code == 11) return 'ahx';

        throw new Exception(sprintf('The encoding type %s is not supported', $code));
    }


}
