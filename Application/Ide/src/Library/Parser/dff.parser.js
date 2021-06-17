/*--------------------------------
	RenderWare DFF model Importer
	RenderWare Version 3.7.0.2
	Platform PC
	by Allen
	2020-12-13
    ported to JS by Sor3nt
    2021-01-17

	Support game:
		Agent Hugo
		Manhunt
--------------------------------*/

MANHUNT.parser.dff = function (binary, level) {

    let allBones = [];
    let allBonesMesh = [];
    let meshBone = {};

    function ReadChunk() {
        return {
            id: binary.consume(4, 'int32'),
            size: binary.consume(4, 'uint32'),
            version: binary.consume(4, 'uint32')
        };
    }


    function ReadFrameName(NameLength) {
        return binary.consume(NameLength, 'nbinary').getString(0);
    }

    function readUserDataPLG(index) {
        let numSet = binary.consume(4, 'int32');
        let boneName = "bone" + index;

        for (let i = 0; i < numSet; i++) {
            let typeNameLen = binary.consume(4, 'int32');
            binary.seek(typeNameLen);

            binary.consume(4, 'int32'); //u2
            binary.consume(4, 'int32'); //u3

            let nameLen = binary.consume(4, 'int32');
            if (nameLen > 0)
                boneName = binary.consume(nameLen, 'nbinary').getString(0);
        }

        return boneName;
    }


    function ReadClump(offset) {
        binary.setCurrent(offset);

        let converted = Renderware.getModel(binary, offset);
        //
        let normalizeOld = normalizeResult(converted.BoneArray, 0, converted.parsedObjects);
        return normalizeOld;


        console.log("normalize new", converted);
        return converted;
        // let rw = MANHUNT.parser.renderware(binary);
        // let converted = rw.convertToModel(rw.parse());
        //
        // if (offset === 0){
        //     console.log(converted.BoneArray, 0, converted.parsedObjects);
        //     consolasde.log(converted.BoneArray, 0, converted.parsedObjects);
        // }


    }


    function ReadClumpList() {

        let entries = [];

        let count = 1;
        while (binary.current() < binary.length()) {
            let offset = binary.current();
            let DFFname = "";
            let clumpChunk = ReadChunk();
            let next = binary.current() + clumpChunk.size;

            let clumpStruct = ReadChunk();
            binary.seek(clumpStruct.size);
            binary.seek(12);

            let FrameListStructHeader = ReadChunk();
            binary.seek(FrameListStructHeader.size);

            ReadChunk(); // extheader
            let header = ReadChunk();
            // conasdsole.log("header.id", header.id);
            if (header.id === 0x11F) {
                DFFname = readUserDataPLG(1);
            } else if (header.id === 0x253F2FE) {
                DFFname = ReadFrameName(header.size);
            } else if (header.id === 286) {
                ReadChunk();
                let header = ReadChunk();
                if (header.id === 39056126){
                    DFFname = ReadFrameName(header.size);
                } else if (header.id === 3) {
                    let endOfs = binary.current() + header.size;
                    while (binary.current() < endOfs) {
                        let sHeader = ReadChunk();
                        if (sHeader.id === 0x253F2FE) {
                            DFFname = ReadFrameName(sHeader.size);
                        } else if (sHeader.id === 0x11F) {
                            DFFname = readUserDataPLG(1);
                        } else {

                            binary.seek(sHeader.size);
                        }
                    }
                }

            } else if (header.id === 3) {
                let endOfs = binary.current() + header.size;
                while (binary.current() < endOfs) {
                    let sHeader = ReadChunk();
                    if (sHeader.id === 0x253F2FE) {
                        DFFname = ReadFrameName(sHeader.size);
                    } else if (sHeader.id === 0x11F) {
                        DFFname = readUserDataPLG(1);
                    } else {

                        binary.seek(sHeader.size);
                    }
                }
            }

            if (DFFname !== ""){

                (function (offset, name) {
                    entries.push({
                        name: name,
                        offset: offset,
                        data: function(){
                            let mesh = ReadClump(offset);
                            mesh.name = DFFname;
                            return mesh;
                        }
                    });
                })(offset, DFFname);
            }

            binary.setCurrent(next);
            count += 1;
        }

        return entries;
    }

    function normalizeResult( BoneArray, GeometryCount, parsedObjects ) {


        function generateBoneStructure(BoneArray){

            BoneArray.bones.forEach(function (bone) {
                allBones.push(createBone(bone));
            });

            BoneArray.bones.forEach(function (bone, index) {
                BoneArray.bones.forEach(function (boneInner, indexInner) {
                    if (indexInner === 0) return;

                    if (index === boneInner.frame.ParentFrameID - 1){
                        allBones[index].add(allBones[indexInner]);
                    }
                });
            });

            if (BoneArray.skinBones.length > 0){
                BoneArray.skinBones.forEach(function (boneInner, index) {
                    BoneArray.bones.forEach(function (bone, indexInner) {
                        if (bone.name === boneInner.name ){
                            allBonesMesh.push(allBones[indexInner]);
                        }
                    });
                });
            }
        }

        function createBone( data ){

            let bone = new THREE.Bone();
            bone.name = data.name;

            bone.applyMatrix4(
                (new THREE.Matrix4()).fromArray(data.frame.matrix)
            );

            return bone;
        }

        let result = {
            skeleton: false,

            bones: [],
            objects: []
        };

        generateBoneStructure(BoneArray);

        result.skeleton = new THREE.Skeleton( allBones );
        result.skeleton.bones.forEach(function(bone){
            bone.updateWorldMatrix();
        });

        parsedObjects.forEach(function (parsedObject) {
            meshBone = result.skeleton.bones[parsedObject.parentFrameID];

            let genericObject = {
                material: [],
                skinning: parsedObject.skinned,
                meshBone: meshBone,

                faces: [],
                faceVertexUvs: [[]],

                vertices: [],
                skinIndices: [],
                skinWeights: [],
            };


            parsedObject.material.forEach(function (parsedMaterial) {

                //TODO diffuse color
                if (typeof parsedMaterial.TextureName === "undefined") return;

                let material = new THREE.MeshStandardMaterial();
                material.name = parsedMaterial.TextureName;
                material.skinning = genericObject.skinning;
                material.vertexColors = THREE.VertexColors;

                genericObject.material.push(material);
            });

            parsedObject.vertices.forEach(function (vertexInfo, index) {

                if (BoneArray.skinBones.length > 0 && typeof parsedObject.skinPLG.boneids !== "undefined") {

                    var indice = new THREE.Vector4(0,0,0,0);
                    indice.fromArray(parsedObject.skinPLG.boneids[index]);
                    genericObject.skinIndices.push(indice);

                    var weight = new THREE.Vector4(0,0,0,0);
                    weight.fromArray(parsedObject.skinPLG.weights[index]);
                    genericObject.skinWeights.push(weight);

                }

                genericObject.vertices.push(
                    new THREE.Vector3( vertexInfo.x, vertexInfo.y, vertexInfo.z )
                );

            });

            for(let x = 0; x < parsedObject.face.length; x++) {

                let face = new THREE.Face3(parsedObject.face[x][0], parsedObject.face[x][1], parsedObject.face[x][2]);

                face.materialIndex = parsedObject.materialPerFace[x];

                face.vertexNormals =[
                    parsedObject.normal[face.a],
                    parsedObject.normal[face.b],
                    parsedObject.normal[face.c]
                ];

                if(parsedObject.uv1.length > 0){
                    genericObject.faceVertexUvs[0].push([
                        new THREE.Vector2(
                            parsedObject.uv1[face.a][0],
                            parsedObject.uv1[face.a][1]
                        ),
                        new THREE.Vector2(
                            parsedObject.uv1[face.b][0],
                            parsedObject.uv1[face.b][1]
                        ),
                        new THREE.Vector2(
                            parsedObject.uv1[face.c][0],
                            parsedObject.uv1[face.c][1]
                        ),
                    ]);
                }

                genericObject.faces.push(face);
            }

            result.objects.push(genericObject);
        });

        if (allBonesMesh.length > 0){
            //we need to rebuild the skeleton based only on mesh bones otherwise the indices and weight orders are wrong
            result.skeleton = new THREE.Skeleton( allBonesMesh );
        }


        allBones = [];
        allBonesMesh = [];
        meshBone = {};

        return result;
    }

    return ReadClumpList();
};