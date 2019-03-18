<?php
namespace App\Service\Archive\Mdl;

use App\Service\NBinary;

class Build {

    public function build( $mdls ){

        $binary = new NBinary();

//        $mdlHeader = $this->createMdlHeader($binary, $mdls);

        foreach ($mdls as $mdl) {

            $this->createEntryIndex($binary);
            $this->createEntry($binary, $mdl);
            $this->createBone($binary, $mdl['bone']);


            if (count($mdl['objects'])){

                foreach ($mdl['objects'] as $object) {
                    $this->createObjectInfo($binary, $object['objectInfo']);
                    $this->createObject($binary, $object['object']);

                    if ($object['materials'] !== false){
                        $this->createMaterials($binary, $object['materials']);
                    }

                    if (count($object['boneTransDataIndex'])){

                        $this->createBoneTransDataIndex($binary, $object['boneTransDataIndex']);

                    }

                }

            }



        }





        return $binary->binary;

    }

    private function createBoneTransDataIndex(NBinary $binary, $boneTransDataIndex ){
        foreach ($boneTransDataIndex as $item) {
            $binary->write($item['numBone'], NBinary::INT_32);
            $binary->write($item['BoneTransDataOffset'], NBinary::INT_32);

            foreach ($item['matrix'] as $matrix) {
                $binary->write($matrix, NBinary::HEX);
            }

        }
    }


    private function createMaterials(NBinary $binary, $materials ){
        foreach ($materials as $material) {
            $binary->write($material['TexNameOffset'], NBinary::INT_32);
            $binary->write($material['Color_ARGB1'], NBinary::HEX);
            $binary->write($material['Color_ARGB2'], NBinary::HEX);

        }
    }

    private function createObject(NBinary $binary, $object ){
        $binary->write($object['MaterialOffset'], NBinary::INT_32);
        $binary->write($object['NumMaterials'], NBinary::INT_32);
        $binary->write($object['BoneTransDataIndexOffset'], NBinary::INT_32);

        $binary->write($object['unknown'], NBinary::INT_32);
        $binary->write($object['unknown2'], NBinary::INT_32);

        $binary->write($object['Position'], NBinary::HEX);

        $binary->write($object['modelChunkFlag'], NBinary::INT_32);
        $binary->write($object['modelChunkSize'], NBinary::INT_32);

        $binary->write($object['zero'], NBinary::INT_32);

        $binary->write($object['numMaterialIDs'], NBinary::INT_32);
        $binary->write($object['numFaceIndex'], NBinary::INT_32);

        $binary->write($object['boundingSphereXYZ'], NBinary::HEX);
        $binary->write($object['boundingSphereRadius'], NBinary::INT_32);
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
                $this->createNormal($binary, $vertex['normal']);
                $binary->write($vertex['Color_BGRA'], NBinary::HEX);

            }else if ($object['VertexElementType'] == 0x152){
                $this->createNormal( $binary, $vertex['normal']);
                $binary->write($vertex['Color_BGRA'], NBinary::HEX);
                $binary->write($vertex['tu'], NBinary::FLOAT_32);
                $binary->write($vertex['tv'], NBinary::FLOAT_32);

            }else if ($object['VertexElementType'] == 0x252){
                $this->createNormal( $binary, $vertex['normal']);
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
                $this->createNormal( $binary, $vertex['normal']);
                $binary->write($vertex['Color_BGRA'], NBinary::HEX);
                $binary->write($vertex['tu'], NBinary::FLOAT_32);
                $binary->write($vertex['tv'], NBinary::FLOAT_32);
            }else if ($object['VertexElementType'] == 0x125E){
                $binary->write($vertex['weight4'], NBinary::FLOAT_32);
                $binary->write($vertex['weight3'], NBinary::FLOAT_32);
                $binary->write($vertex['weight2'], NBinary::FLOAT_32);
                $binary->write($vertex['weight1'], NBinary::FLOAT_32);
                $binary->write($vertex['boneID4321'], NBinary::HEX);
                $this->createNormal( $binary, $vertex['normal']);
                $binary->write($vertex['Color_BGRA'], NBinary::HEX);
                $binary->write($vertex['tu'], NBinary::FLOAT_32);
                $binary->write($vertex['tv'], NBinary::FLOAT_32);
                $binary->write($vertex['tu2'], NBinary::FLOAT_32);
                $binary->write($vertex['tv2'], NBinary::FLOAT_32);
            }
        }
    }


    private function createNormal(NBinary $binary, $normal ){
        $binary->write($normal['x'], NBinary::INT_16);
        $binary->write($normal['y'], NBinary::INT_16);
        $binary->write($normal['z'], NBinary::INT_16);

        if ($binary->current % 4 > 0){
            $binary->write(str_repeat('00', $binary->current % 4), NBinary::HEX);
        }

    }

    private function createMaterialIDs(NBinary $binary, $mtlIds ){
        foreach ($mtlIds as $mtlId) {
            $binary->write($mtlId['BoundingBoxMinX'], NBinary::INT_32);
            $binary->write($mtlId['BoundingBoxMinY'], NBinary::INT_32);
            $binary->write($mtlId['BoundingBoxMinZ'], NBinary::INT_32);
            $binary->write($mtlId['BoundingBoxMaxX'], NBinary::INT_32);
            $binary->write($mtlId['BoundingBoxMaxY'], NBinary::INT_32);
            $binary->write($mtlId['BoundingBoxMaxZ'], NBinary::INT_32);
            $binary->write($mtlId['MaterialIDNumFace'], NBinary::INT_32);
            $binary->write($mtlId['MaterialID'], NBinary::INT_32);
            $binary->write($mtlId['StartFaceID'], NBinary::INT_32);
            $binary->write($mtlId['unknown'], NBinary::INT_32);
            $binary->write($mtlId['zero'], NBinary::INT_32);

        }
    }

    private function createObjectInfo(NBinary $binary, $objectInfo ){
        $binary->write($objectInfo['nextObjectInfoOffset'], NBinary::INT_32);
        $binary->write($objectInfo['prevObjectInfoOffset'], NBinary::INT_32);
        $binary->write($objectInfo['objectParentBoneOffset'], NBinary::INT_32);
        $binary->write($objectInfo['objectOffset'], NBinary::INT_32);
        $binary->write($objectInfo['rootEntryOffset'], NBinary::INT_32);
        $binary->write($objectInfo['zero'], NBinary::INT_32);
        $binary->write($objectInfo['unknown'], NBinary::INT_32);

    }

    private function createBone(NBinary $binary, $bone ){
        $binary->write($bone['unknown'], NBinary::HEX);
        $binary->write($bone['nextBrotherBoneOffset'], NBinary::INT_32);
        $binary->write($bone['parentBoneOffset'], NBinary::INT_32);
        $binary->write($bone['rootBoneOffset'], NBinary::INT_32);
        $binary->write($bone['subBoneOffset'], NBinary::INT_32);
        $binary->write($bone['animationDataIndexOffset'], NBinary::INT_32);
        $binary->write($bone['boneName'], NBinary::HEX);
        $binary->write($bone['matrix4X4_ParentChild'], NBinary::HEX);
        $binary->write($bone['matrix4X4_WorldPos'], NBinary::HEX);

        if ($bone['subBone'] !== false) {
            $this->createBone($binary, $bone['subBone']);
        }else if ($bone['nextBrotherBone'] !== false){
            $this->createBone($binary, $bone['nextBrotherBone']);
        }

        if ($bone['animationDataIndex'] !== false) {
            $this->createAnimationDataIndex($binary, $bone['animationDataIndex']);
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
            foreach ($animationDataIndex['animationData'] as $animationData) {
                $this->parseAnimationData($binary, $animationData);
            }
        }

        if (count($animationDataIndex['boneTransform'])){
            foreach ($animationDataIndex['boneTransform'] as $boneTransform) {
                $binary->write($boneTransform, NBinary::HEX);

            }
        }
    }

    private function parseAnimationData(NBinary $binary, $animationData ){
        $binary->write($animationData['animationBoneId'], NBinary::INT_16);
        $binary->write($animationData['boneType'], NBinary::INT_16);
        $binary->write($animationData['BoneOffset'], NBinary::INT_32);
    }


    private function createEntry(NBinary $binary, $mdl ){
        $binary->write($mdl['entry']['rootBoneOffset'], NBinary::INT_32);
        $binary->write($mdl['entry']['zero3'], NBinary::HEX);
        $binary->write($mdl['entry']['unknown'], NBinary::HEX);
        $binary->write($mdl['entry']['firstObjectInfoOffset'], NBinary::INT_32);
        $binary->write($mdl['entry']['lastObjectInfoOffset'], NBinary::INT_32);
        $binary->write($mdl['entry']['zero'], NBinary::HEX);

    }

    private function createEntryIndex(NBinary $binary ){

        //$extEntryIndexOffset, apply dummy value for now
        $binary->write(1234, NBinary::INT_32);

        //prevEntryIndexOffset, apply dummy value for now
        $binary->write(1234, NBinary::INT_32);

        //$entryOffset, apply dummy value for now
        $binary->write(1234, NBinary::INT_32);

        //zero
        $binary->write(0, NBinary::INT_32);
    }

    public function createMdlHeader( NBinary $binary, $mdls){

        //fourCC
        $binary->write("PMLC", NBinary::BINARY);

        //const
        $binary->write(1, NBinary::INT_32);

//        //file size, apply dummy value for now
//        $binary->write(1234, NBinary::INT_32);
//
//        //offsetTable, apply dummy value for now
//        $binary->write(1234, NBinary::INT_32);
//
//        //offsetTable2, apply dummy value for now
//        $binary->write(1234, NBinary::INT_32);
//
//        //numTable, apply dummy value for now
//        $binary->write(1234, NBinary::INT_32);
//
//
//        $binary->write(count($mdls), NBinary::INT_32);


    }
}
