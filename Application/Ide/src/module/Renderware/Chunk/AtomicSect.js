

import Helper from './../../../Helper.js'
import Chunk from "./Chunk.js";
import Renderware from "./../Renderware.js";
const assert = Helper.assert;

export default class AtomicSect extends Chunk{

    result = {
        vertex: [],
        cpvArray: [],
        uvArray: [],
        faces: [],
        uvForFaces: [],

        chunks: []
    };

    parse(){
        let struct = this.processChunk(this.binary);
        assert(struct.type, Renderware.CHUNK_STRUCT);

        if (struct.header.size > 44){
            struct.binary.seek(4);
            let sectionFaceCount = struct.binary.consume(4, 'uint32');
            let sectionVertexCount = struct.binary.consume(4, 'uint32');

            struct.binary.seek(32);
            // this.result.vertex = struct.binary.consumeMulti(sectionVertexCount * 3, 4, 'float32');
            for(let i = 0; i < sectionVertexCount; i++){
                let vec = struct.binary.readVector3();
                // let z = vec.z;
                // vec.z = vec.y * -1;
                // vec.y = z;
                this.result.vertex.push(vec);
            }

            struct.binary.setCurrent(struct.binary.current() + (4*sectionVertexCount));
            for(let i = 0; i < sectionVertexCount; i++){
                this.result.cpvArray.push(struct.binary.readColorRGBA());
            }

            for(let i = 0; i < sectionVertexCount; i++){
                this.result.uvArray.push([
                    struct.binary.consume(4, 'float32'),
                    struct.binary.consume(4, 'float32')
                ]);
            }


            for(let i = 0; i < sectionFaceCount; i++){
                let face;
                if (this.header.version === 0x1803FFFF) {
                    face = struct.binary.readFace3(2, 'uint16');
                    face.materialIndex = struct.binary.consume(2, 'uint16');
                    this.result.faces.push(face);
                }else{
                    let matId = struct.binary.consume(2, 'uint16');
                    face = struct.binary.readFace3(2, 'uint16');
                    face.materialIndex = matId;
                    this.result.faces.push(face);
                }


                // face.vertexColors = [
                //     cpvArray[face.a],
                //     cpvArray[face.b],
                //     cpvArray[face.c]
                // ];

                //TODO: remove THREEE dependency
                this.result.uvForFaces[i] = [
                    new THREE.Vector2(
                        this.result.uvArray[face.a][0],
                        this.result.uvArray[face.a][1]
                    ),
                    new THREE.Vector2(
                        this.result.uvArray[face.b][0],
                        this.result.uvArray[face.b][1]
                    ),
                    new THREE.Vector2(
                        this.result.uvArray[face.c][0],
                        this.result.uvArray[face.c][1]
                    )
                ];
            }

            this.validateParsing(struct);

        }

        //extension
        if (this.binary.remain() > 0){
            while(this.binary.remain() > 0){
                this.result.chunks.push(this.processChunk(this.binary));
            }

        }

        this.validateParsing(this);
    }

}