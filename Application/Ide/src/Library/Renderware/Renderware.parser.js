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


    rwChunks[CHUNK_CHUNKGROUPSTART] = function (header, rwData) {
        return rwData;
    };

    rwChunks[CHUNK_IMAGE] = function (header, rwData) {

        let struct = rwData.processChunk();
        assert(struct.type, CHUNK_STRUCT);

        rwData.data.head = struct.binary.consume(struct.header.size);
        assert(struct.binary.remain(), 0, 'CHUNK_IMAGE struct: Unable to parse fully the data! Remain ' + struct.binary.remain());


        //rwData.binary = image datta


        return rwData;

    };

    rwChunks[CHUNK_PITEXDICTIONARY] = function (header, rwData) {

        rwData.data.unkInt16 = [
            rwData.binary.consume(2, 'uint16'),
            rwData.binary.consume(2, 'uint16'),
        ];

        rwData.data.count = rwData.binary.consume(4, 'uint32');


        let image = rwData.processChunk();
        assert(image.type, CHUNK_IMAGE);

        return rwData;
    };


    //sound related
    rwChunks[2050] = function (header, rwData) {

        while(rwData.binary.remain() > 0){
            let chunk = rwData.processChunk();
            //contains chunk_2051 or chunk_2052
            rwData.chunks.push(chunk);
        }

        return rwData;
    };

    //sound related
    rwChunks[2051] = function (header, rwData) {

        rwData.data.unknown = rwData.binary.consume(header.size, 'nbinary');

        assert(rwData.binary.remain(), 0, '2051 struct: Unable to parse fully the data! Remain ' + rwData.binary.remain());

        return rwData;
    };

    //sound related
    rwChunks[2052] = function (header, rwData) {

        rwData.data.unknown = rwData.binary.consume(header.size, 'nbinary');

        assert(rwData.binary.remain(), 0, '2051 struct: Unable to parse fully the data! Remain ' + rwData.binary.remain());

        return rwData;
    };

    //sound related
    rwChunks[2057] = function (header, rwData) {

        let unknownChunk = rwData.processChunk();
        assert(unknownChunk.type, 2058);

        while(rwData.binary.remain() > 0){
            let chunk = rwData.processChunk();
            rwData.chunks.push(chunk);
        }

        return rwData;
    };

    //sound related
    rwChunks[2058] = function (header, rwData) {

        rwData.binary.seek(52);

        rwData.data.name = rwData.binary.consume(16, 'nbinary').getString(0);

        assert(rwData.binary.remain(), 0, '2058 struct: Unable to parse fully the data! Remain ' + rwData.binary.remain());

        return rwData;
    };


    //sound related
    rwChunks[2060] = function (header, rwData) {

        let count = rwData.binary.consume(4, 'uint32');

        for(let i = 0;i < count; i++){
            let unknown2050 = rwData.processChunk();
            assert(unknown2050.type, 2050);
            rwData.chunks.push(unknown2050);
        }

        assert(rwData.binary.remain(), 0, '2060 struct: Unable to parse fully the data! Remain ' + rwData.binary.remain());

        return rwData;
    };

    rwChunks[CHUNK_HANIMANIMATION] = function (header, rwData) {
        //code based on .version === 469893165
        assert(header.version, 469893165, "Code is only tested on version 469893165!");

        let unknown = rwData.binary.consume(4, 'uint32');

        //Size, in bytes, of the interpolated keyframe structure.
        let keyFrameSize = rwData.binary.consume(4, 'uint32');
        assert(keyFrameSize, 2, "KeyFrame size is not 2 ! Todo");

        //Number of keyframes in the animation
        let numFrames = rwData.binary.consume(4, 'uint32');

        //Specifies details about animation - relative translation modes etc.
        let flags = rwData.binary.consume(4, 'uint32');

        //Duration of animation in seconds
        rwData.data.duration = rwData.binary.consume(4, 'float32');

        //Pointer to the animation keyframes
        let keyframes = rwData.binary.consume(4, 'int32');

        //Pointer to custom data for this animation
        let customData = rwData.binary.consume(4, 'int32');

        rwData.binary.seek(3*4);
        rwData.binary.seek(2);

        let frames = {};
        let target = 0;
        for(let i = 0; i < numFrames - 1; i++){
            let entry = {
                boneId: target,
                time : rwData.binary.consume(4, 'float32'),
                matrix: [
                    rwData.binary.consume(2, 'uint16') / 2048 / 30,
                    rwData.binary.consume(2, 'uint16') / 2048 / 30,
                    rwData.binary.consume(2, 'uint16') / 2048 / 30,
                    rwData.binary.consume(2, 'uint16') / 2048 / 30,
                    rwData.binary.consume(2, 'uint16') / 2048 / 30,
                    rwData.binary.consume(2, 'uint16') / 2048 / 30,
                    rwData.binary.consume(2, 'uint16') / 2048 / 30,
                    rwData.binary.consume(2, 'uint16') / 2048 / 30,
                    rwData.binary.consume(2, 'uint16') / 2048 / 30,
                ]
            };

            if (typeof frames[target] === "undefined" )
                frames[target] = [];

            frames[target].push(entry);

            if (target % 36 === 0 && target !== 0)
                target = 0;
            else
                target++;

        }
        rwData.data.frames = frames;
        rwData.data.matrix = rwData.binary.readFloats(6);

        assert(rwData.binary.remain(), 0, 'CHUNK_HANIMANIMATION struct: Unable to parse fully the data! Remain ' + rwData.binary.remain());

        return rwData;
    };

    rwChunks[298] = function (header, rwData) {
        return rwData;
    };

    rwChunks[CHUNK_AUDIOCONTAINER] = function (header, rwData) {

        let audioHeader = rwData.processChunk();
        assert(audioHeader.type, CHUNK_AUDIOHEADER);
        rwData.data.audioHeader = audioHeader.data;

        let audioData = rwData.processChunk();
        assert(audioData.type, CHUNK_AUDIODATA);
        rwData.data.audioData = audioData.binary;

        assert(rwData.binary.remain(), 0, '264: Unable to parse fully the data!');

        return rwData;
    };

    rwChunks[264] = function (header, rwData) {

        let struct = rwData.processChunk();
        assert(struct.type, CHUNK_TOC);

        rwData.data.flag = rwData.binary.consume(1, 'uint8');

        assert(rwData.binary.remain(), 0, '264: Unable to parse fully the data!');
        return rwData;
    };

    rwChunks[CHUNK_TOC] = function (header, rwData) {



        rwData.data.unknown = rwData.binary.consume(header.size, 'nbinary');

        assert(rwData.binary.remain(), 0, 'CHUNK_TOC struct: Unable to parse fully the data! Remain ' + rwData.binary.remain());

        return rwData;
    };

    rwChunks[CHUNK_AUDIOHEADER] = function (header, rwData) {

        let headerSize = rwData.binary.consume(4, 'uint32');
        rwData.binary.seek(28); //unkown
        let segmentCount = rwData.binary.consume(4, 'uint32');
        rwData.binary.seek(4); //unkown
        let numberOfTracks = rwData.binary.consume(4, 'uint32');
        rwData.binary.seek(20); //unkown
        rwData.binary.seek(16); //unkown

        let name = rwData.binary.consume(16, 'nbinary').getString(0);
        console.log(segmentCount,  numberOfTracks, name);

        // assert(rwData.binary.remain(), 0, 'CHUNK_AUDIOHEADER: Unable to parse fully the data! Remain ' + rwData.binary.remain());

        //unknown data block
        return rwData;
    };

    rwChunks[524] = function (header, rwData) {
        //unknown data block
        return rwData;
    };

    rwChunks[CHUNK_AUDIODATA] = function (header, rwData) {

        //todo, huge data block left
        return rwData;
    };

    rwChunks[CHUNK_ATOMICSECT] = function (header, rwData) {

        let struct = rwData.processChunk();
        assert(struct.type, CHUNK_STRUCT);

        let structChunk = struct;//.processChunk();

        if (struct.header.size > 44){
            structChunk.binary.seek(4);
            var sectionFaceCount = structChunk.binary.consume(4, 'uint32');
            var sectionVertexCount = structChunk.binary.consume(4, 'uint32');

            rwData.data.vertex = [];
            structChunk.binary.seek(32);
            for(i = 0; i < sectionVertexCount; i++){
                var vec = structChunk.binary.readVector3();
                // var z = vec.z;
                // vec.z = vec.y * -1;
                // vec.y = z;
                rwData.data.vertex.push(vec);
            }

            rwData.data.cpvArray = [];
            structChunk.binary.setCurrent(structChunk.binary.current() + (4*sectionVertexCount));
            for(i = 0; i < sectionVertexCount; i++){
                rwData.data.cpvArray.push(structChunk.binary.readColorRGBA());
            }

            rwData.data.uvArray = [];
            for(i = 0; i < sectionVertexCount; i++){
                rwData.data.uvArray.push([
                    structChunk.binary.consume(4, 'float32'),
                    structChunk.binary.consume(4, 'float32')
                ]);
            }

            rwData.data.faces = [];
            rwData.data.uvForFaces = [];
            for(i = 0; i < sectionFaceCount; i++){
                var face;
                if (header.version === 0x1803FFFF) {
                    face = structChunk.binary.readFace3(2, 'uint16');
                    face.materialIndex = structChunk.binary.consume(2, 'uint16');
                    rwData.data.faces.push(face);
                }else{
                    var matId = structChunk.binary.consume(2, 'uint16');
                    face = structChunk.binary.readFace3(2, 'uint16');
                    face.materialIndex = matId;
                    rwData.data.faces.push(face);
                }

                // face.vertexColors = [
                //     cpvArray[face.a],
                //     cpvArray[face.b],
                //     cpvArray[face.c]
                // ];

                rwData.data.uvForFaces[i] = [
                    new THREE.Vector2(
                        rwData.data.uvArray[face.a][0],
                        rwData.data.uvArray[face.a][1]
                    ),
                    new THREE.Vector2(
                        rwData.data.uvArray[face.b][0],
                        rwData.data.uvArray[face.b][1]
                    ),
                    new THREE.Vector2(
                        rwData.data.uvArray[face.c][0],
                        rwData.data.uvArray[face.c][1]
                    )
                ];
            }


        }

        return rwData;
    };

    rwChunks[CHUNK_PLANESECT] = function (header, rwData) {

        let struct = rwData.processChunk();
        assert(struct.type, CHUNK_STRUCT);

        while(rwData.binary.remain() > 0){
            let chunk = rwData.processChunk();
            rwData.chunks.push(chunk);
        }
        return rwData;
    };

    rwChunks[CHUNK_WORLD] = function (header, rwData) {

        let struct = rwData.processChunk();
        assert(struct.type, CHUNK_STRUCT);

        struct.binary.seek(4 * 4);
        rwData.data.faceCount = struct.binary.consume(4, 'uint32');
        rwData.data.vertexCount = struct.binary.consume(4, 'uint32');
        struct.binary.seek(4);
        rwData.data.sectors = struct.binary.consume(4, 'uint32');

        struct.binary.seek(32);

        assert(struct.binary.remain(), 0, 'CHUNK_WORLD struct: Unable to parse fully the data! Remain ' + struct.binary.remain());

        while(rwData.binary.remain() > 0){
            rwData.chunks.push(rwData.processChunk());
        }

        assert(rwData.binary.remain(), 0, 'CHUNK_WORLD: Unable to parse fully the data!');

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

        if (typeof rootChunk.data.BoneIDArray === "undefined")
            rootChunk.data.BoneIDArray = [];

        rootChunk.data.BoneIDArray.push(boneID);

        rwData.data.bones = [];
        if (boneCount > 0) {

            rwData.binary.seek(4); //flags
            rwData.binary.seek(4); //keyFrameSize


            if (typeof rootChunk.data.hAnimBoneArray === "undefined")
                rootChunk.data.hAnimBoneArray = [];

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
            rootChunk.data = {};
        }

        return rwChunks[header.id](header, rw);
    }


    function cleanTree(rwData, status) {
        let chunks = [];
        rwData.chunks.forEach(function (chunk) {
            let _chunk = cleanTree(chunk, status);

            let doBreak = false;
            [CHUNK_STRUCT, CHUNK_NAOBJECT, CHUNK_RIGHTTORENDER, CHUNK_ATOMICSECT, CHUNK_PLANESECT].forEach(function (type) {
                if (_chunk.type === type  && _chunk.chunks.length === 0  && JSON.stringify(_chunk.data) === JSON.stringify({}) ){
                    status.removed = 1;
                    doBreak = true;
                }
            });

            if (doBreak) return;

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
        let chunks = [];

        rootChunk = {};
        let chunk = processChunk();

        for(var i in rootChunk.data){
            if (!rootChunk.data.hasOwnProperty(i)) continue;
            chunk.data[i] = rootChunk.data[i];
        }

        var status = { removed: 0};
        do{
            chunk = cleanTree(chunk, status);
            _status = status.removed;
            status.removed = 0;
        }while(_status > 0);

        chunks.push(chunk);

        if (chunks.length === 1) return chunks[0];
        return chunks;
    }

    return {
        data: {},
        chunks: [],
        binary: binary,
        processChunk: processChunk,
        parse: parse
    };
};
