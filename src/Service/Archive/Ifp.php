<?php
namespace App\Service\Archive;

use App\Service\Binary;
use Symfony\Component\Console\Output\OutputInterface;

class Ifp {
    private $game = false;

    private function toString( $hex ){
        $hex = str_replace('00', '', $hex);
        return hex2bin($hex);
    }

    private function toInt( $hex ){
        return (int) current(unpack("L", hex2bin($hex)));
    }

    private function toFloat( $hex ){
        return (float) current(unpack("f", hex2bin($hex)));
    }

    private function toInt8($hex){
        return current(unpack("c", hex2bin($hex)));
    }

    private function toInt16($hex){
        return current(unpack("s", hex2bin($hex)));
    }

    private function substr(&$hex, $start, $end){

        $result = substr($hex, $start * 2, $end * 2);
        $hex = substr($hex, $end * 2);
        return $result;

    }

    public function unpack($data, OutputInterface $output = null, $outputTo){
        /** @var Binary $remain */

//        $output = null;
        $entry = bin2hex($data);

        /**
         * ROOT (ANCT)
         */
        $headerType = $this->toString($this->substr($entry, 0, 4));
        $numBlock = $this->toInt($this->substr($entry, 0, 4));

        if ($headerType !== "ANCT")
            throw new \Exception(sprintf('Expected ANCT got: %s', $headerType));


        if (!is_null($output)) $output->writeln(
            sprintf('| <info>Header:</info> %s', $headerType . "\n") .
            sprintf('| <info>Number of Blocks:</info> %s', $numBlock)
        );

        /**
         * BLOCK (BLOC)
         */
        while($numBlock > 0){

            $sectionBLOC = $this->toString($this->substr($entry, 0, 4));
            $blockNameLength = $this->toInt($this->substr($entry, 0, 4));


            if ($sectionBLOC !== "BLOC")
                throw new \Exception(
                    sprintf('Expected BLOC got: %s', $sectionBLOC)
                );


            //Get Block name
            $blockName = $this->toString(
                $this->substr($entry, 0, $blockNameLength)
            );

            if (!is_null($output)) $output->writeln(
                sprintf('| <info>Found Block:</info> %s', $blockName)
            );

            $outputToBlock = $outputTo . $blockName . '/';
            @mkdir($outputToBlock, 0777, true);

            /**
             * Animation Packs
             */

            $headerType = $this->toString($this->substr($entry, 0, 4));
            $animationCount = $this->toInt($this->substr($entry, 0, 4));

            if ($headerType !== "ANPK")
                throw new \Exception(
                    sprintf('Expected ANPK got: %s', $headerType)
                );

            if (!is_null($output)) $output->writeln(
                sprintf('  | <info>Current Section:</info> %s', $headerType) . "\n" .
                sprintf('    | <info>Animations:</info> %s', $animationCount)
            );


            /**
             * Animation Pack Entries
             */
            $this->extractAnimation($animationCount, $entry, $output, $outputToBlock);

        }

    }

    private function extractAnimation($animationCount, &$entry, OutputInterface $output = null, $outputTo){
        $animations = [];
        while($animationCount > 0){


            $nameLabel = $this->toString($this->substr($entry, 0, 4));
            $animationNameLength = $this->toInt($this->substr($entry, 0, 4));

            if ($nameLabel !== "NAME")
                throw new \Exception(
                    sprintf('Expected NAME got: %s', $nameLabel)
                );

            $animationName = $this->toString(
                $this->substr($entry, 0, $animationNameLength)
            );

            $numberOfBones = $this->toInt($this->substr($entry, 0, 4));
            $chunkSize = $this->toInt($this->substr($entry, 0, 4));
            $frameTimeCount = $this->toFloat($this->substr($entry, 0, 4));


            $resultAnimation = [
                'numberOfBones' => $numberOfBones,
                'chunkSize' => $chunkSize,
                'frameTimeCount' => $frameTimeCount,
            ];

            if (!is_null($output)) $output->writeln(
                sprintf('      | <info>Animation:</info> %s', $animationName) . "\n" .
                sprintf('        | <info>Bones:</info> %s', $numberOfBones) . "\n" .
                sprintf('        | <info>Chunk Size:</info> %s', $chunkSize) . "\n" .
                sprintf('        | <info>?? Frame time count:</info> %s', $frameTimeCount)
            );

            /**
             * Sequences
             */

            $resultAnimation['bones'] = $this->extractBones($numberOfBones, $entry, $output);


            $headerSize    = $this->toInt($this->substr($entry, 0, 4));
            $unknown5      = $this->substr($entry, 0, 4);
            $eachEntrySize = $this->toInt($this->substr($entry, 0, 4));
            $numEntry      = $this->toInt($this->substr($entry, 0, 4));


            $resultAnimation['headerSize'][] = $headerSize;
            $resultAnimation['unknown5'][] = $unknown5;
            $resultAnimation['eachEntrySize'][] = $eachEntrySize;
            $resultAnimation['numEntry'][] = $numEntry;


            $resultAnimation['entry'] = [];
            while ($numEntry > 0){

                if ($this->game == "mh1"){
                    $resultAnimation['entry'][] = [
                        'time' => $this->toFloat($this->substr($entry, 0, 4)),
                        'unknown' => $this->substr($entry, 0, 4),
                        'unknown2' => $this->substr($entry, 0, 4),
                        'unknown3' => $this->substr($entry, 0, 4),
                        'unknown4' => $this->substr($entry, 0, 4),
                        'boneId' => $this->toFloat($this->substr($entry, 0, 4)),
                        'particleName' => $this->substr($entry, 0, 8),
                        'particlePosition' => [
                            $this->toFloat($this->substr($entry, 0, 4)),
                            $this->toFloat($this->substr($entry, 0, 4)),
                            $this->toFloat($this->substr($entry, 0, 4)),
                            $this->toFloat($this->substr($entry, 0, 4)),
                            $this->toFloat($this->substr($entry, 0, 4)),
                            $this->toFloat($this->substr($entry, 0, 4)),
                            $this->toFloat($this->substr($entry, 0, 4)),
                        ],
                        'unknown5' => $this->substr($entry, 0, 4),
                    ];
                }else{
                    $resultAnimation['entry'][] = [
                        'time' => $this->toFloat($this->substr($entry, 0, 4)),
                        'unknown' => $this->substr($entry, 0, 4),
                        'unknown2' => $this->substr($entry, 0, 4),
                        'CommandName' => $this->substr($entry, 0, 64),
                        'unknown3' => $this->substr($entry, 0, 4),
                        'boneId' => $this->toFloat($this->substr($entry, 0, 4)),
                        'particleName' => $this->substr($entry, 0, 8),
                        'particlePosition' => [
                            $this->toFloat($this->substr($entry, 0, 4)),
                            $this->toFloat($this->substr($entry, 0, 4)),
                            $this->toFloat($this->substr($entry, 0, 4)),
                            $this->toFloat($this->substr($entry, 0, 4)),
                            $this->toFloat($this->substr($entry, 0, 4)),
                            $this->toFloat($this->substr($entry, 0, 4)),
                            $this->toFloat($this->substr($entry, 0, 4)),
                        ],
                        'unknown5' => $this->substr($entry, 0, 40),
                    ];
                }

                $numEntry--;
            }

            $animations[] = $resultAnimation;

            if (!is_null($output)) $output->writeln(
                sprintf('        | <info>Animation name:</info> %s', $animationName) . "\n" .
                sprintf('        | <info>Header size:</info> %s', $headerSize) . "\n" .
                sprintf('        | <info>Unknown 5:</info> %s', $unknown5) . "\n" .
                sprintf('        | <info>Each Entry Size:</info> %s', $eachEntrySize) . "\n" .
                sprintf('        | <info>Entry Count:</info> %s', $numEntry) . "\n" .
                sprintf('        | <info>Time:</info> %s', $resultAnimation['entry']['time']) . "\n" .
                sprintf('        | <info>unknown:</info> %s', $resultAnimation['entry']['unknown']) . "\n" .
                sprintf('        | <info>unknown2:</info> %s', $resultAnimation['entry']['unknown2']) . "\n" .
                sprintf('        | <info>unknown3:</info> %s', $resultAnimation['entry']['unknown3']) . "\n" .
                sprintf('        | <info>unknown4:</info> %s', $resultAnimation['entry']['unknown4']) . "\n" .
                sprintf('        | <info>unknown5:</info> %s', $resultAnimation['entry']['unknown5']) . "\n" .
                sprintf('        | <info>boneId:</info> %s', $resultAnimation['entry']['boneId']) . "\n" .
                sprintf('        | <info>particleName:</info> %s', $resultAnimation['entry']['particleName']) . "\n" .
                sprintf('        | <info>particlePosition:</info> %s', print_r($resultAnimation['entry']['particlePosition'], true))
            );

            file_put_contents($outputTo . $animationName . ".json", \json_encode($resultAnimation, JSON_PRETTY_PRINT));

            $animationCount--;
        }

        //        return $animations;
    }
    private function extractBones($numberOfBones, &$entry, OutputInterface $output = null){

        $bones = [];
        while($numberOfBones > 0){

            $sequenceLabel = $this->toString($this->substr($entry, 0, 4));

            if ($sequenceLabel !== "SEQT" && $sequenceLabel !== "SEQU")
                throw new \Exception(
                    sprintf('Expected SEQT got: %s', $sequenceLabel)
                );

            $boneId = $this->toInt16($this->substr($entry, 0, 2));
            $frameType = $this->toInt8($this->substr($entry, 0, 1));
            $frames = $this->toInt16($this->substr($entry, 0, 2));
            $startTime = $this->toInt16($this->substr($entry, 0, 2));

            $resultBone = [
                'boneId' => $boneId,
                'frameType' => $frameType,
                'frameCount' => $frames,
                'startTime' => $startTime,
                'frames' => []
            ];

            if (!is_null($output)) $output->writeln(
                sprintf('          | <info>Bone Id:</info> %s', $boneId) . "\n" .
                sprintf('          | <info>Frame Type:</info> %s', $frameType) . "\n" .
                sprintf('          | <info>Frames:</info> %s', $frames) . "\n" .
                sprintf('          | <info>Start Time:</info> %s', $startTime)
            );

            if ($frameType > 2){

                if ($startTime > 0){
                    $unknown1 = $this->substr($entry, 0, 2);

                    $resultBone['unknown1'] = $unknown1;

                    if (!is_null($output)) $output->writeln(
                        sprintf('          | <info>?? Unknown:</info> %s', $unknown1)
                    );
                }

                $unknown2 = $this->substr($entry, 0, 2);
                $unknown3 = $this->substr($entry, 0, 2);
                $unknown4 = $this->substr($entry, 0, 2);

                $resultBone['unknown2'] = $unknown2;
                $resultBone['unknown3'] = $unknown3;
                $resultBone['unknown4'] = $unknown4;


                if (!is_null($output)) $output->writeln(
                    sprintf(
                        '          | <info>?? Unknown:</info> %s,%s,%s',
                        $unknown2,
                        $unknown3,
                        $unknown4
                    )
                );
            }

            /**
             * FRAMES
             */

            $this->game = $sequenceLabel == "SEQU" ? "mh1" : "mh2";

            $resultBone['frames'] = $this->extractFrames(
                $startTime,
                $frames,
                $frameType,
                $entry,
                $output
            );

//            if ($game == "mh1"){
//                $resultBone['lastFrameTime'] = $this->toFloat($this->substr($entry, 0, 4));
//            }

            $bones[] = $resultBone;


            $numberOfBones--;
        }

        return $bones;
    }

    private function extractFrames($startTime, $frames, $frameType, &$entry, OutputInterface $output = null){

        $resultFrames = [
            'frames' => []
        ];

        $index = 0;
        while($frames > 0){

            $resultFrame = [];

            if ($startTime == 0){

                // first frame == starTime
                if ($index == 0 && $frameType < 3){
                    $time = $startTime;
                }else{
                    $time = $this->toInt16($this->substr($entry, 0, 2));
                }

                $resultFrame['time'] = $time;

                if (!is_null($output)) $output->writeln(
                    sprintf('            | <info>Time:</info> %s', $time)
                );
            }

            if ($frameType < 3){

                $x = $this->toInt16($this->substr($entry, 0, 2));
                $y = $this->toInt16($this->substr($entry, 0, 2));
                $z = $this->toInt16($this->substr($entry, 0, 2));
                $w = $this->toInt16($this->substr($entry, 0, 2));

                $resultFrame['quat'] = [$x,$y,$z,$w];

                if (!is_null($output)) $output->writeln(
                    sprintf(
                        '            | <info>Quat:</info> %s,%s,%s,%s',
                        $x,
                        $y,
                        $z,
                        $w
                    )
                );
            }


            if ($frameType > 1){

                $x = $this->toInt16($this->substr($entry, 0, 2));
                $y = $this->toInt16($this->substr($entry, 0, 2));
                $z = $this->toInt16($this->substr($entry, 0, 2));

                $resultFrame['position'] = [$x,$y,$z];

                if (!is_null($output)) $output->writeln(
                    sprintf(
                        '            | <info>Position:</info> %s,%s,%s',
                        $x,
                        $y,
                        $z
                    )
                );
            }

            $resultFrames['frames'][] = $resultFrame;

            $frames--;
            $index++;
        }

        if ($this->game == "mh2"){
            $resultFrames['lastFrameTime'] = $this->toFloat($this->substr($entry, 0, 4));


            if (!is_null($output)) $output->writeln(
                sprintf('          | <info>Last frame time:</info> %s', $resultFrames['lastFrameTime'])
            );
        }

        return $resultFrames;
    }

    public function pack( $records ){

    }

}