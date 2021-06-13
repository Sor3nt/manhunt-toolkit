
import Renderware from "./../Renderware.js";
import Helper from './../../../Helper.js'
import {MeshStandardMaterial, Bone, Skeleton, Vector4, Face3, Vector2, Vector3, VertexColors, Matrix4} from "three";
const assert = Helper.assert;


class NormalizeModel{

    constructor( tree ){
        assert(tree.type, Renderware.CHUNK_CLUMP, "convert: Container is not a Renderware.CHUNK_WORLD it is " + tree.type);
        this.tree = tree;

        this.chunkFrameList = Renderware.findChunk(this.tree, Renderware.CHUNK_FRAMELIST);
        this.frameCount = this.chunkFrameList.result.frameList.length;

        this.allBones = [];
        this.allBonesMesh = [];
    }

    getFrameBones(){

        let bones = [];
        for(let i = 0; i < this.frameCount; i++){
            let extension = this.chunkFrameList.result.chunks[i];
            assert.strictEqual(extension.type, Renderware.CHUNK_EXTENSION);

            let name = "bone" + i;
            if (extension.result.chunks.length > 0){
                let _chunkFrame =  Renderware.findChunk(extension, Renderware.CHUNK_FRAME);
                if (_chunkFrame !== false)
                    name = _chunkFrame.result.name;
            }else{
                if (i === 0)
                    name = "RootDummy";
            }

            let bone = {
                name: name,
                userProp: {},
                frame: this.chunkFrameList.result.frameList[i]
            };

            if (i > 0 && typeof this.tree.result.boneIdArray !== "undefined"){
                bone.userProp.boneId = this.tree.result.boneIdArray[i-1];
            }

            bones.push(bone);

        }

        for(let i = 0; i < this.frameCount; i++){
            let bne = bones[i];
            let boneId = bne.userProp.boneId;

            if (typeof boneId !== "undefined") {
                let hAnimBoneArray = this.tree.result.hAnimBoneArray;
                for (let j = 0; j < hAnimBoneArray.length; j++) {
                    if (hAnimBoneArray[j].boneId === boneId) {
                        bne.userProp.boneIndex = hAnimBoneArray[j].boneIndex;
                        bne.userProp.boneType = hAnimBoneArray[j].boneType;
                    }
                }
            }
        }

        return bones;
    }

    getSkinBones(bones){
        let skinBones = [];

        for(let i = 0; i < this.frameCount; i++){
            for(let j = 0; j < this.frameCount; j++){
                let bne = bones[j];
                let boneIndex = bne.userProp.boneIndex;
                if (typeof boneIndex !== "undefined" && boneIndex === i)
                    skinBones.push(bne);
            }
        }

        return skinBones;
    }

    getMeshes(){
        let chunkGeometryList = Renderware.findChunk(this.tree, Renderware.CHUNK_GEOMETRYLIST);
        let chunksGeometry = Renderware.findChunks(chunkGeometryList, Renderware.CHUNK_GEOMETRY);
        let chunksAtomic = Renderware.findChunks(this.tree, Renderware.CHUNK_ATOMIC);
        assert(chunksGeometry.length, chunksAtomic.length, "Atomic does not match with geometry count");


        let meshes = [];

        for(let i = 0; i < chunksGeometry.length; i++){

            let skinFlag = false;
            let skinPLG = {};

            let chunkGeometryExtension = Renderware.findChunk(chunksGeometry[i], Renderware.CHUNK_EXTENSION);
            if (chunkGeometryExtension !== false){
                let chunkSkin = Renderware.findChunk(chunkGeometryExtension, Renderware.CHUNK_SKIN);
                if (chunkSkin !== false){
                    skinFlag = true;
                    skinPLG = chunkSkin.result.skinPLG;
                }
            }

            let chunkMaterialList = Renderware.findChunk(chunksGeometry[i], Renderware.CHUNK_MATLIST);
            let chunksMaterial = Renderware.findChunks(chunkMaterialList, Renderware.CHUNK_MATERIAL);

            let mesh = {
                skinned: skinFlag,
                parentFrameID: chunksAtomic[i].result.frameIndex,
                material: [],
                skinPLG: skinPLG,
                face: chunksGeometry[i].result.faceMat.face,
                materialPerFace: chunksGeometry[i].result.faceMat.matId,
                normal: chunksGeometry[i].result.normal,
                vertices: chunksGeometry[i].result.vert,
                uv1: chunksGeometry[i].result.uv1,
                uv2: chunksGeometry[i].result.uv2,
                cpv: chunksGeometry[i].result.vColor,
            };

            chunksMaterial.forEach(function (material) {

                let _material = {
                    diffuse: material.result.rgba,
                    textureName: null,
                    opacitymap: null,
                };

                let chunkTexture = Renderware.findChunk(material, Renderware.CHUNK_TEXTURE);
                if (chunkTexture !== false){
                    Helper.assert(chunkTexture.type, Renderware.CHUNK_TEXTURE);
                    let chunksString = Renderware.findChunks(chunkTexture, Renderware.CHUNK_STRING);

                    _material.textureName = chunksString[0].result.name;
                    if (chunksString[1].result.name)
                        _material.opacitymap = chunksString[1].result.name;
                }

                mesh.material.push(_material);
            });

            meshes.push(mesh);
        }

        return meshes;
    }

    createBone( data ){
        let bone = new Bone();
        bone.name = data.name;
        bone.applyMatrix4((new Matrix4()).fromArray(data.frame.matrix));
        return bone;
    }

    generateSkeletonBones(frameBones, skinBones){

        let _this = this;
        frameBones.forEach(function (bone) {
            _this.allBones.push(_this.createBone(bone));
        });

        frameBones.forEach(function (bone, index) {
            frameBones.forEach(function (boneInner, indexInner) {
                if (indexInner === 0) return;

                if (index === boneInner.frame.ParentFrameID - 1){
                    _this.allBones[index].add(_this.allBones[indexInner]);
                }
            });
        });

        if (skinBones.length > 0){
            skinBones.forEach(function (boneInner) {
                frameBones.forEach(function (bone, indexInner) {
                    if (bone.name === boneInner.name ){
                        _this.allBonesMesh.push(_this.allBones[indexInner]);
                    }
                });
            });
        }
    }

    normalize(){
        let meshes = this.getMeshes();
        let frameBones = this.getFrameBones();
        let skinBones = this.getSkinBones(frameBones);

        this.generateSkeletonBones(frameBones, skinBones);

        let result = {
            skeleton: false,

            bones: [],
            objects: []
        };

        result.skeleton = new Skeleton( this.allBones );
        result.skeleton.bones.forEach(function(bone){
            bone.updateWorldMatrix();
        });

        let meshBone;
        meshes.forEach(function (mesh) {
            meshBone = result.skeleton.bones[mesh.parentFrameID];

            let genericObject = {
                material: [],
                skinning: mesh.skinned,
                meshBone: meshBone,

                faces: [],
                faceVertexUvs: [[]],

                vertices: [],
                skinIndices: [],
                skinWeights: [],
            };


            mesh.material.forEach(function (parsedMaterial) {

                //TODO diffuse color
                if (typeof parsedMaterial.TextureName === "undefined") return;

                let material = new MeshStandardMaterial();
                material.name = parsedMaterial.textureName;
                material.skinning = genericObject.skinning;
                material.vertexColors = VertexColors;

                genericObject.material.push(material);
            });

            mesh.vertices.forEach(function (vertexInfo, index) {

                if (skinBones.length > 0 && typeof mesh.skinPLG.boneids !== "undefined") {

                    let indice = new Vector4(0,0,0,0);
                    indice.fromArray(mesh.skinPLG.boneids[index]);
                    genericObject.skinIndices.push(indice);

                    let weight = new Vector4(0,0,0,0);
                    weight.fromArray(mesh.skinPLG.weights[index]);
                    genericObject.skinWeights.push(weight);

                }

                genericObject.vertices.push(
                    new Vector3( vertexInfo.x, vertexInfo.y, vertexInfo.z )
                );

            });

            for(let x = 0; x < mesh.face.length; x++) {

                let face = new Face3(mesh.face[x][0], mesh.face[x][1], mesh.face[x][2]);

                face.materialIndex = mesh.materialPerFace[x];

                face.vertexNormals =[
                    mesh.normal[face.a],
                    mesh.normal[face.b],
                    mesh.normal[face.c]
                ];

                if(mesh.uv1.length > 0){
                    genericObject.faceVertexUvs[0].push([
                        new Vector2(
                            mesh.uv1[face.a][0],
                            mesh.uv1[face.a][1]
                        ),
                        new Vector2(
                            mesh.uv1[face.b][0],
                            mesh.uv1[face.b][1]
                        ),
                        new Vector2(
                            mesh.uv1[face.c][0],
                            mesh.uv1[face.c][1]
                        ),
                    ]);
                }

                genericObject.faces.push(face);
            }

            result.objects.push(genericObject);
        });

        if (this.allBonesMesh.length > 0){
            //we need to rebuild the skeleton based only on mesh bones otherwise the indices and weight orders are wrong
            result.skeleton = new Skeleton( this.allBonesMesh );
        }

        return result;
    }
}