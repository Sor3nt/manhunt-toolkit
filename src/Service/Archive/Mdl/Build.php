<?php
namespace App\Service\Archive\Mdl;

use App\Service\NBinary;

class Build {

    private $offsets = [];
    private $offsetTable = [];

    public function build( $mdls ){

        $binary = new NBinary();

        $this->createMdlHeader($binary, $mdls, 134504);

        $allTexNameOffsetPositions = [];
        $fistMdlObjectEntryOffset = 0;

        foreach ($mdls as $mdlIndex => $mdl) {

            $this->createEntryIndex($binary);

            $objectInfoFirstEntryOffset = $binary->current + 20;

            $rootEntryOffset = $binary->current;

            if ($mdlIndex == 0){
                $fistMdlObjectEntryOffset = $binary->current + 20;
            }

            $this->createEntry($binary, $mdl);

            $rootBoneOffset = $binary->current;

            $this->createBone($binary, $mdl['bone'], $rootBoneOffset);

            if (count($mdl['objects'])){

                $startOfObjectInfo = false;

                foreach ($mdl['objects'] as $index => $object) {
//                    var_dump($binary->current);

                    $prevStartOfObjectInfo = $startOfObjectInfo;
                    $startOfObjectInfo = $binary->current;

                    $objectInfo = $object['objectInfo'];



                    if (count($mdl['objects']) - 1 == $index){

                        if ($fistMdlObjectEntryOffset == $binary->current){
                            //wenn next gleich first ist, kein offset
                            var_dump($objectInfo['nextObjectInfoOffset']);

                            $this->checkShit($binary->current);

                        }
                    }

                    $binary->write(0, NBinary::INT_32); // nextObjectInfoOffset


//                    if ($index == 0){
                        $this->checkShit($binary->current);
//                    }

                    $binary->write(0, NBinary::INT_32); // prevObjectInfoOffset

                    $this->checkShit($binary->current);
                    $binary->write(
                        $this->createBonesOffsets[ $objectInfo['objectParentBoneIndex'] ],
                        NBinary::INT_32
                    );


                    $this->checkShit($binary->current);
                    $objectOffsetPosition = $binary->current;
                    $binary->write(0, NBinary::INT_32);

                    $this->checkShit($binary->current);
                    $binary->write($rootEntryOffset, NBinary::INT_32);


                    $binary->write($objectInfo['zero'], NBinary::INT_32);
                    $binary->write($objectInfo['unknown'], NBinary::INT_32);

                    $binary->write(0, NBinary::INT_32);

                    $materialOffset = false;
                    if ($object['materials'] !== false){
                        list($materialOffset, $texNameOffsetPositions) = $this->createMaterials($binary, $object['materials']);

                        foreach ($texNameOffsetPositions as $texNameOffsetPosition) {
                            $allTexNameOffsetPositions[] = $texNameOffsetPosition;

                        }
                    }else{
                        $binary->write(0, NBinary::INT_32);
                    }



                    $boneTrabsDataOffset = false;
                    if ($object['boneTransDataIndex']){
                        $boneTrabsDataOffset = $this->createBoneTransDataIndex($binary, $object['boneTransDataIndex']);
                    }


                    $this->offsets[$objectOffsetPosition] = $binary->current;

                    $this->createObject($binary, $object['object'], $materialOffset, $object['materials'], $boneTrabsDataOffset);

                    //save objectInfo nextOffset
                    if (count($mdl['objects']) - 1 == $index){
                        $this->offsets[$startOfObjectInfo] = $objectInfoFirstEntryOffset;
                    }else{
                        $this->checkShit($binary->current);
                        $this->offsets[$startOfObjectInfo] = $binary->current;
                    }

                    //save objectInfo prevOffset
                    if ($index == 0){
//                        $this->checkShit($objectInfoFirstEntryOffset);
                        $this->offsets[$startOfObjectInfo + 4] = $objectInfoFirstEntryOffset;
                    }else{
                        $this->offsets[$startOfObjectInfo + 4] = $prevStartOfObjectInfo;
                    }

                }

            }
        }

        $binary->write($binary->getPadding("\x00", 16), NBinary::BINARY);

        //generate texture names
        foreach ($allTexNameOffsetPositions as $allTexNameOffsetPosition) {
            $this->offsets[ $allTexNameOffsetPosition['position'] ] = $binary->current;
            $binary->write($allTexNameOffsetPosition['name'] . "\x00", NBinary::BINARY);
        }


//        var_dump($allTexNameOffsetPositions);
//        exit;

//        var_dump($binary->current);
        foreach ($this->offsets as $offset => $value) {
            $binary->current = $offset;
            $binary->overwrite($value, NBinary::INT_32);
        }


        file_put_contents("test.mdl", $binary->binary);
        exit;
//        return $binary->binary;

    }


    private function createBoneTransDataIndex(NBinary $binary, $boneTransDataIndex ){

        $boneTrabsDataOffset = $binary->current;

        $binary->write($boneTransDataIndex['numBone'], NBinary::INT_32);

        //BoneTransDataOffset
        $this->checkShit($binary->current);
        $binary->write($binary->current + 12, NBinary::INT_32);

        $binary->write($binary->getPadding("\x00", 16), NBinary::BINARY);

        foreach ($boneTransDataIndex['matrix'] as $matrix) {
            $binary->write($matrix, NBinary::HEX);
        }

        return $boneTrabsDataOffset;
    }


    private function createMaterials(NBinary $binary, $materials ){
        $materialOffset = $binary->current;

        $texNameOffsetPositions = [];
        foreach ($materials as $material) {
            $texNameOffsetPositions[] = [ 'position' => $binary->current, 'name' => $material['TexName']];

            //TexNameOffset
            $this->checkShit($binary->current);
            $binary->write(0, NBinary::INT_32);

            $binary->write($material['Color_ARGB1'], NBinary::HEX);
            $binary->write($material['Color_ARGB2'], NBinary::HEX);
        }

        $binary->write($binary->getPadding("\x00", 16), NBinary::BINARY);

        return [$materialOffset, $texNameOffsetPositions];
    }

    private function createObject(NBinary $binary, $object, $materialOffset, $materials, $boneTransDataOffset ){

        $this->checkShit($binary->current);
        $binary->write($materialOffset, NBinary::INT_32);

        //NumMaterials
        $binary->write(count($materials), NBinary::INT_32);

        if ($boneTransDataOffset == false){
            $binary->write(0, NBinary::INT_32);
        }else{
            $this->checkShit($binary->current);
            $binary->write($boneTransDataOffset, NBinary::INT_32);
        }

        $binary->write($object['unknown'], NBinary::HEX);
        $binary->write($object['unknown2'], NBinary::HEX);

        $binary->write($object['Position'], NBinary::HEX);

        $binary->write($object['modelChunkFlag'], NBinary::INT_32);
        $binary->write($object['modelChunkSize'], NBinary::INT_32);

        $binary->write($object['zero'], NBinary::INT_32);

        //numMaterialIDs
        $binary->write(count($materials), NBinary::INT_32);
        $binary->write($object['numFaceIndex'], NBinary::INT_32);

        $binary->write($object['boundingSphereXYZ'], NBinary::HEX);
        $binary->write($object['boundingSphereRadius'], NBinary::FLOAT_32);
        $binary->write($object['boundingSphereScale'], NBinary::HEX);

        $binary->write($object['numVertex'], NBinary::INT_32);
        $binary->write($object['zero2'], NBinary::HEX);
        $binary->write($object['PerVertexElementSize'], NBinary::INT_32);
        $binary->write($object['unknown4'], NBinary::HEX);
        $binary->write($object['VertexElementType'], NBinary::INT_32);
        $binary->write($object['unknown5'], NBinary::HEX);

        $this->createMaterialIDs($binary, $object['mtlIds']);


        foreach ($object['faceindex'] as $faceinde) {
            $binary->write($faceinde, NBinary::INT_16);
        }

        foreach ($object['vertex'] as $vertex) {
            $binary->write($vertex['x'], NBinary::FLOAT_32);
            $binary->write($vertex['y'], NBinary::FLOAT_32);
            $binary->write($vertex['z'], NBinary::FLOAT_32);

            if ($object['VertexElementType'] == 0x52) {
                $normal = $this->createNormal($vertex['normal']);
                $binary->concat($normal);
                $binary->write($vertex['Color_BGRA'], NBinary::HEX);

            }else if ($object['VertexElementType'] == 0x152){
                $normal = $this->createNormal($vertex['normal']);
                $binary->concat($normal);
                $binary->write($vertex['Color_BGRA'], NBinary::HEX);
                $binary->write($vertex['tu'], NBinary::FLOAT_32);
                $binary->write($vertex['tv'], NBinary::FLOAT_32);

            }else if ($object['VertexElementType'] == 0x252){
                $normal = $this->createNormal( $vertex['normal']);
                $binary->concat($normal);
                $binary->write($vertex['Color_BGRA'], NBinary::HEX);
                $binary->write($vertex['tu'], NBinary::FLOAT_32);
                $binary->write($vertex['tv'], NBinary::FLOAT_32);
                $binary->write($vertex['tu2'], NBinary::FLOAT_32);
                $binary->write($vertex['tv2'], NBinary::FLOAT_32);

            }else if ($object['VertexElementType'] == 0x115E){

                $binary->write($vertex['weight4'], NBinary::FLOAT_32);
                $binary->write($vertex['weight3'], NBinary::FLOAT_32);
                $binary->write($vertex['weight2'], NBinary::FLOAT_32);
                $binary->write($vertex['weight1'], NBinary::FLOAT_32);
                $binary->write($vertex['boneID4321'], NBinary::HEX);
                $normal = $this->createNormal( $vertex['normal']);
                $binary->concat($normal);

                $binary->write($vertex['Color_BGRA'], NBinary::HEX);
                $binary->write($vertex['tu'], NBinary::FLOAT_32);
                $binary->write($vertex['tv'], NBinary::FLOAT_32);

            }else if ($object['VertexElementType'] == 0x125E){
                $binary->write($vertex['weight4'], NBinary::FLOAT_32);
                $binary->write($vertex['weight3'], NBinary::FLOAT_32);
                $binary->write($vertex['weight2'], NBinary::FLOAT_32);
                $binary->write($vertex['weight1'], NBinary::FLOAT_32);
                $binary->write($vertex['boneID4321'], NBinary::HEX);
                $normal = $this->createNormal( $vertex['normal']);
                $binary->concat($normal);
                $binary->write($vertex['Color_BGRA'], NBinary::HEX);
                $binary->write($vertex['tu'], NBinary::FLOAT_32);
                $binary->write($vertex['tv'], NBinary::FLOAT_32);
                $binary->write($vertex['tu2'], NBinary::FLOAT_32);
                $binary->write($vertex['tv2'], NBinary::FLOAT_32);
            }

        }

        $binary->write($binary->getPadding("\x00", 16), NBinary::BINARY);

    }


    private function createNormal($normal ){
        $binary = new NBinary();
        $binary->write($normal['x'], NBinary::INT_16);
        $binary->write($normal['y'], NBinary::INT_16);
        $binary->write($normal['z'], NBinary::INT_16);
        $binary->write(0, NBinary::INT_16);

//        if ($binary->current % 4 > 0){
//            $binary->write(str_repeat('00', $binary->current % 4), NBinary::HEX);
//        }

        return $binary;
    }

    private function createMaterialIDs(NBinary $binary, $mtlIds ){

        foreach ($mtlIds as $mtlId) {
            $binary->write($mtlId['BoundingBoxMinX'], NBinary::FLOAT_32);
            $binary->write($mtlId['BoundingBoxMinY'], NBinary::FLOAT_32);
            $binary->write($mtlId['BoundingBoxMinZ'], NBinary::FLOAT_32);
            $binary->write($mtlId['BoundingBoxMaxX'], NBinary::FLOAT_32);
            $binary->write($mtlId['BoundingBoxMaxY'], NBinary::FLOAT_32);
            $binary->write($mtlId['BoundingBoxMaxZ'], NBinary::FLOAT_32);
            $binary->write($mtlId['MaterialIDNumFace'], NBinary::INT_16);
            $binary->write($mtlId['MaterialID'], NBinary::INT_16);
            $binary->write($mtlId['StartFaceID'], NBinary::INT_16);
            $binary->write($mtlId['unknown'], NBinary::INT_16);
            $binary->write($mtlId['zero'], NBinary::HEX);

        }
    }

    private $curShit = 0;
    private function checkShit( $val ){

        $shit = [32,36,48,52,56,64,84,88,108,112,292,296,300,304,308,484,488,492,676,680,684,868,872,876,1060,1064,1068,1252,1256,1260,1448,1452,1636,1640,1644,1648,1832,1836,1840,2020,2024,2028,2216,2220,2404,2408,2412,2596,2600,2604,2788,2792,2796,2980,2984,2988,3172,3176,3180,3364,3368,3372,3556,3560,3564,3748,3752,3756,3940,3944,3948,4132,4136,4140,4328,4332,4520,4524,4528,4540,4548,4556,4564,4572,4580,4588,4596,4604,4612,4620,4628,4636,4644,4652,4660,4668,4676,4684,4692,4700,4708,4716,5456,5460,5464,5468,5472,5488,5500,5512,5524,5536,5548,5560,5572,5584,47904,47908,47912,47916,47920,47936,47952,53888,53892,53896,53900,53904,53920,53936,59264,59268,59272,59276,59280,59296,59308,59332,60816,60824,84624,84628,84632,84636,84640,84656,84668,84680,84708,86192,86200,110608,110612,110616,110620,110624,110640,110660,112144,112152,117456,117460,117464,117468,117472,117488,117508,118992,119000,124304,124308,124312,124316,124320,124336,124348,124360,124388,125872,125880];


        if ($val == $shit[$this->curShit]){
            $this->curShit++;
        }else{
           throw new \Exception("need " . $shit[$this->curShit] . " got " . $val);
        }

    }

    private $createBonesOffsets = [];
    private function createBone(NBinary $binary, $data, $rootBoneOffset, $parentBoneOffset = 0, &$index = 0, $fromNext= false, $fromSub = false ){





        $this->createBonesOffsets[$index] = $binary->current;

        $possibleNextParentBoneOffset = $binary->current;

        $binary->write($data['unknown'], NBinary::HEX);

        $nextBrotherBoneOffsetPosition = 0; // never used
        if ($data['nextBrotherBone'] !== false){
            $nextBrotherBoneOffsetPosition = $binary->current;

            $this->checkShit($binary->current);
            echo "10 -> " . ($binary->current ) . " sub ";
            echo $data['subBone'] === false ? "no" : "yes";
            echo " anim ";
            echo $data['animationDataIndex']  === false ? "no" : "yes";
            echo " index: " .$index . "\n";
        }

        //nextBrotherBoneOffset (will be overwritten when needed)
        $binary->write(0, NBinary::INT_32);

        if ($index > 0){
            echo "11 -> " . ($binary->current ) . " parent " . $parentBoneOffset ." root ". $rootBoneOffset . " index: " .$index  . "\n";
            $this->checkShit($binary->current);

        }

        $binary->write($parentBoneOffset, NBinary::INT_32);

//        if ($parentBoneOffset == 0){

        $this->offsetTable[] = $binary->current;
        echo "8 -> " . ($binary->current ) . " parent " . $parentBoneOffset ." root ". $rootBoneOffset . " index: " .$index . "\n";
        $this->checkShit($binary->current);
        $binary->write($rootBoneOffset, NBinary::INT_32);


        //subBoneOffset
        if ($data['subBone'] !== false) {
            echo "9 -> " . ($binary->current ) . " parent " . $parentBoneOffset ." root ". $rootBoneOffset . " index: " .$index . "\n";
            $this->checkShit($binary->current);
            $binary->write($binary->current + 176, NBinary::INT_32);
        }else{
            $binary->write(0, NBinary::INT_32);
        }

        $animationDataIndexOffsetPosition = 0; // never used
        if ($data['animationDataIndex'] !== false) {
            echo "9a -> " . ($binary->current ) . " parent " . $parentBoneOffset ." root ". $rootBoneOffset . " index: " .$index . "\n";
            $this->checkShit($binary->current);
            $animationDataIndexOffsetPosition = $binary->current;
        }

        //animationDataIndexOffset
        $binary->write(0, NBinary::INT_32);

        $binary->write($data['boneName'], NBinary::HEX);
        $binary->write($data['matrix4X4_ParentChild'], NBinary::HEX);
        $binary->write($data['matrix4X4_WorldPos'], NBinary::HEX);

        if ($data['subBone'] !== false) {
            $index++;
            $this->createBone($binary, $data['subBone'], $rootBoneOffset, $possibleNextParentBoneOffset, $index, false, true);
        }

        if ($data['nextBrotherBone'] !== false){

            $this->offsets[$nextBrotherBoneOffsetPosition] = $binary->current;

            $index++;
            $this->createBone($binary, $data['nextBrotherBone'], $rootBoneOffset, $parentBoneOffset, $index, true, false);
        }

        if ($data['animationDataIndex'] !== false) {

            $this->offsets[$animationDataIndexOffsetPosition] = $binary->current;

            $this->createAnimationDataIndex($binary, $data['animationDataIndex'], $rootBoneOffset);
            $this->checkShit($binary->current);

        }

    }


    private function createAnimationDataIndex(NBinary $binary, $animationDataIndex, $rootBoneOffset ){

        $binary->write($animationDataIndex['numBone'], NBinary::INT_32);
        $binary->write($animationDataIndex['unknown'], NBinary::INT_32);

        $this->checkShit($binary->current);
        $binary->write($rootBoneOffset, NBinary::INT_32);

        //animationDataOffset
        $this->checkShit($binary->current);
        $animationDataOffsetPosition = $binary->current;
        $binary->write(0, NBinary::INT_32);


        //boneTransformOffset
        $this->checkShit($binary->current);
        $boneTransformOffsetPosition = $binary->current;
        $binary->write(0, NBinary::INT_32);

        $binary->write($animationDataIndex['zero'], NBinary::INT_32);

        $this->offsets[$animationDataOffsetPosition] = $binary->current;

        if (count($animationDataIndex['animationData'])){
            foreach ($animationDataIndex['animationData'] as $index => $data) {

                $binary->write($data['animationBoneId'], NBinary::INT_16);
                $binary->write($data['boneType'], NBinary::INT_16);

                //BoneOffset
                $this->checkShit($binary->current);
                $binary->write($this->createBonesOffsets[ $index ], NBinary::INT_32);

            }
        }

        $this->offsets[$boneTransformOffsetPosition] = $binary->current;
        if (count($animationDataIndex['boneTransform'])){
            foreach ($animationDataIndex['boneTransform'] as $boneTransform) {
                $binary->write($boneTransform, NBinary::HEX);

            }
        }

    }


    private function createEntry(NBinary $binary, $mdl ){
        $this->checkShit($binary->current);
        $binary->write($mdl['entry']['rootBoneOffset'], NBinary::INT_32);
        $binary->write($mdl['entry']['zero3'], NBinary::HEX);
        $binary->write($mdl['entry']['unknown'], NBinary::HEX);

        $this->offsetTable[] = $binary->current;
        echo "6 -> " . ($binary->current ) . "\n";
        $this->checkShit($binary->current);
        $binary->write($mdl['entry']['firstObjectInfoOffset'], NBinary::INT_32);

        $this->offsetTable[] = $binary->current;
        echo "7 -> " . ($binary->current ) . "\n";
        $this->checkShit($binary->current);
        $binary->write($mdl['entry']['lastObjectInfoOffset'], NBinary::INT_32);

        $binary->write($mdl['entry']['zero'], NBinary::INT_32);
    }

    private function createEntryIndex( NBinary $binary ){

        //nextEntryIndexOffset, apply dummy value for now
        //TODO
        $this->checkShit($binary->current);
        $binary->write(32, NBinary::INT_32);

        //prevEntryIndexOffset, apply dummy value for now
        //TODO

        $this->offsetTable[] = $binary->current;
        echo "3 -> " . $binary->current . "\n";
        $this->checkShit($binary->current);
        $binary->write(32, NBinary::INT_32);


        //$entryOffset
        $this->offsetTable[] = $binary->current;
        echo "4 -> " . ($binary->current ) . "\n";
        $this->checkShit($binary->current);
        $binary->write($binary->current + 8, NBinary::INT_32);


        //zero
        $binary->write(0, NBinary::INT_32);
    }

    public function createMdlHeader(NBinary $binary, $mdls, $fileSize){

        //fourCC
        $binary->write("PMLC", NBinary::BINARY);

        //const
        $binary->write(1, NBinary::INT_32);

//        //file size, apply dummy value for now
        $binary->write($fileSize, NBinary::INT_32);
//
//        //offsetTable, apply dummy value for now
        $binary->write(133784, NBinary::INT_32);
//
//        //offsetTable2, apply dummy value for now
        $binary->write(133784, NBinary::INT_32);
//
//        //numTable, apply dummy value for now
        $binary->write(180, NBinary::INT_32);
//
//
        $binary->write(0, NBinary::INT_32);
        $binary->write(0, NBinary::INT_32);

        $this->offsetTable[] = $binary->current;
        echo "0 -> " . $binary->current . "\n";
        $this->checkShit($binary->current);
        $binary->write(48, NBinary::INT_32);

        $this->offsetTable[] = $binary->current;
        echo "1 -> " . $binary->current . "\n";
        $this->checkShit($binary->current);
        $binary->write(48, NBinary::INT_32);

        $binary->write(0, NBinary::INT_32);
        $binary->write(0, NBinary::INT_32);

        return $binary;

    }
}
