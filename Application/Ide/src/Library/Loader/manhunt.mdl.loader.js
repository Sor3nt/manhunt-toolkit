/**
 * MDL Reader based on the awesome work from Majest1c_R3 and Allen
 */
MANHUNT.fileLoader.MDL = function () {

    function Manhunt2Mdl(inputData){

        function parseBoneTransDataIndex(binary, boneTransDataIndexOffset ){

            binary.setCurrent(boneTransDataIndexOffset);

            var data = {
                'numBone': binary.consume(4, 'int32'),
                'BoneTransDataOffset': binary.consume(4, 'int32'),
                'matrix': []
            };

            binary.setCurrent(data.BoneTransDataOffset);

            for (var i = 0; i < data.numBone ; i++){
                data.matrix.push(
                    binary.consume(4 * 16, 'arraybuffer')
                )
            }

            return data;
        }

        function parseMaterial(binary, numMaterials ){
            var materials = [];

            for(var i = 0; i < numMaterials; i++){

                var material = {
                    'TexNameOffset': binary.consume(4, 'int32'),
                    // 'Loaded': binary.consume(1, 'uint8'),

                    'Color_RGBA': [
                        binary.consume(1, 'uint8'),
                        binary.consume(1, 'uint8'),
                        binary.consume(1, 'uint8'),
                        binary.consume(1, 'uint8'),
                    ],
                    'Color_RGBA2': [
                        binary.consume(1, 'uint8'),
                        binary.consume(1, 'uint8'),
                        binary.consume(1, 'uint8'),
                        binary.consume(1, 'uint8')
                    ]
                };

                var nextMaterialOffset = binary.current();

                binary.setCurrent(material.TexNameOffset);
                material.TexName = binary.getString(0, true);
                materials.push(material);

                binary.setCurrent(nextMaterialOffset);
            }

            return materials;
        }

        var normals = [];
        function parseObject(binary ){
            var data = {
                'MaterialOffset': binary.consume(4, 'int32'),
                'NumMaterials': binary.consume(4, 'int32'),
                'BoneTransDataIndexOffset': binary.consume(4, 'int32'),
                'unknown': binary.consume(4, 'arraybuffer'),
                'unknown2': binary.consume(4, 'arraybuffer'),
                'Position': (function (binary) {
                    return [
                        binary.consume(4, 'float32'),
                        binary.consume(4, 'float32'),
                        binary.consume(4, 'float32')
                    ];
                })(binary),
                'modelChunkFlag': binary.consume(4, 'int32'),
                'modelChunkSize': binary.consume(4, 'int32'),
                'zero': binary.consume(4, 'int32'),
                'numMaterialIDs': binary.consume(4, 'int32'),
                'numFaceIndex': binary.consume(4, 'int32'),
                'boundingSphereXYZ': (function (binary) {
                    return [
                        binary.consume(4, 'float32'),
                        binary.consume(4, 'float32'),
                        binary.consume(4, 'float32')
                    ];
                })(binary),
                'boundingSphereRadius': binary.consume(4, 'float32'),
                'boundingSphereScale': (function (binary) {
                    return [
                        binary.consume(4, 'float32'),
                        binary.consume(4, 'float32'),
                        binary.consume(4, 'float32')
                    ];
                })(binary),
                'numVertex': binary.consume(4, 'int32'),
                'zero2': binary.consume(12, 'arraybuffer'),
                'PerVertexElementSize': binary.consume(4, 'int32'),
                'unknown4': binary.consume(4 * 11, 'arraybuffer'),
                'VertexElementType': binary.consume(4, 'int32'),
                'unknown5': binary.consume(4 * 8, 'arraybuffer'),

                'faceindex': [],
                'vertex': [],
                'normals': []
            };

            data.mtlIds = parseMaterialIDs(binary, data.numMaterialIDs );

            for(i = 0; i < data.numFaceIndex ; i++){
                data.faceindex.push(
                    binary.consume(2, 'int16')
                )
            }

            data.VertexElementType2 = data.VertexElementType >> 8;
            data.skinDataFlag = (data.VertexElementType2 & 0x10) === 0x10;
            data.numUV = data.VertexElementType2 & 0xf;

            data.CPV_array = [];
            data.UV1_array = [];
            data.UV2_array = [];

            var vertex;
            if (data.VertexElementType === 0x52) {
                for(i = 0; i < data.numVertex ; i++) {
                    vertex = {
                        'x': binary.consume(4, 'float32'),
                        'y': binary.consume(4, 'float32'),
                        'z': binary.consume(4, 'float32'),
                        'normal': parseNormal(binary),
                        'Color_B': binary.consume(1, 'uint8') / 255.0,
                        'Color_G': binary.consume(1, 'uint8') / 255.0,
                        'Color_R': binary.consume(1, 'uint8') / 255.0,
                        'Color_A': binary.consume(1, 'uint8') / 255.0,

                        'maxWeight': 0

                    };

                    data.CPV_array.push(new THREE.Color( vertex.Color_R, vertex.Color_G, vertex.Color_B ));

                    data.vertex.push(vertex);
                    data.normals.push(new THREE.Vector3(vertex.normal.x, vertex.normal.y, vertex.normal.z));
                }

            }else if (data.VertexElementType === 0x152){
                for(i = 0; i < data.numVertex ; i++) {
                    vertex = {
                        'x': binary.consume(4, 'float32'),
                        'y': binary.consume(4, 'float32'),
                        'z': binary.consume(4, 'float32'),
                        'normal': parseNormal(binary),
                        'Color_B': binary.consume(1, 'uint8') / 255.0,
                        'Color_G': binary.consume(1, 'uint8') / 255.0,
                        'Color_R': binary.consume(1, 'uint8') / 255.0,
                        'Color_A': binary.consume(1, 'uint8') / 255.0,
                        'tu': binary.consume(4, 'float32'),
                        'tv': binary.consume(4, 'float32'),

                        'maxWeight': 0
                    };

                    data.CPV_array.push(new THREE.Color( vertex.Color_R, vertex.Color_G, vertex.Color_B ));
                    data.UV1_array.push([vertex.tu, vertex.tv, 0]);

                    data.vertex.push(vertex);
                    data.normals.push(new THREE.Vector3(vertex.normal.x, vertex.normal.y, vertex.normal.z));
                }

            }else if (data.VertexElementType === 0x252){
                for(i = 0; i < data.numVertex ; i++) {

                    vertex = {
                        'x': binary.consume(4, 'float32'),
                        'y': binary.consume(4, 'float32'),
                        'z': binary.consume(4, 'float32'),
                        'normal': parseNormal(binary),
                        'Color_B': binary.consume(1, 'uint8') / 255.0,
                        'Color_G': binary.consume(1, 'uint8') / 255.0,
                        'Color_R': binary.consume(1, 'uint8') / 255.0,
                        'Color_A': binary.consume(1, 'uint8') / 255.0,
                        'tu': binary.consume(4, 'float32'),
                        'tv': binary.consume(4, 'float32'),
                        'tu2': binary.consume(4, 'float32'),
                        'tv2': binary.consume(4, 'float32'),

                        'maxWeight': 0
                    };

                    data.CPV_array.push(new THREE.Color( vertex.Color_R, vertex.Color_G, vertex.Color_B ));
                    data.UV1_array.push([vertex.tu, vertex.tv, 0]);
                    data.UV2_array.push([vertex.tu2, vertex.tv2, 0]);
                    data.vertex.push(vertex);
                    data.normals.push(new THREE.Vector3(vertex.normal.x, vertex.normal.y, vertex.normal.z));
                }

            }else if (data.VertexElementType === 0x115E){
                for(i = 0; i < data.numVertex ; i++){

                    vertex = {
                        'x': binary.consume(4, 'float32'),
                        'y': binary.consume(4, 'float32'),
                        'z': binary.consume(4, 'float32'),
                        'weight4': binary.consume(4, 'float32'),
                        'weight3': binary.consume(4, 'float32'),
                        'weight2': binary.consume(4, 'float32'),
                        'weight1': binary.consume(4, 'float32'),
                        'boneID4': binary.consume(1, 'uint8'),
                        'boneID3': binary.consume(1, 'uint8'),
                        'boneID2': binary.consume(1, 'uint8'),
                        'boneID1': binary.consume(1, 'uint8'),
                        'normal': parseNormal(binary),
                        'Color_B': binary.consume(1, 'uint8') / 255.0,
                        'Color_G': binary.consume(1, 'uint8') / 255.0,
                        'Color_R': binary.consume(1, 'uint8') / 255.0,
                        'Color_A': binary.consume(1, 'uint8') / 255.0,
                        'tu': binary.consume(4, 'float32'),
                        'tv': binary.consume(4, 'float32')
                    };

                    vertex.maxWeight = vertex.weight1 +
                        vertex.weight2 +
                        vertex.weight3 +
                        vertex.weight4;

                    data.CPV_array.push(new THREE.Color( vertex.Color_R, vertex.Color_G, vertex.Color_B ));
                    data.UV1_array.push([vertex.tu, vertex.tv, 0]);

                    data.vertex.push(vertex);
                    data.normals.push(new THREE.Vector3(vertex.normal.x, vertex.normal.y, vertex.normal.z));
                }

            }else if (data.VertexElementType === 0x125E){

                for(i = 0; i < data.numVertex ; i++){
                    vertex = {
                        'x': binary.consume(4, 'float32'),
                        'y': binary.consume(4, 'float32'),
                        'z': binary.consume(4, 'float32'),
                        'weight4': binary.consume(4, 'float32'),
                        'weight3': binary.consume(4, 'float32'),
                        'weight2': binary.consume(4, 'float32'),
                        'weight1': binary.consume(4, 'float32'),
                        'boneID4': binary.consume(1, 'uint8'),
                        'boneID3': binary.consume(1, 'uint8'),
                        'boneID2': binary.consume(1, 'uint8'),
                        'boneID1': binary.consume(1, 'uint8'),
                        'normal': parseNormal(binary),
                        'Color_B': binary.consume(1, 'uint8') / 255.0,
                        'Color_G': binary.consume(1, 'uint8') / 255.0,
                        'Color_R': binary.consume(1, 'uint8') / 255.0,
                        'Color_A': binary.consume(1, 'uint8') / 255.0,
                        'tu': binary.consume(4, 'float32'),
                        'tv': binary.consume(4, 'float32'),
                        'tu2': binary.consume(4, 'float32'),
                        'tv2': binary.consume(4, 'float32'),
                    };


                    vertex.maxWeight = vertex.weight1 +
                        vertex.weight2 +
                        vertex.weight3 +
                        vertex.weight4;

                    data.CPV_array.push(new THREE.Color( vertex.Color_R, vertex.Color_G, vertex.Color_B ));
                    data.UV1_array.push([vertex.tu, vertex.tv, 0]);
                    data.UV2_array.push([vertex.tu2, vertex.tv2, 0]);
                    data.vertex.push(vertex);

                    data.normals.push(new THREE.Vector3(vertex.normal.x, vertex.normal.y, vertex.normal.z));
                }
            }

            return data;
        }


        function parseNormal(binary ){
            return {
                'x': binary.consume(2, 'int16') / 32768.0,
                'y': (binary.consume(2, 'int16') / 32768.0) ,
                'z': binary.consume(2, 'int16') / 32768.0,
                'pad': binary.consume(2, 'int16'),
            };

        }

        function parseMaterialIDs(binary, numMaterialIDs ){
            var materials = [];

            for(i = 0; i < numMaterialIDs; i++){
                materials.push({
                    'BoundingBoxMinX': binary.consume(4, 'float32'),
                    'BoundingBoxMinY': binary.consume(4, 'float32'),
                    'BoundingBoxMinZ': binary.consume(4, 'float32'),
                    'BoundingBoxMaxX': binary.consume(4, 'float32'),
                    'BoundingBoxMaxY': binary.consume(4, 'float32'),
                    'BoundingBoxMaxZ': binary.consume(4, 'float32'),
                    'MaterialIDNumFace': binary.consume(2, 'int16'),
                    'MaterialID': binary.consume(2, 'int16'),
                    'StartFaceID': binary.consume(2, 'int16'),
                    'unknown': binary.consume(2, 'int16'),
                    'zero': binary.consume(12, 'arraybuffer'),
                });
            }

            return materials;

        }


        function parseObjectInfo(bone, binary ){

            return {
                'nextObjectInfoOffset': binary.consume(4, 'int32'),
                'prevObjectInfoOffset': binary.consume(4, 'int32'),
                'objectParentBoneOffset': binary.consume(4, 'int32'),
                'objectOffset': binary.consume(4, 'int32'),
                'rootEntryOffset': binary.consume(4, 'int32'),
                'zero': binary.consume(4, 'int32'),
                'unknown': binary.consume(4, 'int32'),//always  0x3
                'unknown2': binary.consume(4, 'int32')
            };

        }

        function parseBone(binary ){

            var myBoneOffset = binary.current();
            var unknown = binary.consume(4, 'arraybuffer');

            var nextBrotherBoneOffset = binary.consume(4, 'int32');

            var parentBoneOffset = binary.consume(4, 'int32');
            var rootBoneOffset = binary.consume(4, 'int32');
            var subBoneOffset = binary.consume(4, 'int32');

            var animationDataIndexOffset = binary.consume(4, 'int32');

            var boneName = binary.consume(40, 'arraybuffer');
            boneName = new NBinary(boneName);
            boneName = boneName.getString(0);

            var matrix4X4_ParentChild = [];// binary.consume(16 * 4, 'arraybuffer');
            var matrix4X4_WorldPos = [];// binary.consume(16 * 4, 'arraybuffer');

            for(var i = 0; i < 16;i++){
                matrix4X4_ParentChild.push(binary.consume(4, 'float32'));
            }
            for(i = 0; i < 16;i++){
                matrix4X4_WorldPos.push(binary.consume(4, 'float32'));
            }

            var subBone = false;
            var nextBrotherBone = false;
            var animationDataIndex = false;


            if (subBoneOffset !== 0) {

                binary.setCurrent(subBoneOffset);

                subBone = parseBone(binary);
            }

            if (nextBrotherBoneOffset !== 0){
                binary.setCurrent(nextBrotherBoneOffset);
                nextBrotherBone = parseBone(binary);
            }

            if (animationDataIndexOffset !== 0){
                binary.setCurrent(animationDataIndexOffset);

                animationDataIndex = parseAnimationDataIndex(binary);
            }

            return {
                'myBoneOffset': myBoneOffset,
                'unknown': unknown,
                'nextBrotherBoneOffset': nextBrotherBoneOffset,
                'parentBoneOffset': parentBoneOffset,
                'rootBoneOffset': rootBoneOffset,
                'subBoneOffset': subBoneOffset,
                'animationDataIndexOffset': animationDataIndexOffset,
                'boneName': boneName,
                'matrix4X4_ParentChild': matrix4X4_ParentChild,
                'matrix4X4_WorldPos': matrix4X4_WorldPos,
                'transform': [
                    [ matrix4X4_WorldPos[0],matrix4X4_WorldPos[1],matrix4X4_WorldPos[2], ],
                    [ matrix4X4_WorldPos[4],matrix4X4_WorldPos[5],matrix4X4_WorldPos[6], ],
                    [ matrix4X4_WorldPos[8],matrix4X4_WorldPos[9],matrix4X4_WorldPos[10], ],
                    [ matrix4X4_WorldPos[12],matrix4X4_WorldPos[13],matrix4X4_WorldPos[14], ],
                ],
                'subBone': subBone,
                'nextBrotherBone': nextBrotherBone,
                'animationDataIndex': animationDataIndex
            };

        }

        function parseAnimationDataIndex(binary ){
            var i;
            var result = {
                'numBone': binary.consume(4, 'int32'),
                'unknown': binary.consume(4, 'int32'),
                'rootBoneOffset': binary.consume(4, 'int32'),
                'animationDataOffset': binary.consume(4, 'int32'),
                'boneTransformOffset': binary.consume(4, 'int32'),
                'zero': binary.consume(4, 'int32'),
                'animationData': [],
                'boneTransform': []
            };


            if (result.animationDataOffset !== 0){
                binary.setCurrent(result.animationDataOffset);

                for(i = 0; i < result.numBone ; i++){
                    result.animationData.push(
                        parseAnimationData(binary)
                    );
                }
            }

            if (result.boneTransformOffset !== 0){
                binary.setCurrent(result.boneTransformOffset);

                for(i = 0; i < result.numBone ; i++){
                    result.boneTransform.push(
                        binary.consume(4 * 8, 'arraybuffer')
                    );
                }
            }

            return result;

        }

        function parseAnimationData(binary ){

            return {
                'animationBoneId': binary.consume(2, 'int16'),
                'boneType': binary.consume(2, 'int16'),
                'BoneOffset': binary.consume(4, 'int32'),
            };

        }

        function parseEntryIndex(binary ){
            var nextEntryIndexOffset = binary.consume(4, 'int32');
            var prevEntryIndexOffset = binary.consume(4, 'int32');

            var entryOffset = binary.consume(4, 'int32');

            var zero = binary.consume(4, 'int32');

            return {
                'nextEntryIndexOffset': nextEntryIndexOffset,
                'prevEntryIndexOffset': prevEntryIndexOffset,
                'entryOffset': entryOffset,
                'zero': zero
            };
        }

        function parseEntry(binary ){
            var rootBoneOffset = binary.consume(4, 'int32');

            var zero3 = binary.consume(12, 'arraybuffer');

            var unknown = binary.consume(4, 'arraybuffer');

            var objectInfoIndexOffset = binary.current();

            var firstObjectInfoOffset = binary.consume(4, 'int32');
            var lastObjectInfoOffset = binary.consume(4, 'int32');

            var zero = binary.consume(4, 'int32');

            return {
                'objectInfoIndexOffset': objectInfoIndexOffset,
                'rootBoneOffset': rootBoneOffset,
                'zero3': zero3,
                'unknown': unknown,
                'firstObjectInfoOffset': firstObjectInfoOffset,
                'lastObjectInfoOffset': lastObjectInfoOffset,
                'zero': zero
            };

        }

        function parseMdlHeader( binary ){

            var fourCC = binary.consume(4, 'int32');
            var constNumber = binary.consume(4, 'int32');

            return {
                'fileSize' : binary.consume(4, 'int32'),
                'offsetTable' : binary.consume(4, 'int32'),
                'offsetTable2' : binary.consume(4, 'int32'),
                'numTable' : binary.consume(4, 'int32'),
                'zero1' : binary.consume(4, 'int32'),
                'zero2' : binary.consume(4, 'int32'),
                'firstEntryIndexOffset' : binary.consume(4, 'int32'),
                'lastEntryIndexOffset' : binary.consume(4, 'int32'),
                'unknown' : binary.consume(8, 'arraybuffer')
            };
        }


        function get( binary ){

            var mdlHeader = parseMdlHeader(binary);
            binary.setCurrent(mdlHeader.firstEntryIndexOffset);

            var results = [];
            do{

                var result = {};
                var entryIndex = parseEntryIndex(binary);

                binary.setCurrent(entryIndex.entryOffset);
                var entry = parseEntry(binary);

                binary.setCurrent(entry.rootBoneOffset);
                var bone = parseBone(binary);

                result.entryIndex = entryIndex;
                result.entry = entry;
                result.bone = bone;
                result.objects = [];

                if (entry.firstObjectInfoOffset !== entry.objectInfoIndexOffset ){

                    binary.setCurrent(entry.firstObjectInfoOffset);

                    do{
                        var objectRow = {
                            'materials': false,
                            'boneTransDataIndex': false
                        };

                        var objectInfo = parseObjectInfo(bone, binary);

                        binary.setCurrent(objectInfo.objectOffset);
                        var object = parseObject(binary);
                        if (typeof result.skinDataFlag === "undefined")
                            result.skinDataFlag = object.skinDataFlag;

                        objectRow.objectInfo = objectInfo;
                        objectRow.object = object;

                        if (object.MaterialOffset !== 0){
                            binary.setCurrent(object.MaterialOffset);

                            objectRow.materials = parseMaterial(binary, object.NumMaterials );
                        }

                        if (object.BoneTransDataIndexOffset !== 0){


                            binary.setCurrent(object.BoneTransDataIndexOffset);

                            boneTransDataIndex = parseBoneTransDataIndex(binary, object.BoneTransDataIndexOffset );
                            objectRow.boneTransDataIndex = boneTransDataIndex;
                        }

                        binary.setCurrent(objectInfo.nextObjectInfoOffset);

                        result.objects.push(objectRow);

                    }while(objectInfo.nextObjectInfoOffset !== entry.objectInfoIndexOffset );
                }

                if (entryIndex.nextEntryIndexOffset !== 0x20){
                    binary.setCurrent(entryIndex.nextEntryIndexOffset);
                }

                results.push(result);
            }while(entryIndex.nextEntryIndexOffset !== 0x20);

            return results;
        }


        return get(inputData);

    }

    function ManhuntDff(binary){

        var modelName;

        function cClump(){
            return {
                id: binary.consume(4, 'int32'),
                size: binary.consume(4, 'uint32'),
                version: binary.consume(4, 'uint32')
            };
        }

        function clump(){


        }

        function rHAnimPLG() {

            var boneDataAry = [];
            if(binary.consume(4, 'int32') !== 256){
                return console.log('[ManhuntDff] rHAnimPLG, assume 256.');
            }

            var frameBoneId = binary.consume(4, 'int32');
            var boneCount = binary.consume(4, 'uint32');

            if (frameBoneId === -1){
                // console.log('[ManhuntDff] rHAnimPLG, returning empty array. (no BoneId)');
                return false;
            }

            if (boneCount === 0){
                // console.log('[ManhuntDff] rHAnimPLG, returning empty array. (no bones)');
                return [frameBoneId];
            }

            binary.seek(8);

            for(var i = 0; i < boneCount; i++){
                boneDataAry.push({
                    boneId: binary.consume(4, 'uint32'),
                    boneIndex: binary.consume(4, 'uint32'),
                    boneType: binary.consume(4, 'uint32'),
                });
            }

            return [frameBoneId, boneDataAry];

        }

        function rFrameList() {
            var clump = cClump();
            if (clump.id !== 14){
                return console.log('[ManhuntDff] frame list data, assume 14.');
            }

            clump = cClump();
            if (clump.id !== 1){
                return console.log('[ManhuntDff] frame list data, assume 1.');
            }

            var frameCount = binary.consume(4, 'int32');

            var frameAry = [];
            var i;

            // var matrix = [];
            for(i = 0; i < frameCount; i++){
                frameAry.push({
                    // matrix: binary.consume(4 * 12, 'arraybuffer'),
                    matrix: [
                        binary.consume(4, 'float32'), binary.consume(4, 'float32'), binary.consume(4, 'float32'), 0,
                        binary.consume(4, 'float32'), binary.consume(4, 'float32'), binary.consume(4, 'float32'), 0,
                        binary.consume(4, 'float32'), binary.consume(4, 'float32'), binary.consume(4, 'float32'), 0,
                        binary.consume(4, 'float32'), binary.consume(4, 'float32'), binary.consume(4, 'float32'), 1
                    ],
                    parentId: binary.consume(4, 'int32') + 1,
                    unk: binary.consume(4, 'uint32')
                });
            }

            var boneInfos;
            for(i = 0; i < frameCount; i++){
                clump = cClump();
                if (clump.id !== 3){
                    return console.log('[ManhuntDff] frame list ext data, assume 3. got',clump.id);
                }

                if (clump.size !== 0){
                    var loopEnd = binary.current() + clump.size;
                    while(binary.current() < loopEnd){

                        clump = cClump();
                        switch(clump.id){

                            case 39056126:
                                frameAry[i].name = binary.consume(clump.size, 'nbinary').getString(0);
                                break;

                            case 286:
                                var res = rHAnimPLG();
                                if (res !== false){
                                    frameAry[i].boneId = res[0];
                                    if (res.length === 2){
                                        boneInfos = res[1];
                                    }
                                }


                                // clump = cClump();
                                // frameAry[i].name = binary.consume(clump.size, 'nbinary').getString(0);
                                // console.log("TEST", frameAry[i].name);
                                break;

                            default:
                                console.log('[ManhuntDff] frame list ext data, skip, unknown section. len', stringLength);
                                binary.seek(stringLength);
                                break;
                        }

                    }

                }else{
                    if (clump.version === 0x1803FFFF){
                        frameAry[i].name = "Skin_Mesh";
                    }

                }

            }

            frameAry.forEach(function (frameEntry, frameIndex) {
                if (typeof frameEntry.boneId === "undefined") return;

                boneInfos.forEach(function (info, index) {

                    if (info.boneId !== frameEntry.boneId) return;
                    // console.log(frameIndex, info.boneId , frameEntry.boneId);
                    // frameAry[frameIndex].boneId = info.boneId;
                    frameAry[frameIndex].boneIndex = info.boneIndex;
                    frameAry[frameIndex].boneType = info.boneType;

                });
            });

            return frameAry;

        }

        function getGeometryCount() {
            var clump = cClump();
            if (clump.id !== 26){
                return console.log('[ManhuntDff] geometry count, assume 26.');
            }

            clump = cClump();
            if (clump.id !== 1){
                return console.log('[ManhuntDff] geometry count, assume 1.');
            }

            return binary.consume(4, 'int32');
        }

        function rMaterialList() {

            var clump = cClump();
            if (clump.id !== 8){
                return console.log('[ManhuntDff] material, assume 8.');
            }

            clump = cClump();
            if (clump.id !== 1){
                return console.log('[ManhuntDff] material, assume 1.');
            }

            var materialCount = binary.consume(4, 'int32');

            for(var i = 0; i < materialCount; i++){
                binary.consume(4, 'int32');
            }

            var list = [];
            for(i = 0; i < materialCount; i++){
                list.push(rMaterial());
            }

            return list;

        }

        function rMaterial() {
            var result = {};
            var clump = cClump();
            if (clump.id !== 7){
                return console.log('[ManhuntDff] material, assume 7.');
            }

            clump = cClump();
            if (clump.id !== 1){
                return console.log('[ManhuntDff] material, assume 1.');
            }

            var unk = binary.consume(4, 'int32');

            result.color = binary.readColorRGBA();
            unk = binary.consume(4, 'int32');

            var textureCount = binary.consume(4, 'int32');
            result.light = {
                ambient: binary.consume(4, 'float32'),
                diffuse: binary.consume(4, 'float32'),
                specular: binary.consume(4, 'float32')
            };

            result.textures = [];
            for(var i = 0; i < textureCount; i++){
                result.textures.push(getTextureName());
            }

            clump = cClump();
            if (clump.id !== 3){
                return console.log('[ManhuntDff] material data, assume 3.');
            }

            binary.seek(clump.size);

            return result;

        }

        function getTextureName() {
            var clump = cClump();
            if (clump.id !== 6){
                return console.log('[ManhuntDff] getTextureName data, assume 6.');
            }

            clump = cClump();
            if (clump.id !== 1){
                return console.log('[ManhuntDff] getTextureName data, assume 1.');
            }

            var TexFlag = binary.consume(4, 'int32');

            clump = cClump();
            if (clump.id !== 2){
                return console.log('[ManhuntDff] getTextureName data, assume 2.');
            }

            var texName = binary.consume(clump.size, 'nbinary').getString(0);
            clump = cClump();
            if (clump.id !== 2){
                return console.log('[ManhuntDff] getTextureName data, assume 2.');
            }

            var maskName = binary.consume(clump.size, 'string');

            clump = cClump();
            if (clump.id !== 3){
                return console.log('[ManhuntDff] getTextureName data, assume 3.');
            }
            binary.seek(clump.size);

            return texName;
        }

        function rGeometry() {
            
            var result = {
                light: false
            };
            
            var clump = cClump();
            if (clump.id !== 15){
                return console.log('[ManhuntDff] geometry data, assume 15.');
            }

            clump = cClump();
            if (clump.id !== 1){
                return console.log('[ManhuntDff] geometry data, assume 1.');
            }

            var GeometryFlags = binary.consume(1, 'uint8');
            var unk = binary.consume(1, 'int8');
            var t2count = binary.consume(2, 'int16');

            var faceCount = binary.consume(4, 'uint32');
            var vertCount = binary.consume(4, 'uint32');
            var mtCount = binary.consume(4, 'uint32');

            if (clump.version === 0x1003FFFF || clump.version === 0x1803FFFF) {
            }else{
                result.light = {
                    ambient: binary.consume(4, 'float32'),
                    diffuse: binary.consume(4, 'float32'),
                    specular: binary.consume(4, 'float32')
                };
            }

            result.cpvArray = [];
            if (GeometryFlags % 16 >= 8){
                for (var i = 0; i < vertCount; i++){
                    result.cpvArray.push(binary.readColorRGBA());
                }
            }

            result.uvArray = [];
            if (t2count > 0 || GeometryFlags % 8 >= 4){
                for(i = 0; i < vertCount; i++){
                    result.uvArray.push([
                        binary.consume(4, 'float32'),
                        binary.consume(4, 'float32')
                    ]);
                }
            }

            result.uv2Array = [];
            if (t2count > 1){
                for(i = 0; i < vertCount; i++){
                    result.uv2Array.push([
                        binary.consume(4, 'float32'),
                        binary.consume(4, 'float32')
                    ]);
                }
            }

            //more UV maps....
            if (t2count > 2){
                for(i = 2; i < t2count; i++){
                    for(i = 0; i < vertCount; i++){
                        binary.consume(4, 'float32');
                        binary.consume(4, 'float32');
                    }
                }
            }


            result.faces = [];
            for(i = 0; i < faceCount; i++){
                var a2 = binary.consume(2, 'uint16');
                var a1 = binary.consume(2, 'uint16');
                var fg = binary.consume(2, 'uint16');
                var a3 = binary.consume(2, 'uint16');

                result.faces.push([a1,a2,a3])
            }

            result.bbox = {
                bounding: binary.readVector3(),
                radius: binary.consume(4, 'float32'),
                unk: [binary.consume(4, 'float32'),binary.consume(4, 'float32')]
            };

            result.vertices = [];
            for(i = 0; i < vertCount; i++){
                var vec3 = binary.readVector3();
                result.vertices.push(vec3);
            }

            result.normals = [];
            if (GeometryFlags % 32 >= 16){
                for (i = 0; i < vertCount; i++){
                    result.normals.push(binary.readVector3());
                }
            }

            result.material = rMaterialList();
            
            if (binary.consume(4, 'int32') !== 3){
                return console.log('[ManhuntDff] material data, assume 3.');
            }

            var mExt = binary.consume(4, 'int32');
            ver = binary.consume(4, 'int32');
            binary.seek(mExt);

            return result;
        }


        var results = [];
        do{
            modelName = "???";
            var result = {};
            var cur = binary.current();
            var objectClump = cClump();

            binary.seek(4);
            var dataLength = binary.consume(4, 'int32') / 4;
            binary.seek(4);

            var objectCount = binary.consume(4, 'int32');

            if (dataLength > 1){
                binary.seek(4 * (dataLength - 1));
            }

            result.bones = rFrameList();

            if (result.bones[0].name === "Skin_Mesh"){
                modelName = result.bones[1].name;
            }else{
                modelName = result.bones[0].name;
            }

            result.name = modelName;


            var NumGeo = getGeometryCount();

            var MshAry = [];
            for(var i = 0; i < NumGeo; i++){
                MshAry.push(rGeometry());
            }

            result.geometry = MshAry;

            binary.setCurrent(cur + objectClump.size + 12);


            results.push(result);
        }while(binary.remain() > 0);

        return results;
    }

    function DffModelConverter(level, model) {

        var self = {
            _rootBone : {},
            _meshBone : {},
            _allBones : [],
            _boneInfos : [],


            _mesh: new THREE.Group(),

            _generateBoneStructure: function(){

// console.log("raw", model.bones);
                var bones = [];
                model.bones.forEach(function (bone, index) {
                    var realBone = self._createBone(bone);
                    bones.push(realBone);
                });



                model.bones.forEach(function (bone, index) {
                    // if (bone.parentId === 0) return;

                    model.bones.forEach(function (boneInner, indexInner) {
                        if (indexInner === 0) return;


                        if (index === boneInner.parentId - 1){

                            // console.log("add", index, boneInner.parentId - 1);
                            bones[index].add(bones[indexInner]);
                        }
                    });
                });
// console.log("adddd", bones[0], bones[1]);
                bones[0].children.push(bones[1]);

            },

            _createBone: function ( data ){
                var mat4 = new THREE.Matrix4();

                mat4.fromArray(data.matrix);

                var bone = new THREE.Bone();

                // if (self._allBones.length !== 0){
                    bone.applyMatrix4(mat4);

                // }

                bone.name = data.name;
                self._allBones.push(bone);

                return bone;
            },
            _init: function () {

                //
                // model.bones.forEach(function (bone) {
                //     if (bone.boneInfos.length > 0){
                //         self._rootBone = bone;
                //         self._boneInfos = bone.boneInfos;
                //     }
                // });

                self._generateBoneStructure();
                var skeleton = new THREE.Skeleton( self._allBones );

                model.geometry.forEach(function (entry) {
                    var material = [];

                    var geometry = new THREE.Geometry();
                    geometry.colorsNeedUpdate = true;
                    geometry.normalsNeedUpdate = true;

                    geometry.faceVertexUvs = [[]];

                    entry.vertices.forEach(function (vertexVec3, index) {

                        //Generate Vertex
                        // vertexVec3.applyMatrix4(self._meshBone.matrix);
                        if (self._allBones.length > 10){

                            // var mat4 = new THREE.Matrix4();
                            // mat4.fromArray(self._allBones[2].matrix);

                            vertexVec3.applyMatrix4(self._allBones[0].matrix);
                            // vertexVec3.applyMatrix4(mat4);
                        }

                        geometry.vertices.push(vertexVec3);
                    });

                    entry.material.forEach(function (materialObj) {
                        if (materialObj.textures.length === 0) return;

                        var texture = level._storage.tex.find(materialObj.textures[0]);
                        var mat = new THREE.MeshStandardMaterial();
                        mat.name = materialObj.TexName;
                        mat.map = texture;
                        mat.skinning = true;
                        mat.vertexColors = THREE.VertexColors;
                        mat.needsUpdate = true;
                        mat.transparent = texture.format === THREE.RGBA_S3TC_DXT5_Format;

                        material.push(
                            mat
                        );
                    });

                    for(var x = 0; x < entry.faces.length; x++) {
                        var face = new THREE.Face3(entry.faces[x][0], entry.faces[x][1], entry.faces[x][2]);

                        // face.materialIndex = materialForFace[x].MaterialID;


                        face.vertexNormals =[
                            entry.normals[face.a],
                            entry.normals[face.b],
                            entry.normals[face.c]
                        ];


                        if(entry.uvArray.length > 0){
                            geometry.faceVertexUvs[0].push([
                                new THREE.Vector2(
                                    entry.uvArray[face.a][0],
                                    entry.uvArray[face.a][1]
                                ),
                                new THREE.Vector2(
                                    entry.uvArray[face.b][0],
                                    entry.uvArray[face.b][1]
                                ),
                                new THREE.Vector2(
                                    entry.uvArray[face.c][0],
                                    entry.uvArray[face.c][1]
                                ),
                            ]);
                            geometry.uvsNeedUpdate = true;
                        }

                        geometry.faces.push(face);
                        // x += 2;
                    }


                    var bufferGeometry = new THREE.BufferGeometry();
                    bufferGeometry.fromGeometry( geometry );

                    bufferGeometry.colorsNeedUpdate = true;
                    // bufferGeometry.computeBoundingSphere();

                    var mesh;
                    // if (entryIndex === 0) {
                    // if (entry.object.skinDataFlag === true) {
                    //     mesh = new THREE.Mesh(bufferGeometry, material);
                        mesh = new THREE.SkinnedMesh(bufferGeometry, material);
                        // mesh.scale.set(MANHUNT.scale,MANHUNT.scale,MANHUNT.scale);
                    // }else{
                    //     mesh = new THREE.Mesh(bufferGeometry, material);
                    // }

                    mesh.add(self._allBones[1]);
                    mesh.bind(skeleton);
console.log(mesh);
                    self._mesh.add(mesh);

                });

            }
        };

        self._init();


        return {
            mesh: self._mesh
        };
    }

    function MdlModelConverter(level, model ){

        var self = {

            _rootBone : {},
            _meshBone : {},
            _allBones : [],
            _skinDataFlag: false,
            _mesh: new THREE.Group(),

            _init: function(){

                self._skinDataFlag = false;
                model.objects.forEach(function (entry) {
                    if (entry.object.skinDataFlag === true) {
                        self._skinDataFlag = true;
                    }
                });

                self._generateBoneStructure(model.bone, model.objects[0].objectInfo.objectParentBoneOffset);

                var entryIndex = 0;
                var skeleton = new THREE.Skeleton( self._allBones );

                model.objects.forEach(function (entry) {

                    var i;
                    var materialForFace = [];
                    var material = [];

                    var geometry = new THREE.Geometry();
                    geometry.colorsNeedUpdate = true;
                    geometry.normalsNeedUpdate = true;

                    geometry.faceVertexUvs = [[]];

                    //Material per face
                    entry.object.mtlIds.forEach(function (mtl, index) {
                        for(i = mtl.StartFaceID; i <= mtl.StartFaceID + mtl.MaterialIDNumFace; i++){
                            materialForFace[i] = mtl;
                        }
                    });

                    entry.materials.forEach(function (materialObj) {
                        var texture = level._storage.tex.find(materialObj.TexName);
                        var mat = new THREE.MeshStandardMaterial();
                        mat.name = materialObj.TexName;
                        mat.map = texture;
                        mat.skinning = true;
                        mat.vertexColors = THREE.VertexColors;
                        mat.needsUpdate = true;
                        mat.transparent = texture.format === THREE.RGBA_S3TC_DXT5_Format;

                        material.push(
                            mat
                        );
                    });

                    entry.object.vertex.forEach(function (vertex, index) {

                        //Generate Vertex
                        var vertexVec3 = new THREE.Vector3( vertex.x, vertex.y, vertex.z );
                        vertexVec3.applyMatrix4(self._meshBone.matrix);
                        geometry.vertices.push( vertexVec3 );

                        //Generate Skin Indices and Weights
                        var skinIndices = new THREE.Vector4();
                        var skinWeights = new THREE.Vector4();

                        if (vertex.maxWeight !== 0){
                            skinIndices = new THREE.Vector4(
                                vertex.boneID1,
                                vertex.boneID2,
                                vertex.boneID3,
                                vertex.boneID4
                            );

                            skinWeights = new THREE.Vector4(
                                vertex.weight1,
                                vertex.weight2,
                                vertex.weight3,
                                vertex.weight4,
                            );
                        }

                        geometry.skinIndices.push(skinIndices);
                        geometry.skinWeights.push(skinWeights);
                    });

                    //Generate Faces, normals and vertex colors
                    var faceIndex = entry.object.faceindex;
                    for(var x = 0; x < faceIndex.length; x++){
                        var face = new THREE.Face3( faceIndex[x],  faceIndex[x + 1], faceIndex[x + 2] );
                        face.materialIndex = materialForFace[x].MaterialID;

                        face.vertexNormals =[
                            entry.object.normals[face.a],
                            entry.object.normals[face.b],
                            entry.object.normals[face.c]
                        ];

                        if (entry.object.skinDataFlag === true) {
                            //TODO: HACK, the models are very very dark, dont know..
                            // face.vertexColors = [
                            //     entry.object.CPV_array[face.a],
                            //     entry.object.CPV_array[face.b],
                            //     entry.object.CPV_array[face.c]
                            // ];
                        }

                        if(entry.object.UV1_array.length > 0){
                            geometry.faceVertexUvs[0].push([
                                new THREE.Vector2(
                                    entry.object.UV1_array[face.a][0],
                                    entry.object.UV1_array[face.a][1]
                                ),
                                new THREE.Vector2(
                                    entry.object.UV1_array[face.b][0],
                                    entry.object.UV1_array[face.b][1]
                                ),
                                new THREE.Vector2(
                                    entry.object.UV1_array[face.c][0],
                                    entry.object.UV1_array[face.c][1]
                                ),
                            ]);
                            geometry.uvsNeedUpdate = true;
                        }

                        geometry.faces.push(face);
                        x += 2;
                    }

                    var bufferGeometry = new THREE.BufferGeometry();
                    bufferGeometry.fromGeometry( geometry );

                    bufferGeometry.colorsNeedUpdate = true;
                    bufferGeometry.computeBoundingSphere();

                    var mesh;
                    // if (entryIndex === 0) {
                    if (entry.object.skinDataFlag === true) {
                        mesh = new THREE.SkinnedMesh(bufferGeometry, material);
                        // mesh.scale.set(MANHUNT.scale,MANHUNT.scale,MANHUNT.scale);
                    }else{
                        mesh = new THREE.Mesh(bufferGeometry, material);
                    }

                    mesh.visible = entryIndex === 0;
                    self._mesh.add(mesh);

                    if (entryIndex === 0 && entry.object.skinDataFlag === true) {
                        mesh.add(self._allBones[0]);
                        mesh.bind(skeleton);
                    }
                    entryIndex++;
                });

                self._mesh.userData.LODIndex = 0;
            },

            _generateBoneStructure(boneData, objectParentBoneOffset){

                var bones = [];
                var tBone = self._createBone(boneData);

                if (boneData.myBoneOffset === objectParentBoneOffset){
                    self._meshBone = tBone;
                    tBone.userData.meshBone = true;
                }


                if (boneData.myBoneOffset === boneData.rootBoneOffset){
                    self._rootBone = tBone;
                    tBone.userData.rootBone = true;
                }

                bones.push(tBone);

                if (boneData.subBone !== false) {
                    var subBones = self._generateBoneStructure(boneData.subBone, objectParentBoneOffset);
                    subBones.forEach(function (subBone) {
                        tBone.add(subBone);
                    });
                }

                if (boneData.nextBrotherBone !== false) {
                    var nextBones = self._generateBoneStructure(boneData.nextBrotherBone, objectParentBoneOffset);
                    nextBones.forEach(function (subBone) {
                        bones.push(subBone);
                    });
                }

                return bones;
            },

            _createBone: function ( data ){
                var mat4 = new THREE.Matrix4();
                mat4.fromArray(data.matrix4X4_ParentChild);

                var bone = new THREE.Bone();

                if (self._allBones.length !== 0){
                    bone.applyMatrix4(mat4);

                }

                bone.name = data.boneName;
                self._allBones.push(bone);

                return bone;
            },


        };
        self._init();

        return {
            mesh: self._mesh
        };

    }

    return {
        load: function (level, file, callback ) {

            var results;

            MANHUNT.api.load(
                level._game,
                file,
                function ( data ) {

                    var binary = new NBinary(data);
                    var gameId = binary.consume(4, 'uint32');
                    binary.setCurrent(0);

                    if (gameId === 1129074000){

                        results = Manhunt2Mdl(binary);

                    }else{

                        results = ManhuntDff(binary);

                    }


                    //TODO !!!
                    var cache = {};

                    callback({
                        getModelNames: function(){
                            var result = [];

                            for(var i in results){
                                if (!results.hasOwnProperty(i)) continue;

                                var entry = results[i];
                                if (gameId === 1129074000){
                                    if (entry.objects.length === 0) continue;
                                    result.push(entry.bone.boneName);
                                }else{
                                    result.push(entry.name);
                                }
                            }

                            return result;
                        },

                        find: function (name) {
                            for(var i in results){
                                if (!results.hasOwnProperty(i)) continue;

                                var entry = results[i];
                                var threeModel;

                                if (gameId === 1129074000){
                                    if (entry.objects.length === 0) continue;

                                    if (entry.bone.boneName.toLowerCase() === name.toLowerCase()){

                                        threeModel = new MdlModelConverter(level, entry);
                                        threeModel.mesh.name = entry.bone.boneName;
                                        return threeModel.mesh;
                                    }
                                }else{
                                    if (entry.name.toLowerCase() === name.toLowerCase()){

                                        threeModel = new DffModelConverter(level, entry);
                                        threeModel.mesh.name = entry.name;
                                        return threeModel.mesh;

                                    }
                                }
                            }

                            return false;
                        }
                    });

                }
            );

        }
    };

};