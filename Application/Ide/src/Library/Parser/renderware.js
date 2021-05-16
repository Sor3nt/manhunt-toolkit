MANHUNT.parser.renderwareTmp = {
    hasNativeGeometry: false,
    vertexCount: false,
    BoneIDArray: [],
    hAnimBoneArray: [],
};

MANHUNT.parser.renderware = function (binary) {
    let deep = 0;
    function assert(a, b, msg){
        if (a !== b){
            console.error((msg || ('Expect ' + CHUNK_ID_NAME[b] + ' got ' + CHUNK_ID_NAME[a])) );
            die;
        }
    }


    const rpGEOMETRYPOSITIONS = 0x00000002;
    /**<This geometry has positions */
    const rpGEOMETRYTEXTURED = 0x00000004;
    /**<This geometry has only one set of
     texture coordinates. Texture
     coordinates are specified on a per
     vertex basis */
    const rpGEOMETRYPRELIT = 0x00000008;
    /**<This geometry has pre-light colors */
    const rpGEOMETRYNORMALS = 0x00000010;
    /**<This geometry has vertex normals */

    const rpGEOMETRYTEXTURED2 = 0x00000080;
    /**<This geometry has at least 2 sets of
     texture coordinates. */

    let CHUNK_ID_NAME = {

        0x0: "CHUNK_NAOBJECT",
        0x1: "CHUNK_STRUCT",
        0x2: "CHUNK_STRING",
        0x3: "CHUNK_EXTENSION",
        0x5: "CHUNK_CAMERA",
        0x6: "CHUNK_TEXTURE",
        0x7: "CHUNK_MATERIAL",
        0x8: "CHUNK_MATLIST",
        0x9: "CHUNK_ATOMICSECT",
        0xA: "CHUNK_PLANESECT",
        0xB: "CHUNK_WORLD",
        0xC: "CHUNK_SPLINE",
        0xD: "CHUNK_MATRIX",
        0xE: "CHUNK_FRAMELIST",
        0xF: "CHUNK_GEOMETRY",
        0x10: "CHUNK_CLUMP",
        0x12: "CHUNK_LIGHT",
        0x13: "CHUNK_UNICODESTRING",
        0x14: "CHUNK_ATOMIC",
        0x15: "CHUNK_TEXTURENATIVE",
        0x16: "CHUNK_TEXDICTIONARY",
        0x17: "CHUNK_ANIMDATABASE",
        0x18: "CHUNK_IMAGE",
        0x19: "CHUNK_SKINANIMATION",
        0x1A: "CHUNK_GEOMETRYLIST",
        0x1B: "CHUNK_HANIMANIMATION",
        0x1C: "CHUNK_TEAM",
        0x1D: "CHUNK_CROWD",
        0x1F: "CHUNK_RIGHTTORENDER",
        0x20: "CHUNK_MTEFFECTNATIVE",
        0x21: "CHUNK_MTEFFECTDICT",
        0x22: "CHUNK_TEAMDICTIONARY",
        0x23: "CHUNK_PITEXDICTIONARY",
        0x24: "CHUNK_TOC",
        0x25: "CHUNK_PRTSTDGLOBALDATA",
        0x26: "CHUNK_ALTPIPE",
        0x27: "CHUNK_PIPEDS",
        0x28: "CHUNK_PATCHMESH",
        0x29: "CHUNK_CHUNKGROUPSTART",
        0x2A: "CHUNK_CHUNKGROUPEND",
        0x2B: "CHUNK_UVANIMDICT",
        0x2C: "CHUNK_COLLTREE",
        0x2D: "CHUNK_ENVIRONMENT",
        0x2E: "CHUNK_COREPLUGINIDMAX",

        0x105: "CHUNK_MORPH",
        0x110: "CHUNK_SKYMIPMAP",
        0x116: "CHUNK_SKIN",
        0x118: "CHUNK_PARTICLES",
        0x11E: "CHUNK_HANIM",
        0x120: "CHUNK_MATERIALEFFECTS",
        0x131: "CHUNK_PDSPLG",
        0x134: "CHUNK_ADCPLG",
        0x135: "CHUNK_UVANIMPLG",
        0x50E: "CHUNK_BINMESH",
        0x510: "CHUNK_VERTEXFORMAT",

        0x253F2F3: "CHUNK_PIPELINESET",
        0x253F2F6: "CHUNK_SPECULARMAT",
        0x253F2F8: "CHUNK_2DFX",
        0x253F2F9: "CHUNK_NIGHTVERTEXCOLOR",
        0x253F2FA: "CHUNK_COLLISIONMODEL",
        0x253F2FC: "CHUNK_REFLECTIONMAT",
        0x253F2FD: "CHUNK_MESHEXTENSION",
        0x253F2FE: "CHUNK_FRAME",

    };

    const PLATFORM_OGL = 2;
    const PLATFORM_PS2    = 4;
    const PLATFORM_XBOX   = 5;
    const PLATFORM_D3D8   = 8;
    const PLATFORM_D3D9   = 9;
    const PLATFORM_PS2FOURCC = 0x00325350; /* "PS2\0" */

    const CHUNK_NAOBJECT        = 0x0;
    const CHUNK_STRUCT          = 0x1;
    const CHUNK_STRING          = 0x2;
    const CHUNK_EXTENSION       = 0x3;
    const CHUNK_CAMERA          = 0x5;
    const CHUNK_TEXTURE         = 0x6;
    const CHUNK_MATERIAL        = 0x7;
    const CHUNK_MATLIST         = 0x8;
    const CHUNK_ATOMICSECT      = 0x9;
    const CHUNK_PLANESECT       = 0xA;
    const CHUNK_WORLD           = 0xB;
    const CHUNK_SPLINE          = 0xC;
    const CHUNK_MATRIX          = 0xD;
    const CHUNK_FRAMELIST       = 0xE;
    const CHUNK_GEOMETRY        = 0xF;
    const CHUNK_CLUMP           = 0x10;
    const CHUNK_LIGHT           = 0x12;
    const CHUNK_UNICODESTRING   = 0x13;
    const CHUNK_ATOMIC          = 0x14;
    const CHUNK_TEXTURENATIVE   = 0x15;
    const CHUNK_TEXDICTIONARY   = 0x16;
    const CHUNK_ANIMDATABASE    = 0x17;
    const CHUNK_IMAGE           = 0x18;
    const CHUNK_SKINANIMATION   = 0x19;
    const CHUNK_GEOMETRYLIST    = 0x1A;
    const CHUNK_ANIMANIMATION   = 0x1B;
    const CHUNK_HANIMANIMATION  = 0x1B;
    const CHUNK_TEAM            = 0x1C;
    const CHUNK_CROWD           = 0x1D;
    const CHUNK_RIGHTTORENDER   = 0x1F;
    const CHUNK_MTEFFECTNATIVE  = 0x20;
    const CHUNK_MTEFFECTDICT    = 0x21;
    const CHUNK_TEAMDICTIONARY  = 0x22;
    const CHUNK_PITEXDICTIONARY = 0x23;
    const CHUNK_TOC             = 0x24;
    const CHUNK_PRTSTDGLOBALDATA = 0x25;
    const CHUNK_ALTPIPE         = 0x26;
    const CHUNK_PIPEDS          = 0x27;
    const CHUNK_PATCHMESH       = 0x28;
    const CHUNK_CHUNKGROUPSTART = 0x29;
    const CHUNK_CHUNKGROUPEND   = 0x2A;
    const CHUNK_UVANIMDICT      = 0x2B;
    const CHUNK_COLLTREE        = 0x2C;
    const CHUNK_ENVIRONMENT     = 0x2D;
    const CHUNK_COREPLUGINIDMAX = 0x2E;

    const CHUNK_MORPH           = 0x105;
    const CHUNK_SKYMIPMAP       = 0x110;
    const CHUNK_SKIN            = 0x116;
    const CHUNK_PARTICLES       = 0x118;
    const CHUNK_HANIM           = 0x11E;
    const CHUNK_MATERIALEFFECTS = 0x120;
    const CHUNK_PDSPLG          = 0x131;
    const CHUNK_ADCPLG          = 0x134;
    const CHUNK_UVANIMPLG       = 0x135;
    const CHUNK_BINMESH         = 0x50E;
    const CHUNK_NATIVEDATA      = 0x510;
    const CHUNK_VERTEXFORMAT    = 0x510;

    const CHUNK_PIPELINESET      = 0x253F2F3;
    const CHUNK_SPECULARMAT      = 0x253F2F6;
    const CHUNK_2DFX             = 0x253F2F8;
    const CHUNK_NIGHTVERTEXCOLOR = 0x253F2F9;
    const CHUNK_COLLISIONMODEL   = 0x253F2FA;
    const CHUNK_REFLECTIONMAT    = 0x253F2FC;
    const CHUNK_MESHEXTENSION    = 0x253F2FD;
    const CHUNK_FRAME            = 0x253F2FE;

    const FLAGS_TRISTRIP   = 0x01;
    const FLAGS_POSITIONS  = 0x02;
    const FLAGS_TEXTURED   = 0x04;
    const FLAGS_PRELIT     = 0x08;
    const FLAGS_NORMALS    = 0x10;
    const FLAGS_LIGHT      = 0x20;
    const FLAGS_MODULATEMATERIALCOLOR  = 0x40;
    const FLAGS_TEXTURED2  = 0x80;

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

        if (MANHUNT.parser.renderwareTmp.hasNativeGeometry){
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

            let BoneCount = rwData.binary.consume(1, 'uint8');
            let UsedIDCount = rwData.binary.consume(1, 'uint8');
            let maxWeightsPerVertex = rwData.binary.consume(2, 'uint16');

            rwData.binary.seek(UsedIDCount);

            rwData.data.SkinPLG = {boneids: [], weights: [], inverseMatrix: []};


            for (let i = 0; i < MANHUNT.parser.renderwareTmp.vertexCount; i++) {
                rwData.data.SkinPLG.boneids.push([
                    rwData.binary.consume(1, 'uint8'),
                    rwData.binary.consume(1, 'uint8'),
                    rwData.binary.consume(1, 'uint8'),
                    rwData.binary.consume(1, 'uint8')
                ]);
            }

            for (let i = 0; i < MANHUNT.parser.renderwareTmp.vertexCount; i++) {

                rwData.data.SkinPLG.weights.push([
                    rwData.binary.consume(4, 'float32'),
                    rwData.binary.consume(4, 'float32'),
                    rwData.binary.consume(4, 'float32'),
                    rwData.binary.consume(4, 'float32')
                ]);
            }

            let Matrix_Array = [];
            for (let i = 0; i < BoneCount; i++) {
                Matrix_Array.push([
                    rwData.binary.consume(4, 'float32'), rwData.binary.consume(4, 'float32'), rwData.binary.consume(4, 'float32'), rwData.binary.consume(4, 'float32'),
                    rwData.binary.consume(4, 'float32'), rwData.binary.consume(4, 'float32'), rwData.binary.consume(4, 'float32'), rwData.binary.consume(4, 'float32'),
                    rwData.binary.consume(4, 'float32'), rwData.binary.consume(4, 'float32'), rwData.binary.consume(4, 'float32'), rwData.binary.consume(4, 'float32'),
                    rwData.binary.consume(4, 'float32'), rwData.binary.consume(4, 'float32'), rwData.binary.consume(4, 'float32'), rwData.binary.consume(4, 'float32')
                ]);
            }


            while(rwData.binary.remain() > 0){
                rwData.chunks.push(rwData.processChunk());
            }
            // binary.seek(12);

            rwData.data.SkinPLG.inverseMatrix = Matrix_Array;
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

        let FaceType = rwData.binary.consume(4, 'int32');
        let SplitCount = rwData.binary.consume(4, 'uint32');
        let numIndices = rwData.binary.consume(4, 'uint32'); //FaceCount

        let hasData = header.size > 12+SplitCount*8;

        rwData.data.faces = [];
        rwData.data.materialIds = [];
        for(let i = 0; i < SplitCount; i++){
            let SplitFaceCount = rwData.binary.consume(4, 'uint32'); //numIndices
            rwData.data.materialIds.push(rwData.binary.consume(4, 'uint32') + 1);

            if (hasData){

                for (let i = 0; i < SplitFaceCount; i++) {

                    if (MANHUNT.parser.renderwareTmp.hasNativeGeometry){
                        rwData.data.faces.push(rwData.binary.consume(2, 'uint16') + 1);
                    }else{
                        rwData.data.faces.push(rwData.binary.consume(4, 'uint32') + 1);
                    }

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
        let numUv = struct.binary.consume(1, 'int8'); //NumTexCoorsCustom  / numUVs
        MANHUNT.parser.renderwareTmp.hasNativeGeometry = struct.binary.consume(1, 'int8') !== 0; //GeometryNativeFlags

        let faceCount = struct.binary.consume(4, 'uint32');
        MANHUNT.parser.renderwareTmp.vertexCount = struct.binary.consume(4, 'uint32');
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

        if (!MANHUNT.parser.renderwareTmp.hasNativeGeometry){

            if ((formatFlags & rpGEOMETRYPRELIT) === rpGEOMETRYPRELIT){
            // if (formatFlags & FLAGS_PRELIT){
                for(let i = 0; i < MANHUNT.parser.renderwareTmp.vertexCount; i++){
                    rwData.data.VColor_Array.push(struct.binary.readColorRGBA());
                }
            }

            if ((formatFlags & rpGEOMETRYTEXTURED) === rpGEOMETRYTEXTURED || (formatFlags & rpGEOMETRYTEXTURED2) === rpGEOMETRYTEXTURED2){
            // if (formatFlags & FLAGS_TEXTURED){
                for(let i = 0; i < MANHUNT.parser.renderwareTmp.vertexCount; i++){
                    rwData.data.UV1_array.push([
                        struct.binary.consume(4, 'float32'),
                        struct.binary.consume(4, 'float32')
                    ]);
                }
            }

            if ((formatFlags & rpGEOMETRYTEXTURED2) === rpGEOMETRYTEXTURED2){
            // if (formatFlags & FLAGS_TEXTURED2){
                for(let i = 0; i < MANHUNT.parser.renderwareTmp.vertexCount; i++){
                    rwData.data.UV2_array.push([
                        struct.binary.consume(4, 'float32'),
                        struct.binary.consume(4, 'float32')
                    ]);
                }

                // for(let u = 0; u < numUv; u++){
                //     for(let i = 0; i < MANHUNT.parser.renderwareTmp.vertexCount; i++){
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

        let hasPosition = struct.binary.consume(4, 'int32');
        let hasNormal = struct.binary.consume(4, 'int32'); // need to recompute. Edit: hmmw why?
        hasNormals = (formatFlags & FLAGS_NORMALS) ? 1 : 0;

        if (!MANHUNT.parser.renderwareTmp.hasNativeGeometry){
            for (let i = 0; i < MANHUNT.parser.renderwareTmp.vertexCount; i++) {
                rwData.data.Vert_array.push(struct.binary.readVector3());
            }

            if (formatFlags & FLAGS_NORMALS){
                for (let i = 0; i < MANHUNT.parser.renderwareTmp.vertexCount; i++) {
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

        rwData.binary.consume(4, 'uint32'); // unknown
        let boneID = rwData.binary.consume(4, 'int32');
        let BoneCount = rwData.binary.consume(4, 'uint32');

        MANHUNT.parser.renderwareTmp.BoneIDArray.push(boneID);

        rwData.data.bones = [];
        if (BoneCount > 0) {

            rwData.binary.consume(4, 'int32'); //flags
            rwData.binary.consume(4, 'int32'); //keyFrameSize
            for (let i = 0; i < BoneCount; i++) {

                let animBone = {
                    BoneID: rwData.binary.consume(4, 'int32'),
                    BoneIndex: rwData.binary.consume(4, 'uint32'),
                    BoneType: rwData.binary.consume(4, 'uint32'),
                };
                rwData.data.bones.push(animBone);

                MANHUNT.parser.renderwareTmp.hAnimBoneArray.push(animBone);

            }

        }

        assert(rwData.binary.remain(), 0, 'CHUNK_HANIM: Unable to parse fully the data!');

        return rwData;
    };
//


    rwChunks[CHUNK_FRAME] = function (header, rwData) {
        rwData.data.name = rwData.binary.getString(0);
        return rwData;
    };

    rwChunks[CHUNK_FRAMELIST] = function (header, rwData) {

        rwData.type = CHUNK_FRAMELIST;
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

        let rw = MANHUNT.parser.renderware(data);
        rw.header = header;
        rw.type = header.id;
        rw.typeName = CHUNK_ID_NAME[header.id];

        return rwChunks[header.id](header, rw);
    }


    let convertResult = {
        boneArray: []
    };

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

    function convertToModel(rwData) {

        let tree = cleanTree(rwData, 0);
        assert(tree.type, CHUNK_CLUMP, "convert: Container is not a CHUNK_CLUMP it is " + tree.typeName);

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
                console.log(tree, i, frameCount, chunkFrameList.data.frameList);
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

            if (i > 0)
                bone.userProp.BoneID = MANHUNT.parser.renderwareTmp.BoneIDArray[i-1];

            bones.push(bone);

        }

        //Search Bones
        for(let i = 0; i < frameCount; i++){
            let bne = bones[i];
            let boneID = bne.userProp.BoneID;

            if (typeof boneID !== "undefined") {
                let hAnimBoneArray = MANHUNT.parser.renderwareTmp.hAnimBoneArray;
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


        MANHUNT.parser.renderwareTmp = {
            hasNativeGeometry: false,
            vertexCount: false,
            BoneIDArray: [],
            hAnimBoneArray: [],
        };

        return{
            parsedObjects: parsedObjects,
            BoneArray: {
                bones: bones,
                skinBones: skinBones
            }
        };

    }

    function cleanTree(rwData, deep) {
        let chunks = [];
        rwData.chunks.forEach(function (chunk) {
            let _chunk = cleanTree(chunk, deep + 1);
            if (_chunk.type === CHUNK_STRUCT  && _chunk.chunks.length === 0)
                return;
            // if (_chunk.type === CHUNK_EXTENSION  && _chunk.chunks.length === 0)
            //     return;
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

    return {

        data: {},
        chunks: [],
        binary: binary,
        convertToModel: convertToModel,
        processChunk: processChunk
    };
};
