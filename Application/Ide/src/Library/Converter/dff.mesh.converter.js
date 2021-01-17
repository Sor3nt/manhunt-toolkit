
MANHUNT.converter.dff2mesh = function (level, model) {
    console.log(model);

    var self = {
        _rootBone : {},
        _meshBone : {},
        _allBones : [],
        _boneInfos : [],

        _mesh: new THREE.Group(),

        _init: function () {
            self._generateBoneStructure();
// console.log(self._allBones);
            // var meshParentMatrix = false;
            // self._allBones.forEach(function (bone) {
            //     if (bone.name === model.name)
            //         meshParentMatrix = bone.matrix;
            // });
            //
            // if (meshParentMatrix === false)
            //     return console.error('[MANHUNT.fileLoader.MDL] Matrix not found');

            var skeleton = new THREE.Skeleton( self._allBones );

            var material = [];

            var object = model.data.geometry[0];


            var geometry = new THREE.Geometry();
            geometry.colorsNeedUpdate = true;
            geometry.normalsNeedUpdate = true;

            geometry.faceVertexUvs = [[]];

            object.vertices.forEach(function (vertexVec3, index) {
                //
                // if (typeof object.skinPLG.boneids !== "undefined"){
                //     // console.log("EHM", self._allBones[object.skinPLG.boneids[index]]);
                //     vertexVec3.applyMatrix4(self._allBones[object.skinPLG.boneids[index][0]].matrix);
                //
                // }
                geometry.vertices.push(vertexVec3);
            });

            object.material.forEach(function (data) {

                if (typeof data.TextureName === "undefined"){
                    //TODO diffuse color
                    return;
                }

                var texture = level._storage.tex.find(data.TextureName);
                var mat = new THREE.MeshStandardMaterial();
                mat.name = data.TextureName;
                mat.map = texture;
                mat.skinning = true;
                mat.vertexColors = THREE.VertexColors;
                mat.needsUpdate = true;
                mat.transparent = texture.format === THREE.RGBA_S3TC_DXT5_Format;

                material.push(
                    mat
                );
            });

            for(var x = 0; x < object.face.length; x++) {

                var face = new THREE.Face3(object.face[x][0], object.face[x][1], object.face[x][2]);

                // face.materialIndex = materialForFace[x].MaterialID;

                face.vertexNormals =[
                    object.normal[face.a],
                    object.normal[face.b],
                    object.normal[face.c]
                ];

                if(object.uv1.length > 0){
                    geometry.faceVertexUvs[0].push([
                        new THREE.Vector2(
                            object.uv1[face.a][0],
                            object.uv1[face.a][1]
                        ),
                        new THREE.Vector2(
                            object.uv1[face.b][0],
                            object.uv1[face.b][1]
                        ),
                        new THREE.Vector2(
                            object.uv1[face.c][0],
                            object.uv1[face.c][1]
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
// console.log("target bbone", self._allBones[object.parentFrameID]);
//
//             mesh.applyMatrix4(self._allBones[object.parentFrameID].matrix);
            // mesh.applyMatrix4(self._allBones[0].matrix);

            mesh.add(self._allBones[0]);
            mesh.bind(skeleton);

            self._mesh.add(mesh);


        },


        _generateBoneStructure: function(){
            model.data.skeleton.bones.forEach(function (bone, index) {
                var realBone = self._createBone(bone);
                self._allBones.push(realBone);
            });

            model.data.skeleton.bones.forEach(function (bone, index) {

                // self._allBones[index].applyMatrix4(self._allBones[model.data.geometry[0].parentFrameID].matrix);


                model.data.skeleton.bones.forEach(function (boneInner, indexInner) {
                    if (indexInner === 0) return;

                    if (index === boneInner.frame.ParentFrameID){
                        self._allBones[index].add(self._allBones[indexInner]);
                    }
                });
            });
        },

        _createBone: function ( data ){

            var bone = new THREE.Bone();
            bone.applyMatrix4(
                (new THREE.Matrix4()).fromArray(data.frame.matrix)
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
