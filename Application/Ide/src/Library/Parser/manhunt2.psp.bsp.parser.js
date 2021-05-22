
MANHUNT.parser.manhun2PspBsp = function (binary, level) {


    function parseHeader() {
        binary.seek(4); // skip WRLD / DLRW
        let header = {
            version: binary.consume(4, 'uint32'),
            fileSize: binary.consume(4, 'uint32'),
            tableOffset: binary.consume(4, 'uint32'),
            tableOffset2: binary.consume(4, 'uint32'),
            numTable: binary.consume(4, 'uint32')      //main table number of offsets
        };

        binary.seek(6*4); // skip 6 zeros

        return header;
    }

    function parseWorldSectorInfo(){
        return {
            ident: binary.consume(4, 'uint32'),
            nextWorldSectorInfoPtr: binary.consume(4, 'uint32'),
            worldSectorPtr: binary.consume(4, 'uint32'),

            worldSectorID: binary.consume(2, 'uint16'),
            numAdjacentSectors: binary.consume(2, 'uint16'),

            adjacentSectorIndicesPtr: binary.consume(4, 'uint32'),
            adjacentSectorPortalsPtr: binary.consume(4, 'uint32'),
        };
    }

    function parseSectorTree(){
        let tree = {};
        tree.flag = binary.consume(4, 'int32');

        if (tree.flag !== -1){
            tree.leftSectorTreePtr = binary.consume(4, 'int32');
            tree.rightSectorTreePtr = binary.consume(4, 'int32');
            tree.position = binary.readVector3();
        }

        return tree;
    }

    function parseWorldSectorGeometry(){
        binary.seek(4); //padding

        let result = {
            numFaces: binary.consume(4, 'uint32'),
            numVerts: binary.consume(4, 'uint32'),
            boundingBox: [
                binary.readVector3(4, 'float32', true), //MIN
                binary.readVector3(4, 'float32', true) //MAX
            ],
            vertexOffset: binary.consume(4, 'uint32'),
        };
        binary.seek(4); //padding

        result.colorOffset = binary.consume(4, 'uint32');
        binary.seek(4); //padding

        result.facesOffset = binary.consume(4, 'int32');
        result.facesGroupsOffset = binary.consume(4, 'int32');
        result.geometryOffset = binary.consume(4, 'int32');
        result.parentSectorIndex = binary.consume(2, 'uint16');

        if (level._platform === "psp" || level._platform === "psp001"){
            binary.seek(4 * 2); //unknown 4x uin16
        }

        return result;
    }

    function parseVertex(vertexElementType) {

        let uVFormat        = vertexElementType & 3;
        let colorFormat     = (vertexElementType >> 2) & 7 ;
        let normalFormat    = (vertexElementType >> 5) & 3;
        let positionFormat  = (vertexElementType >> 7) & 3;
        let weightFormat    = (vertexElementType >> 9) & 3;
        // let indexFormat     = (vertexElementType >> 11) & 3;
        let numWeights      = ((vertexElementType >> 14) & 7) + 1;
        // let numVertices     = ((vertexElementType >> 18) & 7) + 1;
        // let coordType       = (vertexElementType >> 23) & 1;//1 -Transformed Coordinates . 0-Raw Coordinates.

        let result = {
            formats: {
                uVFormat: uVFormat,
                colorFormat: colorFormat,
                normalFormat: normalFormat,
                positionFormat: positionFormat,
                weightFormat: weightFormat
            }
        };
        switch(weightFormat){
            case 0: break;
            case 1: result.weight = binary.consumeMulti(numWeights, 1, 'uint8');break;
            case 2: result.weight = binary.consumeMulti(numWeights, 2, 'uint16');break;
            case 3: result.weight = binary.consumeMulti(numWeights, 4, 'float32');break;
        }

        switch(uVFormat){
            case 0: break;
            case 1: result.uv = binary.consumeMulti(2, 1, 'uint8');break;
            case 2: result.uv = binary.consumeMulti(2, 2, 'uint16');break;
            case 3: result.uv = binary.consumeMulti(2, 4, 'float32');break;
        }
        switch(colorFormat){
            case 0: break;
            case 1: break;
            case 2: break;
            case 3: break;
            case 4: result.color = binary.consume(32, 'uint16'); break;//BGR5650
            case 5: result.color = binary.consumeMulti(2, 2, 'uint16'); break;//ABGR5551
            case 6: result.color = binary.consumeMulti(2, 2, 'uint16'); break;//ABGR4444
            case 7: result.color = binary.consumeMulti(2, 2, 'uint16'); break;//ABGR8888
        }
        //NORMAL XYZ_PAD
        switch(normalFormat){
            case 0: break;
            case 1: result.normal = binary.consumeMulti(4, 1, 'uint8');break;
            case 2: result.normal = binary.consumeMulti(4, 2, 'uint16');break;
            case 3: result.normal = binary.consumeMulti(4, 4, 'float32');break;
        }
        //Position XYZ
        switch(positionFormat){
            case 0: break;
            case 1: result.position = binary.consumeMulti(3, 1, 'uint8');break;
            case 2: result.position = binary.consumeMulti(3, 2, 'uint16');break;
            case 3: result.position = binary.consumeMulti(3, 4, 'float');break;
        }

        return result;
    }


    function parseGeometry() {
        let result = {
            size: binary.consume(4, 'int32'),
            vertexElementType: binary.consume(4, 'int32'),//0x135 0x13D 0x1C335 0x1C33D
            numMaterialID: binary.consume(4, 'int32'),
            zero: binary.consume(4, 'int32'),
            boundingSphereXYZ: binary.readVector3(4, 'float32'),
            boundingSphereRadius: binary.consume(4, 'float32'),
            scaleFactorXYZ: binary.readVector3(4, 'float32'),
            numVerts: binary.consume(4, 'int32'), // (in tri-strip numfaces = numVerts-2)
            translateFactorXYZ: binary.readVector3(4, 'float32'),
            zero2: binary.consume(4, 'int32'),
            chunkHeaderSize: binary.consume(4, 'int32')
        };

        binary.seek(3 * 4); //zeros

        result.materialSplit = [];
        for (let i = 0; i < result.numMaterialID; i++){
            result.materialSplit.push({
                boundingBoxMinXYZ: binary.readVector3(2, 'int16'),// need / 32768.0 get float
                u1: binary.consume(2, 'int16'),//always is 1
                boundingBoxMaxXYZ: binary.readVector3(2, 'int16'),// need / 32768.0 get float
                u2: binary.consume(2, 'int16'),//always is 1
                materialIDNumVerts: binary.consume(2, 'int16'),//(MaterialIDNumFace = MaterialIDNumVerts-2)
                materialID: binary.consume(2, 'int16'),
                u3: binary.consume(4, 'float32'),
                boneId: [
                    binary.consume(1, 'uint8'), binary.consume(1, 'uint8'),
                    binary.consume(1, 'uint8'), binary.consume(1, 'uint8'),
                    binary.consume(1, 'uint8'), binary.consume(1, 'uint8'),
                    binary.consume(1, 'uint8'), binary.consume(1, 'uint8'),
                ]
            })
        }

        result.vertices = [];
        for(let i = 0; i < result.numVerts; i++){
            result.vertices.push(
                parseVertex(result.vertexElementType)
            );

        }
        console.log(result);

        return result;
    }

    function parseFaceGroup(startPtr) {
        let start = binary.current();
        binary.seek(12);
        let coordToCompare = binary.consume(4, 'int32');
        binary.setCurrent(start);

        let result = {};
        if (coordToCompare === -1){

            result.group = {
                coordMin: binary.consumeMulti(3, 4, 'float32'),
                coordToCompare: binary.consume(4, 'int32'),
                coordMax: binary.consumeMulti(3, 4, 'float32'),
                faceStartIndex: binary.consume(4, 'int32'),
                numFaces: binary.consume(4, 'int32'),
            }

        }else{
            let jumpToIndexIfTrue = binary.consume(4, 'int32');
            let jumpToIndexIfFalse = binary.consume(4, 'int32');
            let coordMin = binary.consume(4, 'float32');
            let coordMax = binary.consume(4, 'float32');

            binary.setCurrent(startPtr + jumpToIndexIfTrue * 16);
            result.ifTrue = parseFaceGroup(startPtr);

            binary.setCurrent(startPtr + jumpToIndexIfFalse * 16);
            result.ifFalse = parseFaceGroup(startPtr);

        }

        return result;
    }

    function parseMaterial() {
        let nameOffset = binary.consume(4, 'int32');

        let result = {
            color1: binary.consumeMulti(4, 1, 'uint8'),
            color2: binary.consumeMulti(4, 1, 'uint8'),
        };

        let cur = binary.current();
        binary.setCurrent(nameOffset);
        result.textureName = binary.getString(0, false);

        binary.setCurrent(cur);

        return result;
    }

    function parseWorldSelector(){
        let sectorTree = parseSectorTree();

        let result = {};

        if (sectorTree.flag !== -1){
            binary.setCurrent(sectorTree.leftSectorTreePtr);
            result.leftSelector = parseWorldSelector();
            binary.setCurrent(sectorTree.rightSectorTreePtr);
            result.rightSelector = parseWorldSelector();
        }else{
            let geometry = parseWorldSectorGeometry();

            if (geometry.vertexOffset > 0){
                binary.setCurrent(geometry.vertexOffset);

                result.colPrelight = [];
                result.colVerts = [];
                for (i = 0; i < geometry.numVerts; i++) {
                    let vec3 = binary.readVector3(4, 'float32', true);
                    result.colVerts.push(vec3);
                }

                if (geometry.colorOffset > 0) {
                    binary.setCurrent(geometry.colorOffset);

                    for (i = 0; i < geometry.numVerts; i++) {
                        result.colPrelight.push(binary.readColorRGBA());
                    }
                }
            }

            if (geometry.facesOffset > 0){
                binary.setCurrent(geometry.facesOffset);

                result.colFaces = [];
                for (i = 0; i < geometry.numFaces; i++) {
                    var face3 = binary.readFace3(2, 'uint16');

                    if (geometry.colorOffset > 0) {
                        face3.vertexColors = [
                            result.colPrelight[face3.a],
                            result.colPrelight[face3.b],
                            result.colPrelight[face3.c]
                        ];
                    }

                    result.colFaces.push(face3);
                    result.materialForFace.push(
                        binary.consume(2, 'uint16')
                    );
                }
            }

            if (geometry.geometryOffset > 0){
                binary.setCurrent(geometry.geometryOffset);
                result.geometry = parseGeometry();
            }

            if (geometry.facesGroupsOffset > 0){
                binary.setCurrent(geometry.facesGroupsOffset);
                result.faceGroup = parseFaceGroup(geometry.facesGroupsOffset);
            }

        }

        return result;
    }

    let result = {
        selectors: [],
    };

    let header = parseHeader();

    result.sceneBBox = [
        binary.readVector3(4, 'float32', true), //MIN
        binary.readVector3(4, 'float32', true) //MAX
    ];

    let materialsOffset = binary.consume(4, 'int32');
    let materialCount = binary.consume(4, 'int32');
    let firstWorldSectorInfoPtr = binary.consume(4, 'int32');
    // 20 byte unknown

    binary.setCurrent(firstWorldSectorInfoPtr);
    let worldSectorInfo;
    do {

        worldSectorInfo = parseWorldSectorInfo();

        // binary.setCurrent(worldSectorInfo.adjacentSectorIndicesPtr);
        binary.setCurrent(worldSectorInfo.worldSectorPtr);
        let selector = parseWorldSelector();
        result.selectors.push( selector );

        if (worldSectorInfo.nextWorldSectorInfoPtr > 0)
            binary.setCurrent(worldSectorInfo.nextWorldSectorInfoPtr);

    }while(worldSectorInfo.nextWorldSectorInfoPtr > 0);

    binary.setCurrent(materialsOffset);
    result.material = [];
    for (let i = 0; i < materialCount; i++) {
        result.material.push(parseMaterial());
    }

    return result;

};