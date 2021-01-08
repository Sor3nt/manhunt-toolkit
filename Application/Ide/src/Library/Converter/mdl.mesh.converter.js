
MANHUNT.converter.mdl2mesh = function (level, model) {


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
};
