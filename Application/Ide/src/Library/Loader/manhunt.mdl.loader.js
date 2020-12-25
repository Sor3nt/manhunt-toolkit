/**
 * MDL Reader based on the awesome work from Majest1c_R3 and Allen
 */
MANHUNT.fileLoader.MDL = function () {

    function MhtMDLExtract(inputData){

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

    function MdlModelConverter( model ){

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
                        var texture = MANHUNT.level.getStorage('tex').find(materialObj.TexName);
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

    var loader = new THREE.FileLoader();
    loader.setResponseType( 'arraybuffer' );

    return {
        load: function (file, callback ) {

            var results;

            loader.load(
                file,
                function ( data ) {

                    var binary = new NBinary(data);

                    results = MhtMDLExtract(binary);


                    //TODO !!!
                    var cache = {};

                    callback({
                        getDataRaw: function(){
                            var result = [];

                            for(var i in results){
                                if (!results.hasOwnProperty(i)) continue;
                                result.push(results[i]);
                            }

                            return result;
                        },
                        find: function (name) {
                            for(var i in results){
                                if (!results.hasOwnProperty(i)) continue;

                                var entry = results[i];
                                if (entry.objects.length === 0) continue;

                                if (entry.bone.boneName.toLowerCase() === name.toLowerCase()){

                                    console.log("select model", entry);

                                    var threeModel = new MdlModelConverter(entry);
                                    threeModel.mesh.name = entry.bone.boneName;
                                    // threeModel.mesh.renderOrder = 1;

                                    // if (materialObj.TexName === "ashes"){
                                    // }
                                    // threeModel.mesh.children[0].position.set(0,0,0);
                                    return threeModel.mesh;
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