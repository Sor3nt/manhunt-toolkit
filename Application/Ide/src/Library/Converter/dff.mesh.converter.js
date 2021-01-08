
MANHUNT.converter.dff2mesh = function (level, model) {

    var self = {
        _rootBone : {},
        _meshBone : {},
        _allBones : [],
        _boneInfos : [],

        _mesh: new THREE.Group(),

        _init: function () {
            self._generateBoneStructure();

            var meshParentMatrix = false;
            self._allBones.forEach(function (bone) {
                if (bone.name === model.name)
                    meshParentMatrix = bone.matrix;
            });

            if (meshParentMatrix === false)
                return console.error('[MANHUNT.fileLoader.MDL] Matrix not found');

            var skeleton = new THREE.Skeleton( self._allBones );

            model.geometry.forEach(function (entry) {
                var material = [];

                var geometry = new THREE.Geometry();
                geometry.colorsNeedUpdate = true;
                geometry.normalsNeedUpdate = true;

                geometry.faceVertexUvs = [[]];

                entry.vertices.forEach(function (vertexVec3, index) {
                    vertexVec3.applyMatrix4(meshParentMatrix);
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
                }


                var bufferGeometry = new THREE.BufferGeometry();
                bufferGeometry.fromGeometry( geometry );

                bufferGeometry.colorsNeedUpdate = true;

                var mesh;

                //TODO: ATOM section reader
                mesh = new THREE.SkinnedMesh(bufferGeometry, material);
                mesh.add(self._allBones[0]);
                mesh.bind(skeleton);

                self._mesh.add(mesh);
            });

        },


        _generateBoneStructure: function(){
            model.bones.forEach(function (bone, index) {
                var realBone = self._createBone(bone);
                self._allBones.push(realBone);
            });

            model.bones.forEach(function (bone, index) {
                model.bones.forEach(function (boneInner, indexInner) {
                    if (indexInner === 0) return;

                    if (index === boneInner.parentId - 1){
                        self._allBones[index].add(self._allBones[indexInner]);
                    }
                });
            });
        },

        _createBone: function ( data ){

            var bone = new THREE.Bone();
            bone.applyMatrix4(
                (new THREE.Matrix4()).fromArray(data.matrix)
            );
            bone.name = data.name;

            return bone;
        }
    };

    self._init();


    return {
        mesh: self._mesh
    };
};
