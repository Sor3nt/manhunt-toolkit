<?php
namespace App\Service\Archive\Mdl;

use App\Service\NBinary;

class Build {

    private $nextObjectOffsetPosition;


    private $offsets = [];

    public function build( $mdls ){

        $binary = new NBinary();

        $this->createMdlHeader($binary, $mdls, 134504);

        foreach ($mdls as $mdlIndex => $mdl) {

            $this->createEntryIndex($binary);

            $objectInfoFirstEntryOffset = $binary->current + 20;

            $rootEntryOffset = $binary->current;

            $this->createEntry($binary, $mdl);

            $rootBoneOffset = $binary->current;

            $this->createBone($binary, $mdl['bone'], $rootBoneOffset);

            if (count($mdl['objects'])){

                $startOfObjectInfo = false;

                foreach ($mdl['objects'] as $index => $object) {
                    $prevStartOfObjectInfo = $startOfObjectInfo;
                    $startOfObjectInfo = $binary->current;

                    $objectInfo = $object['objectInfo'];

                    $binary->write(0, NBinary::INT_32); // nextObjectInfoOffset
                    $binary->write(0, NBinary::INT_32); // prevObjectInfoOffset

                    $binary->write(
                        $this->createBonesOffsets[ $objectInfo['objectParentBoneIndex'] ],
                        NBinary::INT_32
                    );


                    $objectOffsetPosition = $binary->current;
                    $binary->write(0, NBinary::INT_32);

                    $binary->write($rootEntryOffset, NBinary::INT_32);
                    $binary->write($objectInfo['zero'], NBinary::INT_32);
                    $binary->write($objectInfo['unknown'], NBinary::INT_32);

                    $binary->write(0, NBinary::INT_32);

                    if ($object['materials'] !== false){
                        $this->createMaterials($binary, $object['materials']);
                    }else{
                        $binary->write(0, NBinary::INT_32);
                    }

                    if ($object['boneTransDataIndex']){
                        $this->createBoneTransDataIndex($binary, $object['boneTransDataIndex']);
                    }

                    $this->offsets[$objectOffsetPosition] = $binary->current;

                    $this->createObject($binary, $object['object']);

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

        foreach ($this->offsets as $offset => $value) {
            $binary->current = $offset;
            $binary->overwrite($value, NBinary::INT_32);
        }


        file_put_contents("test.mdl", $binary->binary);
        exit;
//        return $binary->binary;

    }


    private function createBoneTransDataIndex(NBinary $binary, $boneTransDataIndex ){
        $binary->write($boneTransDataIndex['numBone'], NBinary::INT_32);
        $binary->write($boneTransDataIndex['BoneTransDataOffset'], NBinary::INT_32);


        $binary->write($binary->getPadding("\x00", 16), NBinary::BINARY);

        foreach ($boneTransDataIndex['matrix'] as $matrix) {
            $binary->write($matrix, NBinary::HEX);
        }

    }


    private function createMaterials(NBinary $binary, $materials ){
        foreach ($materials as $material) {
            $binary->write($material['TexNameOffset'], NBinary::INT_32);
            $binary->write($material['Color_ARGB1'], NBinary::HEX);
            $binary->write($material['Color_ARGB2'], NBinary::HEX);
        }

        $binary->write($binary->getPadding("\x00", 16), NBinary::BINARY);


    }

    private function createObject(NBinary $binary, $object ){

        $binary->write($object['MaterialOffset'], NBinary::INT_32);
        $binary->write($object['NumMaterials'], NBinary::INT_32);
        $binary->write($object['BoneTransDataIndexOffset'], NBinary::INT_32);

        $binary->write($object['unknown'], NBinary::HEX);
        $binary->write($object['unknown2'], NBinary::HEX);

        $binary->write($object['Position'], NBinary::HEX);

        $binary->write($object['modelChunkFlag'], NBinary::INT_32);
        $binary->write($object['modelChunkSize'], NBinary::INT_32);

        $binary->write($object['zero'], NBinary::INT_32);

        $binary->write($object['numMaterialIDs'], NBinary::INT_32);
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

    private $createBonesOffsets = [];
    private function createBone(NBinary $binary, $data, $rootBoneOffset, $parentBoneOffset = 0, &$index = 0 ){

        $this->createBonesOffsets[$index] = $binary->current;


        $possibleNextParentBoneOffset = $binary->current;

        $binary->write($data['unknown'], NBinary::HEX);

        $nextBrotherBoneOffsetPosition = 0; // never used
        if ($data['nextBrotherBone'] !== false){
            $nextBrotherBoneOffsetPosition = $binary->current;
        }

        //nextBrotherBoneOffset (will be overwritten when needed)
        $binary->write(0, NBinary::INT_32);

        $binary->write($parentBoneOffset, NBinary::INT_32);
        $binary->write($rootBoneOffset, NBinary::INT_32);

        //subBoneOffset
        if ($data['subBone'] !== false) {
            $binary->write($binary->current + 176, NBinary::INT_32);
        }else{
            $binary->write(0, NBinary::INT_32);
        }

        $animationDataIndexOffsetPosition = 0; // never used
        if ($data['animationDataIndex'] !== false) {
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

            $this->createAnimationDataIndex($binary, $data['animationDataIndex']);
        }

    }


    private function createAnimationDataIndex(NBinary $binary, $animationDataIndex ){

        $binary->write($animationDataIndex['numBone'], NBinary::INT_32);
        $binary->write($animationDataIndex['unknown'], NBinary::INT_32);
        $binary->write($animationDataIndex['rootBoneOffset'], NBinary::INT_32);
        $binary->write($animationDataIndex['animationDataOffset'], NBinary::INT_32);
        $binary->write($animationDataIndex['boneTransformOffset'], NBinary::INT_32);
        $binary->write($animationDataIndex['zero'], NBinary::INT_32);

        if (count($animationDataIndex['animationData'])){
            foreach ($animationDataIndex['animationData'] as $data) {
                $animationData = $this->parseAnimationData($data);
                $binary->concat($animationData);
            }
        }

        if (count($animationDataIndex['boneTransform'])){
            foreach ($animationDataIndex['boneTransform'] as $boneTransform) {
                $binary->write($boneTransform, NBinary::HEX);

            }
        }
    }

    private function parseAnimationData($animationData ){
        $binary = new NBinary();
        $binary->write($animationData['animationBoneId'], NBinary::INT_16);
        $binary->write($animationData['boneType'], NBinary::INT_16);
        $binary->write($animationData['BoneOffset'], NBinary::INT_32);
        return $binary;
    }


    private function createEntry(NBinary $binary, $mdl ){
        $binary->write($mdl['entry']['rootBoneOffset'], NBinary::INT_32);
        $binary->write($mdl['entry']['zero3'], NBinary::HEX);
        $binary->write($mdl['entry']['unknown'], NBinary::HEX);
        $binary->write($mdl['entry']['firstObjectInfoOffset'], NBinary::INT_32);
        $binary->write($mdl['entry']['lastObjectInfoOffset'], NBinary::INT_32);
        $binary->write($mdl['entry']['zero'], NBinary::INT_32);
    }

    private function createEntryIndex( NBinary $binary ){

        //$extEntryIndexOffset, apply dummy value for now
        $binary->write(32, NBinary::INT_32);

        //prevEntryIndexOffset, apply dummy value for now
        $binary->write(32, NBinary::INT_32);

        //$entryOffset, apply dummy value for now
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

        $binary->write(48, NBinary::INT_32);
        $binary->write(48, NBinary::INT_32);

        $binary->write(0, NBinary::INT_32);
        $binary->write(0, NBinary::INT_32);

        return $binary;

    }
}
