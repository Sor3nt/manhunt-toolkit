

import Helper from './../../../Helper.js'
import Chunk from "./Chunk.js";
import Renderware from "./../Renderware.js";
const assert = Helper.assert;

export default class AtomicSect extends Chunk{

    result = {
        vertex: [],
        cpvArray: [],
        uvArray: [],
        uv2Array: [],
        faces: [],
        uvForFaces: [],
        normals: [],

        chunks: []
    };

    parse(){
        let struct = this.processChunk(this.binary);
        assert(struct.type, Renderware.CHUNK_STRUCT);

        if (struct.header.version === 784){

            let materialIdBase = struct.binary.consume( 4,'uint32');
            let triangleCount = struct.binary.consume( 4,'uint32');
            let vertexCount = struct.binary.consume( 4,'uint32');
            let bbox1 = struct.binary.consumeMulti(3, 4,'float32');
            let bbox2 = struct.binary.consumeMulti(3, 4,'float32');
            struct.binary.seek(2*4); //0, unused


            for(let i = 0; i < vertexCount; i++){
                let vec = struct.binary.readVector3();
                // let z = vec.z;
                // vec.z = vec.y * -1;
                // vec.y = z;
                this.result.vertex.push(vec);
            }

            if (this.rootData.worldFlags.rpWORLDNORMALS){
                for(let i = 0; i < vertexCount; i++){
                    this.result.normals.push(struct.binary.consumeMulti(3, 1,'int8'));
                    struct.binary.seek(1); //padding
                }
            }

            if (this.rootData.worldFlags.rpWORLDPRELIT){
                for(let i = 0; i < vertexCount; i++) {
                    // color.push(struct.binary.readColorRGBA());
                    this.result.cpvArray.push(struct.binary.consumeMulti(4, 1,'uint8'));
                }
            }

            let uv = [];
            let uv2 = [];
            let uv3 = [];
            if (this.rootData.worldFlags.rpWORLDTEXTURED){
                for(let i = 0; i < vertexCount; i++) {
                    this.result.uvArray.push(struct.binary.consumeMulti(2, 4, 'float32'));
                }
            }else if (this.rootData.worldFlags.rpWORLDTEXTURED2){

                for(let i = 0; i < vertexCount; i++) {
                    this.result.uvArray.push(struct.binary.consumeMulti(2, 4, 'float32'));
                }

                for(let i = 0; i < vertexCount; i++) {
                    this.result.uv2Array.push(struct.binary.consumeMulti(2, 4, 'float32'));
                }

            }



            //
            let face;
            let facesMat = [];
            for(let i = 0; i < triangleCount; i++) {

                let matId = struct.binary.consume(2, 'uint16');
                face = struct.binary.readFace3(2, 'uint16');
                face.materialIndex = matId;
                this.result.faces.push(face);


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

            while(this.binary.remain() > 0){
                this.result.chunks.push(this.processChunk(this.binary));
            }
            // console.log( color, uv, uv2,faces,facesMat);
            // die;
            //
            return;
        }


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