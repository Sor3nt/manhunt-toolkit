<?php
namespace App\Service\Archive\Inst;

use App\MHT;
use App\Service\Archive\Inst;
use App\Service\CompilerV2\Manhunt2;
use App\Service\Helper;
use App\Service\NBinary;

class Extract {

    private int $missedParameters = 0;
    private array $missedParametersIds = [];

    public function get( NBinary $binary ) : array{

        // enable big-endian for wii files
        if ($binary->unpack($binary->get(4), NBinary::INT_32) > 100_000)
            $binary->numericBigEndian = true;

        $placements = $binary->consume(4, NBinary::INT_32);
        if ($placements === 0)
            return [];

        //split sizes (header) from content
        $sizes = $binary->consume($placements * 4, NBinary::HEX);
        $sizes = str_split($sizes, 8);

        //extract every record
        $records = [];
        foreach ($sizes as $i => $size) {
            $size = $binary->unpack(hex2bin($size), NBinary::INT_32);

            $block = new NBinary( $binary->consume($size, NBinary::BINARY) );
            $block->numericBigEndian = $binary->numericBigEndian;

            $record = $this->parseRecord( $block );
            $records[($i + 1) . "#" .$record['internalName'] . '.json'] = $record;
        }

        //Some debug helper
        {
            $msg = "Missed IDs " . count($this->missedParametersIds) . " (usage " . $this->missedParameters . ")\n";
            echo $msg;
        }

        return $records;
    }

    private function parseRecord( NBinary $binary ) : array{

        $record = [
            'record' => $binary->getString(),
            'internalName' => $binary->getString(),
            'position' => $binary->readXYZ_(),
            'rotation' => $binary->readXYZW(),
            'entityClass' => $binary->getString()
        ];

        //Detect game
        {
            $record['game'] = MHT::GAME_MANHUNT;

            if ($binary->remain() >= 12){
                $maybeType = trim($binary->get(4, 4));
                if (in_array($maybeType, [ 'flo', 'boo', 'str', 'int' ]))
                    $record['game'] = MHT::GAME_MANHUNT_2;
            }
        }

        $paramIndex = 0;
        $params = [];
        while($binary->remain() > 0) {

            //Manhunt 1 has only values
            if ($record['game'] == MHT::GAME_MANHUNT){
                $params[] = [
                    'value' => $binary->consume(4, NBinary::INT_32)
                ];

                continue;
            }

            $parameter = $binary->consume(4, NBinary::HEX);
            if ($binary->numericBigEndian)
                $parameter = Helper::toBigEndian($parameter);

            $parameterName = Manhunt2::getNameByHash($parameter);

            $type = $binary->consume(4, NBinary::STRING);

            // float, boolean, integer are always 4-byte long
            // string need to be calculated
            switch ($type) {
                case 'flo':
                    $value = $binary->consume(4, NBinary::FLOAT_32);
                    break;

                case 'boo':
                case 'int':
                    $value = $binary->consume(4, NBinary::INT_32);

                    if($parameterName == "WEAPON") {
                        $value = Inst::getWeaponNameById($value);
                    }else if($parameterName == "WEAPON2"){
                        $value = Inst::getWeapon2NameById($value);
                    }

                    break;
                case 'str':
                    $value = $binary->getString();
                    break;
                default:
                    die('Unknown INST Datatype found');
            }


            if ($parameterName === null){
                $parameterName = $parameter;

                $this->missedParameters++;
                if (!isset($this->missedParametersIds[$parameter])) {
                    file_put_contents('de', $parameter . '    ' . $record['record'] . '   ' . $record['internalName'] . ' ' . $record['entityClass'].  "\n", FILE_APPEND);

                    $this->missedParametersIds[$parameter] = $record['record'] . '_' . $record['internalName'];
                }
            }


            $params[] = [
                'parameterId' => $parameterName,
                'type' => $type,
                'value' => $value
            ];

            $paramIndex++;
        }

        $record['parameters'] = $params;
        return $record;
    }
}