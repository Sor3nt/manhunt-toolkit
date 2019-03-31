<?php
namespace App\Service\Archive\Mdl;

use App\MHT;
use App\Service\NBinary;

class Extract {

    public function get( NBinary $binary ){

        $mdlHeader = $this->parseMdlHeader($binary);


        $results = [];
        do{

            $result = [];

            $entryIndex = $this->parseEntryIndex($binary);

            $entry = $this->parseEntry($binary);
            $bone = $this->parseBone($binary);

            $result['entryIndex'] = $entryIndex;
            $result['entry'] = $entry;
            $result['bone'] = $bone;
            $result['objects'] = [];

            if ($entry['firstObjectInfoOffset'] != $entry['objectInfoIndexOffset']){

                $binary->current = $entry['firstObjectInfoOffset'];

                do{
                    $objectRow = [
                        'materials' => false,
                        'boneTransDataIndex' => false,
                    ];

                    $objectInfo = $this->parseObjectInfo($binary);

                    $binary->current = $objectInfo['objectOffset'];

                    $object = $this->parseObject($binary);

                    $objectRow['objectInfo'] = $objectInfo;
                    $objectRow['object'] = $object;

                    if ($object['MaterialOffset'] != 0){
                        $binary->current = $object['MaterialOffset'];

                        $materials = $this->parseMaterial($binary, $object['NumMaterials']);
                        $objectRow['materials'] = $materials;
                    }

                    if ($object['BoneTransDataIndexOffset'] != 0){

                        $boneTransDataIndex = $this->parseBoneTransDataIndex($binary, $object['BoneTransDataIndexOffset']);
                        $objectRow['boneTransDataIndex'] = $boneTransDataIndex;
                    }

                    $binary->current = $objectInfo['nextObjectInfoOffset'];

                    $result['objects'][] = $objectRow;

                }while($objectInfo['nextObjectInfoOffset'] != $entry['objectInfoIndexOffset']);
            }

            if ($entryIndex['nextEntryIndexOffset'] != 0x20){
                $binary->current = $entryIndex['nextEntryIndexOffset'];
            }

            $results[] = $result;
        }while($entryIndex['nextEntryIndexOffset'] != 0x20);


        $table = $this->parseTable($binary, $mdlHeader);

        return $this->convertEntriesToSingleMdl( $results );
    }

    public function convertEntriesToSingleMdl( $mdls ){
        $build = new Build();

        $singleMdls = [];

        foreach ($mdls as $mdl) {
            $singleMdls[] = $build->build([$mdl]);
        }

        return $singleMdls;
    }


    private function parseTable(NBinary $binary, $mdlHeader ){
        $binary->current = $mdlHeader['offsetTable'];

        $offsets = [];
        for($i = 0; $i < $mdlHeader['numTable']; $i++){
            $offsets[] = $binary->consume(4, NBinary::INT_32);
        }

        return $offsets;
    }

    private function parseBoneTransDataIndex(NBinary $binary, $boneTransDataIndexOffset ){

        $binary->current = $boneTransDataIndexOffset;

        $data = [
            'numBone' => $binary->consume(4, NBinary::INT_32),
            'BoneTransDataOffset' => $binary->consume(4, NBinary::INT_32),
            'matrix' => []
        ];

        $binary->current = $data['BoneTransDataOffset'];

        for ($i = 0; $i < $data['numBone']; $i++){
            $data['matrix'][] = $binary->consume(4 * 16, NBinary::HEX); // 16 floats
        }

        return $data;
    }

    private function parseMaterial(NBinary $binary, $numMaterials ){
        $materials = [];

        for($i = 0; $i < $numMaterials; $i++){

            $material = [
                'TexNameOffset' => $binary->consume(4, NBinary::INT_32),
                'Color_ARGB1' => $binary->consume(4, NBinary::HEX),
                'Color_ARGB2' => $binary->consume(4, NBinary::HEX),
            ];

            $nextMaterialOffset = $binary->current;

            $binary->current = $material['TexNameOffset'];
            $material['TexName'] = $binary->getString();
            $materials[] = $material;

            $binary->current = $nextMaterialOffset;

        }

        return $materials;
    }
    private function parseObject(NBinary $binary ){
        $data = [
            'MaterialOffset' => $binary->consume(4, NBinary::INT_32),
            'NumMaterials' => $binary->consume(4, NBinary::INT_32),
            'BoneTransDataIndexOffset' => $binary->consume(4, NBinary::INT_32),
            'unknown' => $binary->consume(4, NBinary::HEX),
            'unknown2' => $binary->consume(4, NBinary::HEX),
            'Position' => $binary->consume(12, NBinary::HEX),
            'modelChunkFlag' => $binary->consume(4, NBinary::INT_32),
            'modelChunkSize' => $binary->consume(4, NBinary::INT_32),
            'zero' => $binary->consume(4, NBinary::INT_32),
            'numMaterialIDs' => $binary->consume(4, NBinary::INT_32),
            'numFaceIndex' => $binary->consume(4, NBinary::INT_32),
            'boundingSphereXYZ' => $binary->consume(12, NBinary::HEX),
            'boundingSphereRadius' => $binary->consume(4, NBinary::FLOAT_32),
            'boundingSphereScale' => $binary->consume(12, NBinary::HEX),
            'numVertex' => $binary->consume(4, NBinary::INT_32),
            'zero2' => $binary->consume(12, NBinary::HEX),
            'PerVertexElementSize' => $binary->consume(4, NBinary::INT_32),
            'unknown4' => $binary->consume(4 * 11, NBinary::HEX),
            'VertexElementType' => $binary->consume(4, NBinary::INT_32),
            'unknown5' => $binary->consume(4 * 8, NBinary::HEX),

            'faceindex' => [],
            'vertex' => []
        ];

        $data['mtlIds'] = $this->parseMaterialIDs($binary, $data['numMaterialIDs']);

        for($i = 0; $i < $data['numFaceIndex']; $i++){
            $data['faceindex'][] = $binary->consume(2, NBinary::INT_16);
        }

        if ($data['VertexElementType'] == 0x52) {
            for($i = 0; $i < $data['numVertex']; $i++) {
                $data['vertex'][] = [
                    'x' => $binary->consume(4, NBinary::FLOAT_32),
                    'y' => $binary->consume(4, NBinary::FLOAT_32),
                    'z' => $binary->consume(4, NBinary::FLOAT_32),
                    'normal' => $this->parseNormal($binary),
                    'Color_BGRA' => $binary->consume(4, NBinary::HEX),

                ];
            }

        }else if ($data['VertexElementType'] == 0x152){
            for($i = 0; $i < $data['numVertex']; $i++) {
                $data['vertex'][] = [
                    'x' => $binary->consume(4, NBinary::FLOAT_32),
                    'y' => $binary->consume(4, NBinary::FLOAT_32),
                    'z' => $binary->consume(4, NBinary::FLOAT_32),
                    'normal' => $this->parseNormal($binary),
                    'Color_BGRA' => $binary->consume(4, NBinary::HEX),
                    'tu' => $binary->consume(4, NBinary::FLOAT_32),
                    'tv' => $binary->consume(4, NBinary::FLOAT_32),
                ];
            }

        }else if ($data['VertexElementType'] == 0x252){
            for($i = 0; $i < $data['numVertex']; $i++) {
                $data['vertex'][] = [
                    'x' => $binary->consume(4, NBinary::FLOAT_32),
                    'y' => $binary->consume(4, NBinary::FLOAT_32),
                    'z' => $binary->consume(4, NBinary::FLOAT_32),
                    'normal' => $this->parseNormal($binary),
                    'Color_BGRA' => $binary->consume(4, NBinary::HEX),
                    'tu' => $binary->consume(4, NBinary::FLOAT_32),
                    'tv' => $binary->consume(4, NBinary::FLOAT_32),
                    'tu2' => $binary->consume(4, NBinary::FLOAT_32),
                    'tv2' => $binary->consume(4, NBinary::FLOAT_32),
                ];
            }

        }else if ($data['VertexElementType'] == 0x115E){
            for($i = 0; $i < $data['numVertex']; $i++){

                $data['vertex'][] = [
                    'x' => $binary->consume(4, NBinary::FLOAT_32),
                    'y' => $binary->consume(4, NBinary::FLOAT_32),
                    'z' => $binary->consume(4, NBinary::FLOAT_32),
                    'weight4' => $binary->consume(4, NBinary::FLOAT_32),
                    'weight3' => $binary->consume(4, NBinary::FLOAT_32),
                    'weight2' => $binary->consume(4, NBinary::FLOAT_32),
                    'weight1' => $binary->consume(4, NBinary::FLOAT_32),
                    'boneID4321' => $binary->consume(4, NBinary::HEX),
                    'normal' => $this->parseNormal($binary),
                    'Color_BGRA' => $binary->consume(4, NBinary::HEX),
                    'tu' => $binary->consume(4, NBinary::FLOAT_32),
                    'tv' => $binary->consume(4, NBinary::FLOAT_32),
                ];
            }

        }else if ($data['VertexElementType'] == 0x125E){

            for($i = 0; $i < $data['numVertex']; $i++){

                $data['vertex'][] = [
                    'x' => $binary->consume(4, NBinary::FLOAT_32),
                    'y' => $binary->consume(4, NBinary::FLOAT_32),
                    'z' => $binary->consume(4, NBinary::FLOAT_32),
                    'weight4' => $binary->consume(4, NBinary::FLOAT_32),
                    'weight3' => $binary->consume(4, NBinary::FLOAT_32),
                    'weight2' => $binary->consume(4, NBinary::FLOAT_32),
                    'weight1' => $binary->consume(4, NBinary::FLOAT_32),
                    'boneID4321' => $binary->consume(4, NBinary::HEX),
                    'normal' => $this->parseNormal($binary),
                    'Color_BGRA' => $binary->consume(4, NBinary::HEX),
                    'tu' => $binary->consume(4, NBinary::FLOAT_32),
                    'tv' => $binary->consume(4, NBinary::FLOAT_32),
                    'tu2' => $binary->consume(4, NBinary::FLOAT_32),
                    'tv2' => $binary->consume(4, NBinary::FLOAT_32),
                ];
            }
        }

        return $data;
    }


    private function parseNormal(NBinary $binary ){
        $data = [
            'x' => $binary->consume(2, NBinary::INT_16),
            'y' => $binary->consume(2, NBinary::INT_16),
            'z' => $binary->consume(2, NBinary::INT_16),
        ];

        $binary->current += 2;

        return $data;

    }

    private function parseMaterialIDs(NBinary $binary, $numMaterialIDs ){
        $materials = [];

        for($i = 0; $i < $numMaterialIDs; $i++){
            $materials[] = [
                'BoundingBoxMinX' => $binary->consume(4, NBinary::FLOAT_32),
                'BoundingBoxMinY' => $binary->consume(4, NBinary::FLOAT_32),
                'BoundingBoxMinZ' => $binary->consume(4, NBinary::FLOAT_32),
                'BoundingBoxMaxX' => $binary->consume(4, NBinary::FLOAT_32),
                'BoundingBoxMaxY' => $binary->consume(4, NBinary::FLOAT_32),
                'BoundingBoxMaxZ' => $binary->consume(4, NBinary::FLOAT_32),
                'MaterialIDNumFace' => $binary->consume(2, NBinary::INT_16),
                'MaterialID' => $binary->consume(2, NBinary::INT_16),
                'StartFaceID' => $binary->consume(2, NBinary::INT_16),
                'unknown' => $binary->consume(2, NBinary::INT_16),
                'zero' => $binary->consume(12, NBinary::HEX),
            ];
        }

        return $materials;

    }

    private function parseObjectInfo(NBinary $binary ){

        return [
            'nextObjectInfoOffset' => $binary->consume(4, NBinary::INT_32),
            'prevObjectInfoOffset' => $binary->consume(4, NBinary::INT_32),
            'objectParentBoneOffset' => $binary->consume(4, NBinary::INT_32),
            'objectOffset' => $binary->consume(4, NBinary::INT_32),
            'rootEntryOffset' => $binary->consume(4, NBinary::INT_32),
            'zero' => $binary->consume(4, NBinary::INT_32),
            'unknown' => $binary->consume(4, NBinary::INT_32),//always  0x3
            'unknown2' => $binary->consume(4, NBinary::INT_32)
        ];

    }

    private function parseBone(NBinary $binary ){
        $unknown = $binary->consume(4, NBinary::HEX);
        $nextBrotherBoneOffset = $binary->consume(4, NBinary::INT_32);
        $parentBoneOffset = $binary->consume(4, NBinary::INT_32);
        $rootBoneOffset = $binary->consume(4, NBinary::INT_32);
        $subBoneOffset = $binary->consume(4, NBinary::INT_32);
        $animationDataIndexOffset = $binary->consume(4, NBinary::INT_32);

        $boneName = $binary->consume(40, NBinary::HEX);

        $matrix4X4_ParentChild = $binary->consume(16 * 4, NBinary::HEX);
        $matrix4X4_WorldPos = $binary->consume(16 * 4, NBinary::HEX);

        $subBone = false;
        $nextBrotherBone = false;
        $animationDataIndex = false;
        if ($subBoneOffset != 0) {

            $binary->current = $subBoneOffset;

            $subBone = $this->parseBone($binary);
        }

        if ($nextBrotherBoneOffset != 0){

            $binary->current = $nextBrotherBoneOffset;
            $nextBrotherBone = $this->parseBone($binary);
        }

        if ($animationDataIndexOffset != 0){
            $binary->current = $animationDataIndexOffset;

            $animationDataIndex = $this->parseAnimationDataIndex($binary);


        }

        return [
            'unknown' => $unknown,
            'nextBrotherBoneOffset' => $nextBrotherBoneOffset,
            'parentBoneOffset' => $parentBoneOffset,
            'rootBoneOffset' => $rootBoneOffset,
            'subBoneOffset' => $subBoneOffset,
            'animationDataIndexOffset' => $animationDataIndexOffset,
            'boneName' => $boneName,
            'matrix4X4_ParentChild' => $matrix4X4_ParentChild,
            'matrix4X4_WorldPos' => $matrix4X4_WorldPos,
            'subBone' => $subBone,
            'nextBrotherBone' => $nextBrotherBone,
            'animationDataIndex' => $animationDataIndex
        ];

    }

    private function parseAnimationDataIndex(NBinary $binary ){
        $result = [
            'numBone' => $binary->consume(4, NBinary::INT_32),
            'unknown' => $binary->consume(4, NBinary::INT_32),
            'rootBoneOffset' => $binary->consume(4, NBinary::INT_32),
            'animationDataOffset' => $binary->consume(4, NBinary::INT_32),
            'boneTransformOffset' => $binary->consume(4, NBinary::INT_32),
            'zero' => $binary->consume(4, NBinary::INT_32),
            'animationData' => [],
            'boneTransform' => []
        ];


        if ($result['animationDataOffset'] != 0){
            for($i = 0; $i < $result['numBone']; $i++){
                $result['animationData'][] = $this->parseAnimationData($binary);
            }
        }

        if ($result['boneTransformOffset'] != 0){
            for($i = 0; $i < $result['numBone']; $i++){
                $result['boneTransform'][] = $binary->consume(4 * 8, NBinary::HEX);
            }
        }

        return $result;

    }

    private function parseAnimationData(NBinary $binary ){

        return [
            'animationBoneId' => $binary->consume(2, NBinary::INT_16),
            'boneType' => $binary->consume(2, NBinary::INT_16),
            'BoneOffset' => $binary->consume(4, NBinary::INT_32),
        ];

    }

    private function parseEntryIndex(NBinary $binary ){
        $nextEntryIndexOffset = $binary->consume(4, NBinary::INT_32);
        $prevEntryIndexOffset = $binary->consume(4, NBinary::INT_32);

        $entryOffset = $binary->consume(4, NBinary::INT_32);

        $zero = $binary->consume(4, NBinary::INT_32);

        return [
            'nextEntryIndexOffset' => $nextEntryIndexOffset,
            'prevEntryIndexOffset' => $prevEntryIndexOffset,
            'entryOffset' => $entryOffset,
            'zero' => $zero
        ];
    }

    private function parseEntry(NBinary $binary ){
        $rootBoneOffset = $binary->consume(4, NBinary::INT_32);

        $zero3 = $binary->consume(12, NBinary::HEX);

        $unknown = $binary->consume(4, NBinary::HEX);

        $objectInfoIndexOffset = $binary->current;

        $firstObjectInfoOffset = $binary->consume(4, NBinary::INT_32);
        $lastObjectInfoOffset = $binary->consume(4, NBinary::INT_32);

        $zero = $binary->consume(4, NBinary::INT_32);

        return [
            'objectInfoIndexOffset' => $objectInfoIndexOffset,
            'rootBoneOffset' => $rootBoneOffset,
            'zero3' => $zero3,
            'unknown' => $unknown,
            'firstObjectInfoOffset' => $firstObjectInfoOffset,
            'lastObjectInfoOffset' => $lastObjectInfoOffset,
            'zero' => $zero,
        ];

    }

    private function parseMdlHeader(NBinary $binary ){

        $fourCC = $binary->consume(4, NBinary::BINARY);

        $constNumber = $binary->consume(4, NBinary::INT_32);

        return [
            'fileSize' => $binary->consume(4, NBinary::INT_32),
            'offsetTable' => $binary->consume(4, NBinary::INT_32),
            'offsetTable2' => $binary->consume(4, NBinary::INT_32),
            'numTable' => $binary->consume(4, NBinary::INT_32),
            'zero1' => $binary->consume(4, NBinary::INT_32),
            'zero2' => $binary->consume(4, NBinary::INT_32),
            'firstEntryIndexOffset' => $binary->consume(4, NBinary::INT_32),
            'lastEntryIndexOffset' => $binary->consume(4, NBinary::INT_32),
            'unknown' => $binary->consume(8, NBinary::HEX)
        ];


    }


}