<?php
namespace App\Service\Archive\Mdl;

use App\Service\NBinary;

class Build {

    private $offsets = [];
    private $offsetTable = [];

    public $keepOrder = false;

    /**
     * @param $mdls
     * @return null|string
     */
    public function build( $mdls ){

        $binary = new NBinary();

        $this->createMdlHeader($binary);

        $allTexNameOffsetPositions = [];

        $fistMdlEntryOffset = 0;
        $lastMdlObjectEntryOffset = 0;

        $lastMdlOffsetPosition = false;

        $mdlEntryOffset = 32;

        foreach ($mdls as $mdlIndex => $mdl) {

            if ($lastMdlOffsetPosition){
                $this->offsets[$lastMdlOffsetPosition] = $binary->current;
            }

            $lastMdlEntryOffset = $mdlEntryOffset;
            $mdlEntryOffset = $binary->current;

            if ($mdlIndex == 0){
                $fistMdlEntryOffset = $binary->current;
            }

            if ($mdlIndex == count($mdls) - 1){
                $lastMdlObjectEntryOffset = $binary->current;
            }

            //nextEntryIndexOffset, apply dummy value for now
            //point to the header FirstEntryIndexOffset
            $this->offsetTable[] = $binary->current;
            $lastMdlOffsetPosition = $binary->current;

            //set per default to 32 because single mdls will never overwrite the value
            $binary->write(32, NBinary::INT_32);

            //prevEntryIndexOffset, apply dummy value for now
            $this->offsetTable[] = $binary->current;
            $binary->write($lastMdlEntryOffset, NBinary::INT_32);

            //$entryOffset
            $this->offsetTable[] = $binary->current;
            $binary->write($binary->current + 8, NBinary::INT_32);

            //zero (actual padding?)
            $binary->write(0, NBinary::INT_32);

            $objectInfoFirstEntryOffset = $binary->current + 20;

            $rootEntryOffset = $binary->current;

            $firstObjectInfoOffsetPosition = 0;
            $lastObjectInfoOffsetPosition = 0;

            $this->createEntry($binary, $mdl, $firstObjectInfoOffsetPosition, $lastObjectInfoOffsetPosition);

            $rootBoneOffset = $binary->current;

            $this->createBone($binary, $mdl['bone'], $rootBoneOffset);

            $this->offsets[ $firstObjectInfoOffsetPosition ] = $binary->current;

            if (count($mdl['objects'])){

                $startOfObjectInfo = false;

                foreach ($mdl['objects'] as $index => $object) {

                    if (count($mdl['objects']) - 1 == $index){
                        $this->offsets[ $lastObjectInfoOffsetPosition ] = $binary->current;
                    }

                    $objectInfo = $object['objectInfo'];

                    $prevStartOfObjectInfo = $startOfObjectInfo;
                    $startOfObjectInfo = $binary->current;

                    //its a hack...
                    if (end($this->offsetTable) != $binary->current){
                        $this->offsetTable[] = $binary->current;
                    }

                    $binary->write(0, NBinary::INT_32); // nextObjectInfoOffset

                    $this->offsetTable[] = $binary->current;
                    $binary->write(0, NBinary::INT_32); // prevObjectInfoOffset

                    $this->offsetTable[] = $binary->current;
                    $binary->write(
                        $this->createBonesOffsets[ $objectInfo['objectParentBoneIndex'] ],
                        NBinary::INT_32
                    );

                    $this->offsetTable[] = $binary->current;
                    $objectOffsetPosition = $binary->current;
                    $binary->write(0, NBinary::INT_32);


                    $this->offsetTable[] = $binary->current;
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
                        $this->offsets[$startOfObjectInfo] = $binary->current;
                    }

                    //save objectInfo prevOffset
                    if ($index == 0){
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

        $binary->write($binary->getPadding("\x00", 4), NBinary::BINARY);

        //update table position
        $this->offsets[12] = $binary->current;
        $this->offsets[16] = $binary->current;

        //update table entry count
        $this->offsets[20] = count($this->offsetTable);

        //update first entry
        $this->offsets[32] = $fistMdlEntryOffset;

        //update last entry
        $this->offsets[36] = $lastMdlObjectEntryOffset;

        //generate offset table
        foreach ($this->offsetTable as $offset) {
            $binary->write($offset, NBinary::INT_32);
        }

        //update file size
        $this->offsets[8] = $binary->current;


        //correct offsets
        $binary->overwriteBatch($this->offsets, NBinary::INT_32);

        return $binary->binary;

    }


    private function createBoneTransDataIndex(NBinary $binary, $boneTransDataIndex ){

        $boneTrabsDataOffset = $binary->current;

        $binary->write($boneTransDataIndex['numBone'], NBinary::INT_32);

        //BoneTransDataOffset
        $this->offsetTable[] = $binary->current;
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
            $this->offsetTable[] = $binary->current;
            $binary->write(0, NBinary::INT_32);

            $binary->write($material['Color_ARGB1'], NBinary::HEX);
            $binary->write($material['Color_ARGB2'], NBinary::HEX);
        }

        $binary->write($binary->getPadding("\x00", 16), NBinary::BINARY);

        return [$materialOffset, $texNameOffsetPositions];
    }

    private function createObject(NBinary $binary, $object, $materialOffset, $materials, $boneTransDataOffset ){

        $this->offsetTable[] = $binary->current;
        $binary->write($materialOffset, NBinary::INT_32);

        //NumMaterials
        $binary->write(count($materials), NBinary::INT_32);

        if ($boneTransDataOffset == false){
            $binary->write(0, NBinary::INT_32);
        }else{
            $this->offsetTable[] = $binary->current;
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

    private $createBonesOffsets = [];
    private function createBone(NBinary $binary, $data, $rootBoneOffset, $parentBoneOffset = 0, &$index = 0 ){


        $this->createBonesOffsets[$index] = $binary->current;

        $possibleNextParentBoneOffset = $binary->current;

        $binary->write($data['unknown'], NBinary::HEX);

        $nextBrotherBoneOffsetPosition = 0; // never used
        if ($data['nextBrotherBone'] !== false){
            $nextBrotherBoneOffsetPosition = $binary->current;

            $this->offsetTable[] = $binary->current;
        }

        //nextBrotherBoneOffset (will be overwritten when needed)
        $binary->write(0, NBinary::INT_32);

        if ($index > 0){
            $this->offsetTable[] = $binary->current;
        }

        $binary->write($parentBoneOffset, NBinary::INT_32);

        $this->offsetTable[] = $binary->current;
        $binary->write($rootBoneOffset, NBinary::INT_32);

        //subBoneOffset
        if ($data['subBone'] !== false) {
            $this->offsetTable[] = $binary->current;
            $binary->write($binary->current + 176, NBinary::INT_32);
        }else{
            $binary->write(0, NBinary::INT_32);
        }

        $animationDataIndexOffsetPosition = 0; // never used
        if ($data['animationDataIndex'] !== false) {
            $this->offsetTable[] = $binary->current;
            $animationDataIndexOffsetPosition = $binary->current;
        }

        //animationDataIndexOffset
        $binary->write(0, NBinary::INT_32);

        $binary->write($data['boneName'], NBinary::HEX);
        $binary->write($data['matrix4X4_ParentChild'], NBinary::HEX);
        $binary->write($data['matrix4X4_WorldPos'], NBinary::HEX);

        if ($data['subBone'] !== false) {
            $index++;
            $this->createBone($binary, $data['subBone'], $rootBoneOffset, $possibleNextParentBoneOffset, $index);
        }

        if ($data['nextBrotherBone'] !== false){

            $this->offsets[$nextBrotherBoneOffsetPosition] = $binary->current;

            $index++;
            $this->createBone($binary, $data['nextBrotherBone'], $rootBoneOffset, $parentBoneOffset, $index);
        }

        if ($data['animationDataIndex'] !== false) {

            $this->offsets[$animationDataIndexOffsetPosition] = $binary->current;

            $this->createAnimationDataIndex($binary, $data['animationDataIndex'], $rootBoneOffset);
            $this->offsetTable[] = $binary->current;
        }
    }

    private function createAnimationDataIndex(NBinary $binary, $animationDataIndex, $rootBoneOffset ){

        $binary->write($animationDataIndex['numBone'], NBinary::INT_32);
        $binary->write($animationDataIndex['unknown'], NBinary::INT_32);

        $this->offsetTable[] = $binary->current;
        $binary->write($rootBoneOffset, NBinary::INT_32);

        //animationDataOffset
        $this->offsetTable[] = $binary->current;
        $animationDataOffsetPosition = $binary->current;
        $binary->write(0, NBinary::INT_32);

        //boneTransformOffset
        $this->offsetTable[] = $binary->current;
        $boneTransformOffsetPosition = $binary->current;
        $binary->write(0, NBinary::INT_32);

        $binary->write($animationDataIndex['zero'], NBinary::INT_32);

        $this->offsets[$animationDataOffsetPosition] = $binary->current;

        if (count($animationDataIndex['animationData'])){
            foreach ($animationDataIndex['animationData'] as $index => $data) {

                $binary->write($data['animationBoneId'], NBinary::INT_16);
                $binary->write($data['boneType'], NBinary::INT_16);

                //BoneOffset
                $this->offsetTable[] = $binary->current;
                $binary->write($this->createBonesOffsets[ $index ], NBinary::INT_32);
            }
        }

        $this->offsets[$boneTransformOffsetPosition] = $binary->current;
        if (count($animationDataIndex['boneTransform'])){
            foreach ($animationDataIndex['boneTransform'] as $boneTransform) {
                $binary->write($boneTransform, NBinary::HEX);

            }

            $binary->write($binary->getPadding("\x00", 16), NBinary::BINARY);

        }
    }

    private function createEntry(NBinary $binary, $mdl, &$firstObjectInfoOffsetPosition, &$lastObjectInfoOffsetPosition ){
        $this->offsetTable[] = $binary->current;

        $binary->write($binary->current + 32, NBinary::INT_32);
        $binary->write($mdl['entry']['zero3'], NBinary::HEX);
        $binary->write($mdl['entry']['unknown'], NBinary::HEX);

        $this->offsetTable[] = $binary->current;
        $firstObjectInfoOffsetPosition = $binary->current;
        $binary->write(0, NBinary::INT_32);

        $this->offsetTable[] = $binary->current;
        $lastObjectInfoOffsetPosition = $binary->current;

        $binary->write($mdl['entry']['lastObjectInfoOffset'], NBinary::INT_32);

        $binary->write($mdl['entry']['zero'], NBinary::INT_32);
    }

    public function createMdlHeader(NBinary $binary){

        //fourCC
        $binary->write("PMLC", NBinary::BINARY);

        //const, always 1
        $binary->write(1, NBinary::INT_32);

        //file size
        $binary->write(0, NBinary::INT_32);

        //offsetTable
        $binary->write(0, NBinary::INT_32);

        //offsetTable2, same as offsetTable
        $binary->write(0, NBinary::INT_32);

        //numTable
        $binary->write(0, NBinary::INT_32);

        $binary->write(0, NBinary::INT_32);
        $binary->write(0, NBinary::INT_32);

        //FirstEntryIndexOffset
        $this->offsetTable[] = $binary->current;
        $binary->write(0, NBinary::INT_32);

        //LastEntryIndexOffset
        $this->offsetTable[] = $binary->current;
        $binary->write(0, NBinary::INT_32);

        //padding
        $binary->write(0, NBinary::INT_32);
        $binary->write(0, NBinary::INT_32);

        return $binary;

    }
}
