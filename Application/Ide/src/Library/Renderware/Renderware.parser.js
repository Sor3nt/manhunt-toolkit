RW.parser = function (binary, rootChunk) {


    rootChunk = rootChunk || {};

    let rwChunks = {};

    rwChunks[CHUNK_STRUCT] = function (header, rwData) {
        return rwData;
    };

    rwChunks[CHUNK_NAOBJECT] = function (header, rwData) {
        return rwData;
    };

    rwChunks[CHUNK_RIGHTTORENDER] = function (header, rwData) {
        return rwData;
    };

    rwChunks[CHUNK_EXTENSION] = function (header, rwData) {

        while(rwData.binary.remain() > 0){
            rwData.chunks.push(rwData.processChunk());
        }

        assert(rwData.binary.remain(), 0, 'CHUNK_EXTENSION: Unable to parse fully the data!');

        return rwData;
    };


    rwChunks[CHUNK_MATERIALEFFECTS] = function (header, rwData) {
        //wrong, see https://github.com/leonvb/GTA_Unity_City/blob/a29ac805b08088705a20cbf9050301775fcab75e/Assets/Scripts/Renderware/DFF/Atomic.cs#L77
        // rwData.data.matFXenabled = binary.consume(4, 'int32'); // bool32- MatFX enabled MaterialEffectsPLG 0x120
        // assert(rwData.binary.remain(), 0, 'CHUNK_MATERIALEFFECTS: Unable to parse fully the data! Remain ' + rwData.binary.remain());

        return rwData;
    };

    rwChunks[CHUNK_REFLECTIONMAT] = function (header, rwData) {

        rwData.data = {
            EnvironmentMapScaleX: rwData.binary.consume(4, 'float32'), // Environment Map Scale X
            EnvironmentMapScaleY: rwData.binary.consume(4, 'float32'), // Environment Map Scale Y
            EnvironmentMapOffsetX: rwData.binary.consume(4, 'float32'), // Environment Map Offset X
            EnvironmentMapOffsetY: rwData.binary.consume(4, 'float32'), // Environment Map Offset Y
            ReflectionIntensity: rwData.binary.consume(4, 'float32'), // Reflection Intensity (Shininess, 0.0-1.0)
            EnvironmentTexturePtr: rwData.binary.consume(4, 'float32') // Environment Texture Ptr, always 0 (zero)
        };

        assert(rwData.binary.remain(), 0, 'CHUNK_REFLECTIONMAT: Unable to parse fully the data!');

        return rwData;
    };

    rwChunks[CHUNK_SKIN] = function (header, rwData) {
        rwData.data.hasSkin = true;

        if (rootChunk.data.hasNativeGeometry){
            console.warn("UNTESTED SECTIOIN!! CHUNK_SKIN hasNativeGeometry");
            let platform = rwData.binary.consume(4, 'uint32');
            rwData.binary.seek(-4);
            if (platform === PLATFORM_OGL || platform === PLATFORM_PS2 || platform === PLATFORM_XBOX){
                rwData.chunks.push(rwData.processChunk());
            }else{
                //unknown native data format
                console.error('CHUNK_SKIN: Unknown Platform !');
                rwData.binary.consume(rwData.binary.remain(), 'nbinary');
            }
        }else{

            let boneCount = rwData.binary.consume(1, 'uint8');
            let usedIdCount = rwData.binary.consume(1, 'uint8');
            rwData.binary.seek(2); //maxWeightsPerVertex
            rwData.binary.seek(usedIdCount);

            let skinPLG = {boneids: [], weights: [], inverseMatrix: []};

            for (let i = 0; i < rootChunk.data.vertexCount; i++) {
                skinPLG.boneids.push([
                    rwData.binary.consume(1, 'uint8'),
                    rwData.binary.consume(1, 'uint8'),
                    rwData.binary.consume(1, 'uint8'),
                    rwData.binary.consume(1, 'uint8')
                ]);
            }

            for (let i = 0; i < rootChunk.data.vertexCount; i++) {
                skinPLG.weights.push(rwData.binary.readFloats(4));
            }

            for (let i = 0; i < boneCount; i++) {
                skinPLG.inverseMatrix.push(rwData.binary.readFloats(16));
            }

            while(rwData.binary.remain() > 0){
                rwData.chunks.push(rwData.processChunk());
            }

            rwData.data.SkinPLG = skinPLG;
        }

        assert(rwData.binary.remain(), 0, 'CHUNK_SKIN: Unable to parse fully the data! Remain ' + rwData.binary.remain());

        return rwData;
    };

    rwChunks[CHUNK_GEOMETRYLIST] = function (header, rwData) {

        let struct = rwData.processChunk();
        assert(struct.type, CHUNK_STRUCT);

        let GeometryCount = struct.binary.consume(4, 'uint32');
        while(GeometryCount--){
            rwData.chunks.push( rwData.processChunk() );
        }

        assert(rwData.binary.remain(), 0, 'CHUNK_GEOMETRYLIST: Unable to parse fully the data!');

        return rwData;
    };

    rwChunks[CHUNK_CLUMP] = function (header, rwData) {

        while(rwData.binary.remain() > 0){
            rwData.chunks.push( rwData.processChunk() );
        }

        assert(rwData.binary.remain(), 0, 'CHUNK_CLUMP: Unable to parse fully the data! Remain ' + rwData.binary.remain());

        return rwData;
    };

    rwChunks[CHUNK_ATOMIC] = function (header, rwData) {

        let struct = rwData.processChunk();
        assert(struct.type, CHUNK_STRUCT);

        rwData.data.frameIndex = struct.binary.consume(4, 'int32');
        rwData.data.geometryIndex = struct.binary.consume(4, 'int32');
        rwData.data.flags = struct.binary.consume(4, 'int32');
        struct.binary.consume(4, 'int32'); //constant

        assert(struct.binary.remain(), 0, 'CHUNK_ATOMIC: Unable to parse fully the struct data!');

        let extention = rwData.processChunk();
        assert(extention.type, CHUNK_EXTENSION);
        rwData.chunks.push(extention);

        assert(rwData.binary.remain(), 0, 'CHUNK_ATOMIC: Unable to parse fully the rw data! Remain ' + rwData.binary.remain());

        return rwData;
    };

    rwChunks[CHUNK_BINMESH] = function (header, rwData) {

        rwData.binary.seek(4); // FaceType
        let splitCount = rwData.binary.consume(4, 'uint32');
        rwData.binary.seek(4); //FaceCount

        let hasData = header.size > 12+splitCount*8;

        rwData.data.faces = [];
        rwData.data.materialIds = [];
        for(let i = 0; i < splitCount; i++){
            let splitFaceCount = rwData.binary.consume(4, 'uint32'); //numIndices
            rwData.data.materialIds.push(rwData.binary.consume(4, 'uint32') + 1);

            if (!hasData) continue;

            for (let i = 0; i < splitFaceCount; i++) {
                if (rootChunk.data.hasNativeGeometry){
                    rwData.data.faces.push(rwData.binary.consume(2, 'uint16') + 1);
                }else{
                    rwData.data.faces.push(rwData.binary.consume(4, 'uint32') + 1);
                }
            }
        }

        assert(rwData.binary.remain(), 0, 'CHUNK_BINMESH: Unable to parse fully the data!');

        return rwData;
    };

    rwChunks[CHUNK_STRING] = function (header, rwData) {
        rwData.data.name = rwData.binary.getString(0);
        return rwData;
    };

    rwChunks[CHUNK_SKYMIPMAP] = function (header, rwData) {
        return rwData;
    };

    rwChunks[CHUNK_TEXTURE] = function (header, rwData) {
        let struct = rwData.processChunk();
        assert(struct.type, CHUNK_STRUCT);

        rwData.data.filterFlag = struct.binary.consume(2, 'int16');
        struct.binary.consume(2, 'int16'); //unknown
        assert(struct.binary.remain(), 0, 'CHUNK_TEXTURE: Unable to parse fully the struct data!' );

        let textureName = rwData.processChunk();
        assert(textureName.type, CHUNK_STRING);
        rwData.chunks.push(textureName);

        let alphaTextureName = rwData.processChunk();
        assert(alphaTextureName.type, CHUNK_STRING);
        rwData.chunks.push(alphaTextureName);

        let extension = rwData.processChunk();
        assert(extension.type, CHUNK_EXTENSION);

        assert(rwData.binary.remain(), 0, 'CHUNK_TEXTURE: Unable to parse fully the rw data!' );

        return rwData;
    };

    rwChunks[CHUNK_MATERIAL] = function (header, rwData) {
        let struct = rwData.processChunk();
        assert(struct.type, CHUNK_STRUCT);

        rwData.data.flags = struct.binary.consume(4, 'int32');
        rwData.data.RGBA = struct.binary.readColorRGBA();
        struct.binary.consume(4, 'int32'); //unused
        let hasTexture = struct.binary.consume(4, 'int32') !== 0;

        rwData.surfaceProp = {
            ambient: struct.binary.consume(4, 'float32'),
            diffuse: struct.binary.consume(4, 'float32'),
            specular: struct.binary.consume(4, 'float32')
        };

        assert(struct.binary.remain(), 0, 'CHUNK_MATERIAL: Unable to parse fully the struct data!' );

        if (hasTexture){
            let texture = rwData.processChunk();
            assert(texture.type, CHUNK_TEXTURE);
            rwData.chunks.push(texture);
        }

        let extention = rwData.processChunk();
        assert(extention.type, CHUNK_EXTENSION);
        rwData.chunks.push(extention);

        assert(rwData.binary.remain(), 0, 'CHUNK_MATERIAL: Unable to parse fully the rw data!');

        return rwData;
    };

    rwChunks[CHUNK_MATLIST] = function (header, rwData) {

        let struct = rwData.processChunk();
        assert(struct.type, CHUNK_STRUCT);

        let materialCount = struct.binary.consume(4, 'int32'); //numMaterials
        struct.binary.seek(materialCount * 4); // constant

        assert(struct.binary.remain(), 0, 'CHUNK_MATLIST struct: Unable to parse fully the data!');

        for (let i = 0; i < materialCount; i++) {
            rwData.chunks.push(rwData.processChunk());
        }

        assert(rwData.binary.remain(), 0, 'CHUNK_MATLIST: Unable to parse fully the struct data!');

        return rwData;
    };

    rwChunks[CHUNK_GEOMETRY] = function (header, rwData) {

        let struct = rwData.processChunk();
        assert(struct.type, CHUNK_STRUCT);

        let formatFlags = struct.binary.consume(2, 'uint16'); // flags
        struct.binary.seek(1); //NumTexCoorsCustom  / numUVs
        rootChunk.data.hasNativeGeometry = struct.binary.consume(1, 'int8') !== 0; //GeometryNativeFlags

        let faceCount = struct.binary.consume(4, 'uint32');
        rootChunk.data.vertexCount = struct.binary.consume(4, 'uint32');

        struct.binary.consume(4, 'uint32'); //numMorphTargets

        /*
        // skip light info
           LibraryVersion libVer = header.getVersion();

            if (libVer.rwLibMinor <= 3)
            {
                rw.seekg(12, std::ios::cur);
            }

         */
        rwData.data.VColor_Array = [];
        rwData.data.UV1_array = [];
        rwData.data.UV2_array = [];
        rwData.data.Vert_array = [];
        rwData.data.Normal_array = [];

        if (!rootChunk.data.hasNativeGeometry){

            if ((formatFlags & rpGEOMETRYPRELIT) === rpGEOMETRYPRELIT){
                // if (formatFlags & FLAGS_PRELIT){
                for(let i = 0; i < rootChunk.data.vertexCount; i++){
                    rwData.data.VColor_Array.push(struct.binary.readColorRGBA());
                }
            }

            if ((formatFlags & rpGEOMETRYTEXTURED) === rpGEOMETRYTEXTURED || (formatFlags & rpGEOMETRYTEXTURED2) === rpGEOMETRYTEXTURED2){
                // if (formatFlags & FLAGS_TEXTURED){
                for(let i = 0; i < rootChunk.data.vertexCount; i++){
                    rwData.data.UV1_array.push([
                        struct.binary.consume(4, 'float32'),
                        struct.binary.consume(4, 'float32')
                    ]);
                }
            }

            if ((formatFlags & rpGEOMETRYTEXTURED2) === rpGEOMETRYTEXTURED2){
                // if (formatFlags & FLAGS_TEXTURED2){
                for(let i = 0; i < rootChunk.data.vertexCount; i++){
                    rwData.data.UV2_array.push([
                        struct.binary.consume(4, 'float32'),
                        struct.binary.consume(4, 'float32')
                    ]);
                }

                // for(let u = 0; u < numUv; u++){
                //     for(let i = 0; i < RW.parserTmp.vertexCount; i++){
                //         rwData.data.UV2_array.push([
                //             struct.binary.consume(4, 'float32'),
                //             struct.binary.consume(4, 'float32')
                //         ]);
                //     }
                // }
            }

            //read faces
            rwData.data.faceMat = {
                Face: [],
                MatID: [],
            };

            for (let i = 0; i < faceCount; i++) {

                let f2 = struct.binary.consume(2, 'uint16');
                let f1 = struct.binary.consume(2, 'uint16');
                let matID = struct.binary.consume(2, 'uint16');
                let f3 = struct.binary.consume(2, 'uint16');

                rwData.data.faceMat.Face.push([f1, f2, f3]);
                rwData.data.faceMat.MatID.push(matID);
            }

        }

        rwData.data.boundingSphere = {
            position: struct.binary.readVector3(),
            radius: struct.binary.consume(4, 'float32')
        };

        struct.binary.seek(4); //hasPosition
        struct.binary.seek(4); //hasNormal: need to recompute. Edit: hmmw why?
        // let hasNormals = (formatFlags & FLAGS_NORMALS) ? 1 : 0;

        if (!rootChunk.data.hasNativeGeometry){
            for (let i = 0; i < rootChunk.data.vertexCount; i++) {
                rwData.data.Vert_array.push(struct.binary.readVector3());
            }

            if (formatFlags & FLAGS_NORMALS){
                for (let i = 0; i < rootChunk.data.vertexCount; i++) {
                    rwData.data.Normal_array.push(struct.binary.readVector3());
                }
            }

        }

        assert(struct.binary.remain(), 0, 'CHUNK_GEOMETRY: Unable to parse fully the struct data!');

        while(rwData.binary.remain() > 0){
            rwData.chunks.push( rwData.processChunk() );
        }

        assert(rwData.binary.remain(), 0, 'CHUNK_GEOMETRY: Unable to parse fully the rw data! Remain ' +  rwData.binary.remain());

        return rwData;
    };

    rwChunks[CHUNK_HANIM] = function (header, rwData) {

        rwData.binary.seek(4); // unknown
        let boneID = rwData.binary.consume(4, 'int32');
        let boneCount = rwData.binary.consume(4, 'uint32');

        rwData.data.boneID = boneID;
        rwData.data.boneCount = boneCount;

        rootChunk.data.BoneIDArray.push(boneID);

        rwData.data.bones = [];
        if (boneCount > 0) {

            rwData.binary.seek(4); //flags
            rwData.binary.seek(4); //keyFrameSize

            for (let i = 0; i < boneCount; i++) {
                let animBone = {
                    BoneID: rwData.binary.consume(4, 'int32'),
                    BoneIndex: rwData.binary.consume(4, 'uint32'),
                    BoneType: rwData.binary.consume(4, 'uint32'),
                };
                rwData.data.bones.push(animBone);

                rootChunk.data.hAnimBoneArray.push(animBone);
            }
        }

        assert(rwData.binary.remain(), 0, 'CHUNK_HANIM: Unable to parse fully the data!');

        return rwData;
    };

    rwChunks[CHUNK_FRAME] = function (header, rwData) {
        rwData.data.name = rwData.binary.getString(0);
        return rwData;
    };

    rwChunks[CHUNK_FRAMELIST] = function (header, rwData) {

        let frameList = [];
        while(rwData.binary.remain() > 0){
            let chunk = rwData.processChunk();

            switch (chunk.type) {

                case CHUNK_EXTENSION:
                    rwData.chunks.push(chunk);
                    break;

                case CHUNK_STRUCT:

                    let frameCount = chunk.binary.consume(4, 'int32');
                    for(let i = 0; i < frameCount; i++){
                        frameList.push({
                            matrix: [
                                chunk.binary.consume(4, 'float32'), chunk.binary.consume(4, 'float32'), chunk.binary.consume(4, 'float32'), 0,
                                chunk.binary.consume(4, 'float32'), chunk.binary.consume(4, 'float32'), chunk.binary.consume(4, 'float32'), 0,
                                chunk.binary.consume(4, 'float32'), chunk.binary.consume(4, 'float32'), chunk.binary.consume(4, 'float32'), 0,
                                chunk.binary.consume(4, 'float32'), chunk.binary.consume(4, 'float32'), chunk.binary.consume(4, 'float32'), 1
                            ],
                            ParentFrameID: chunk.binary.consume(4, 'int32') + 1,
                            matrixCreationFlags: chunk.binary.consume(4, 'int32'),
                        });
                    }

                    assert(chunk.binary.remain(), 0, 'CHUNK_FRAMELIST: Unable to parse fully the data!');

                    break;
                default:
                    console.error("CHUNK_FRAMELIST: Unknown chunk type " + CHUNK_ID_NAME[chunk.type]);
                    die;
                    break;

            }

        }

        assert(rwData.binary.remain(), 0, 'CHUNK_FRAMELIST: Unable to parse fully the data!');

        rwData.data.frameList = frameList;

        return rwData;
    };

    function processChunk() {
        let header = {
            id: binary.consume(4, 'int32'),
            size: binary.consume(4, 'uint32'),
            version: binary.consume(4, 'uint32')
        };

        if (typeof rwChunks[header.id] === "undefined"){
            console.error("Chunk function ", CHUNK_ID_NAME[header.id], "not found! Binary pos" , binary.current() - 12, header);
            die;
        }

        let data = binary.consume(header.size, 'nbinary');

        let rw = RW.parser(data, rootChunk);
        rw.header = header;
        rw.type = header.id;
        rw.typeName = CHUNK_ID_NAME[header.id];


        if (JSON.stringify(rootChunk) === JSON.stringify({})){
            rootChunk.root = rw;
            rootChunk.data = {
                hasNativeGeometry: false,
                vertexCount: false,
                BoneIDArray: [],
                hAnimBoneArray: [],
            };

        }

        return rwChunks[header.id](header, rw);
    }


    function cleanTree(rwData) {
        let chunks = [];
        rwData.chunks.forEach(function (chunk) {
            let _chunk = cleanTree(chunk);
            if (_chunk.type === CHUNK_STRUCT  && _chunk.chunks.length === 0)
                return;
            if (_chunk.type === CHUNK_NAOBJECT  && _chunk.chunks.length === 0)
                return;
            if (_chunk.type === CHUNK_RIGHTTORENDER  && _chunk.chunks.length === 0)
                return;

            // console.log("-".repeat(deep + 1), CHUNK_ID_NAME[_chunk.type], _chunk.data, "childs", _chunk.chunks.length);

            chunks.push(_chunk);
        });


        return {
            type: rwData.type,
            typeName: CHUNK_ID_NAME[rwData.type],
            data: rwData.data,
            chunks: chunks
        };

    }


    function parse() {
        rootChunk = {};
        let chunk = processChunk();
        chunk.data = rootChunk.data;
        return cleanTree(chunk);
    }

    return {
        data: {},
        chunks: [],
        binary: binary,
        processChunk: processChunk,
        parse: parse
    };
};
