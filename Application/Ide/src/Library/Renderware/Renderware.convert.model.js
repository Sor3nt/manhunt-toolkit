RW.convert.model = function (tree) {
    assert(tree.type, CHUNK_CLUMP, "convert: Container is not a CHUNK_CLUMP it is " + tree.typeName);

    function getChunk(rwData, type) {
        let found = getChunks(rwData, type);
        if (found === false) return false;

        if (found.length > 1){
            console.error("Tried to get exact one chunk with type", CHUNK_ID_NAME[type], 'but multiple found!');
            die;
        }

        return found[0] || false;
    }

    function getChunks(rwData, type) {
        let found = [];
        rwData.chunks.forEach(function (chunk) {
            if (chunk.type === type) found.push(chunk);
        });

        return found;
    }


    let chunkFrameList = getChunk(tree, CHUNK_FRAMELIST);
    // assert(chunkFrameList.chunks.length, chunkFrameList.data.frameList.length, "convert: CHUNK_FRAMELIST count does not match");
    let bones = [];
    let skinBones = [];

    let frameCount = chunkFrameList.data.frameList.length;

    // let chunkFrameLists = [];
    // for(let i = 0; i < frameCount; i++){
    //     let extension = chunkFrameList.chunks[i];
    //     if (extension.size > 0){
    //         chunkFrameLists.push(extension);
    //     }
    // }

    for(let i = 0; i < frameCount; i++){
        let _chunk = chunkFrameList.chunks[i];
        assert(_chunk.type, CHUNK_EXTENSION);


        let name = "";
        if (_chunk.chunks.length > 0){
            let _chunkFrame =  getChunk(_chunk, CHUNK_FRAME);
            if (_chunkFrame === false)
                name = "bone" + i;
            else
                name = _chunkFrame.data.name;
        }else{
            if (i === 0)
                name = "RootDummy";
            else
                name = "bone" + i;
        }

        let bone = {
            name: name,
            userProp: {},
            frame: chunkFrameList.data.frameList[i]
        };

        if (i > 0){
            bone.userProp.BoneID = tree.data.BoneIDArray[i-1];
        }

        bones.push(bone);

    }

    //Search Bones
    for(let i = 0; i < frameCount; i++){
        let bne = bones[i];
        let boneID = bne.userProp.BoneID;

        if (typeof boneID !== "undefined") {
            let hAnimBoneArray = tree.data.hAnimBoneArray;
            for (j = 0; j < hAnimBoneArray.length; j++) {
                if (hAnimBoneArray[j].BoneID === boneID) {
                    bne.userProp.BoneIndex = hAnimBoneArray[j].BoneIndex;
                    bne.userProp.BoneType = hAnimBoneArray[j].BoneType;
                }
            }
        }
    }

    //Search Skin-Bones
    for(let i = 0; i < frameCount; i++){
        for(let j = 0; j < frameCount; j++){
            let bne = bones[j];
            let boneIndex = bne.userProp.BoneIndex;
            if (typeof boneIndex !== "undefined" && boneIndex === i)
                skinBones.push(bne);
        }
    }

    let chunkGeometryList = getChunk(tree, CHUNK_GEOMETRYLIST);

    let chunksGeometry = getChunks(chunkGeometryList, CHUNK_GEOMETRY);
    let chunksAtomic = getChunks(tree, CHUNK_ATOMIC);
    assert(chunksGeometry.length, chunksAtomic.length, "Atomic does not match with geometry count");



    let parsedObjects = [];
    for(let i = 0; i < chunksGeometry.length; i++){

        let skinFlag = false;
        let skinPLG = {};

        let chunkGeometryExtension = getChunk(chunksGeometry[i], CHUNK_EXTENSION);
        if (chunkGeometryExtension !== false){
            let chunkSkin = getChunk(chunkGeometryExtension, CHUNK_SKIN);
            if (chunkSkin !== false){
                skinFlag = true;
                skinPLG = chunkSkin.data.SkinPLG;
            }
        }

        let chunkMaterialList = getChunk(chunksGeometry[i], CHUNK_MATLIST);
        let chunksMaterial = getChunks(chunkMaterialList, CHUNK_MATERIAL);


        let mesh = {
            skinned: skinFlag,
            parentFrameID: chunksAtomic[i].data.frameIndex,
            material: [],
            skinPLG: skinPLG,
            face: chunksGeometry[i].data.faceMat.Face,
            materialPerFace: chunksGeometry[i].data.faceMat.MatID,
            normal: chunksGeometry[i].data.Normal_array,
            vertices: chunksGeometry[i].data.Vert_array,
            uv1: chunksGeometry[i].data.UV1_array,
            uv2: chunksGeometry[i].data.UV2_array,
            cpv: chunksGeometry[i].data.VColor_Array,
        };

        chunksMaterial.forEach(function (material) {

            let _material = {
                diffuse: material.data.RGBA
            };

            let chunkTexture = getChunk(material, CHUNK_TEXTURE);
            if (chunkTexture !== false){
                assert(chunkTexture.type, CHUNK_TEXTURE);
                let chunksString = getChunks(chunkTexture, CHUNK_STRING);

                _material.TextureName = chunksString[0].data.name;
                if (chunksString[0].data.name)
                    _material.opacitymap = chunksString[1].data.name;
            }

            mesh.material.push(_material);
        });

        parsedObjects.push(mesh);
    }

    return{
        parsedObjects: parsedObjects,
        BoneArray: {
            bones: bones,
            skinBones: skinBones
        }
    };

};
