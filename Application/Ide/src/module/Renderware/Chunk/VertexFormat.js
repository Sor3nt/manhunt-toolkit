
import Chunk from './Chunk.js'
import Renderware from "../Renderware.js";

/**
 * Thanks to https://github.com/qaisjp/green-candy/blob/807e79bac9296225ab3c162b713f2461c1542e46/ps2%20export%20tool/modded_vendor/rwtools-master/src/ps2native.cpp
 */
export default class VertexFormat extends Chunk{

    parse(){
        //OR CHUNK_NATIVEDATA (depends on rw version i guess)

        if (this.header.version === 469893221){
            let struct = this.processChunk(this.binary);
            assert(struct.type, Renderware.CHUNK_STRUCT);

            this.result.platform = struct.binary.consume(4, 'uint32');

            switch (this.result.platform) {


                case Renderware.PLATFORM_PS2:
                case Renderware.PLATFORM_PS2FOURCC:
                    // console.log("not implemented / buggy");
                    // debugger;

                    this.parsePs2(struct.binary);
                    break;

                default:
                    console.error("Platform not supported ", this.result.platform);
                    debugger;
                    break;
            }
        }



        this.validateParsing(this);
    }

    parsePs2( binary ){
        let size = binary.consume(4, 'uint32');
        let noPointers = binary.consume(4, 'uint32');

        console.log(this);
        die;
        if (noPointers === 0){
            console.log("pointers active... todo");
            debugger;
            return;
        }

        let data = binary.consume(size, 'nbinary');

        for(let i = 0; i < this.rootData.binMesh.splitCount; i++) {
            let numIndices = this.rootData.binMesh.splitFaceCount[i];
            console.log(binary.consume(4, 'float32'));
            console.log(this.rootData.binMesh);
            die;
        }

    }

    parsePs2Rw( binary ){

        this.index = 0;
        let numIndices = 0;

        for(let i = 0; i < this.rootData.binMesh.splitCount; i++){
            let splitSize = binary.consume(4, 'uint32');
            binary.seek(4);// bool: hasNoSectionAData

            numIndices = this.rootData.binMesh.splitFaceCount[i];
            let start = binary.current();
            let end = splitSize + binary.current();

            let reachedEnd;
            let sectionALast = false;
            let sectionBLast = false;
            let dataAread = false;

            //
            // let struct2 = this.processChunk(binary);
            // console.log(struct2);
            // consasdole.log(struct2);


            let tmpI = 0;
            while(binary.current() < end){
                reachedEnd = false;

                while (!reachedEnd && !sectionALast) {
                    let chunk8 = binary.consumeMulti(16, 1, 'uint8');
                    binary.seek(-16); // go back parse again
                    let chunk32 = binary.consumeMulti(4, 4, 'uint32');

                    tmpI++;
                    if (tmpI >= 1000){
                        die;
                    }
                    console.log(chunk8[3], binary.current());
                    // die;
                    // rw.read((char *) chunk8, 0x10);
                    switch (chunk8[3]) {
                        case 0x30: {
                            /* read all split data when we find the
                             * first data block and ignore all
                             * other blocks */
                            if (dataAread) {
                                /* skip dummy data */
                                binary.seek(16);
                                break;
                            }

                            let oldPos = binary.current();
                            let dataPos = start + chunk32[1]*0x10;

                            binary.setCurrent(dataPos);

                            this.readData(numIndices,chunk32[3], i, binary);
                            binary.setCurrent(oldPos + 0x10);
                            break;
                        }
                        case 0x60:
                            sectionALast = true;
                        /* fall through */
                        case 0x10:
                            reachedEnd = true;
                            dataAread = true;
                            break;
                        default:
                            break;
                    }
                }

            }

            /* sectionB */
            reachedEnd = false;
            while (!reachedEnd && !sectionBLast) {
                let chunk8 = binary.consumeMulti(16, 1, 'uint8');
                binary.seek(-16); // go back parse again
                let chunk32 = binary.consumeMulti(4, 4, 'uint32');


                // rw.read((char *) chunk8, 0x10);
                switch (chunk8[3]) {
                    case 0x00:
                    case 0x07:
                        this.readData(chunk8[14],chunk32[3], i, binary);
                        /* remember what sort of data we read */
                        // typesRead.push(chunk32[3]);
                        break;
                    case 0x04:
                        // if (chunk8[7] == 0x15)
                        //     ;//first
                        // else if (chunk8[7] == 0x17)
                        //     ;//not first

                        if ((chunk8[11] === 0x11 &&
                            chunk8[15] === 0x11) ||
                            (chunk8[11] === 0x11 &&
                                chunk8[15] === 0x06)) {
                            // last
                            binary.setCurrent(end);
                            // typesRead.clear();
                            sectionBLast = true;
                        } else if (chunk8[11] === 0 &&
                            chunk8[15] === 0 &&
                            this.rootData.binMesh.faceType === Renderware.FACETYPE_STRIP) {
                            // deleteOverlapping(typesRead, i);
                            // typesRead.clear();
                            // not last
                        }
                        reachedEnd = true;
                        break;
                    default:
                        break;
                }
            }

        }

console.log(this.rootData.binMesh.splitCount);
die;

    }

    readData(vertexCount, type, split, binary){


        // let vertexScale = (flags & FLAGS_PRELIT) ? VERTSCALE1 : VERTSCALE2;
        let vertexScale = Renderware.VERTSCALE1; //TODO
        let numUVs = 1; //TODO

        let vertices = [];
        let vertexColors = [];
        let nightColors = [];
        let normals = [];
        let vertexBoneIndices = [];
        let vertexBoneWeights = [];
        let uv = [[]];
        let size = 0;
        type &= 0xFF00FFFF;
        /* TODO: read greater chunks */
        switch (type) {
            /* Vertices */
            case 0x68008000: {
                size = 3 * 4;
                for (let j = 0; j < vertexCount; j++) {
                    vertices.push(binary.consume(4, 'float32'));
                    vertices.push(binary.consume(4, 'float32'));
                    vertices.push(binary.consume(4, 'float32'));
                    this.rootData.binMesh.faces.push(this.index++);
                }
                break;
            } case 0x6D008000: {
                size = 4 * 2;
                // int16 vertex[4];
                for (let j = 0; j < vertexCount; j++) {
                    let vertex = binary.consumeMulti(4, 2, 'int16');
                    // rw.read((char *) (vertex), size);
                    let flag = vertex[3] & 0xFFFF;
                    vertices.push(vertex[0] * vertexScale);
                    vertices.push(vertex[1] * vertexScale);
                    vertices.push(vertex[2] * vertexScale);
                    if (flag === 0x8000){
                        this.rootData.binMesh.faces.push(this.index - 1);
                        this.rootData.binMesh.faces.push(this.index - 1);
                    }
                    this.rootData.binMesh.faces.push(this.index++);
                }
                break;
                /* Texture coordinates */
            } case 0x64008001: {
                size = 2 * 4;
                for (let j = 0; j < vertexCount; j++) {
                    uv[0].push(binary.consume(4, 'float32'));
                    uv[0].push(binary.consume(4, 'float32'));
                }
                for (let i = 1; i < numUVs; i++) {
                    for (let j = 0; j < vertexCount; j++) {
                        uv[i].push(0);
                        uv[i].push(0);
                    }
                }
                break;
            } case 0x6D008001: {
                size = 2 * 2;

                for (let j = 0; j < vertexCount; j++) {
                    for (let i = 0; i < numUVs; i++) {
                        let _uv = binary.consumeMulti(2, 2, 'int16');
                        uv[i].push(_uv[0] * Renderware.UVSCALE);
                        uv[i].push(_uv[1] * Renderware.UVSCALE);
                    }
                }
                size *= numUVs;
                break;
            } case 0x65008001: {
                size = 2 * 2;
                for (let j = 0; j < vertexCount; j++) {
                    let _uv = binary.consumeMulti(2, 2, 'int16');
                    uv[0].push(_uv[0] * Renderware.UVSCALE);
                    uv[0].push(_uv[1] * Renderware.UVSCALE);
                }
                for (let i = 1; i < numUVs; i++) {
                    for (let j = 0; j < vertexCount; j++) {
                        uv[i].push(0);
                        uv[i].push(0);
                    }
                }
                break;
                /* Vertex colors */
            } case 0x6D00C002: {
                size = 8;
                
                for (let j = 0; j < vertexCount; j++) {
                    vertexColors.push(binary.consume(1, 'uint8'));
                    nightColors.push(binary.consume(1, 'uint8'));
                    vertexColors.push(binary.consume(1, 'uint8'));
                    nightColors.push(binary.consume(1, 'uint8'));
                    vertexColors.push(binary.consume(1, 'uint8'));
                    nightColors.push(binary.consume(1, 'uint8'));
                    vertexColors.push(binary.consume(1, 'uint8'));
                    nightColors.push(binary.consume(1, 'uint8'));
                }
                break;
            } case 0x6E00C002: {
                size = 4;
                for (let j = 0; j < vertexCount; j++) {
                    vertexColors.push(binary.consume(1, 'uint8'));
                    vertexColors.push(binary.consume(1, 'uint8'));
                    vertexColors.push(binary.consume(1, 'uint8'));
                    vertexColors.push(binary.consume(1, 'uint8'));
                }
                break;
                /* Normals */
            } case 0x6E008002: case 0x6E008003: {
                size = 4;
                // int8 normal[4];
                for (let j = 0; j < vertexCount; j++) {
                    // rw.read((char *) (normal), size);
                    let normal = binary.consumeMulti(4, 1, 'int8');

                    normals.push(normal[0] * Renderware.NORMALSCALE);
                    normals.push(normal[1] * Renderware.NORMALSCALE);
                    normals.push(normal[2] * Renderware.NORMALSCALE);
                }
                break;
            } case 0x6A008003: {
                size = 3;
                // int8 normal[3];
                for (let j = 0; j < vertexCount; j++) {
                    let normal = binary.consumeMulti(3, 1, 'int8');
                    normals.push(normal[0] * Renderware.NORMALSCALE);
                    normals.push(normal[1] * Renderware.NORMALSCALE);
                    normals.push(normal[2] * Renderware.NORMALSCALE);
                }
                break;
                /* Skin weights and indices */
            } case 0x6C008004: case 0x6C008003: case 0x6C008001: {
                size = 4 * 4;


                // uint8 indices[4];;
                let indices = [];
                for (let j = 0; j < vertexCount; j++) {
                    let weight = binary.consumeMulti(3, 4, 'float32');

                    for (let i = 0; i < 4; i++) {
                        vertexBoneWeights.push(weight[i]);
                        indices[i] = weight[i] >> 2;
                        if (indices[i] !== 0)
                            indices[i] -= 1;
                    }
                    vertexBoneIndices.push(indices[3] << 24 |
                        indices[2] << 16 |
                        indices[1] << 8 |
                        indices[0]);
                }
                break;
            }
            default:
                console.log("unknown data type");
                debugger;
                break;
        }

        /* skip padding */
        if (vertexCount*size & 0xF)
            binary.seek(0x10 - (vertexCount*size & 0xF));


        console.log("vertices", vertices);
    }

}