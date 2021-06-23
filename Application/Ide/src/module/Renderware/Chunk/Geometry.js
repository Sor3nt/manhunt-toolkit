import Helper from './../../../Helper.js'
import Chunk from "./Chunk.js";
const assert = Helper.assert;
import Renderware from "./../Renderware.js";

export default class Geometry extends Chunk{

    result = {
        vColor: [],
        uv1: [],
        uv2: [],
        vert: [],
        normal: [],
        numMorphTargets: null,
        boundingSphere: {
            position: null,
            radius: null
        },
        faceMat: {
            face: [],
            matId: [],
        },
        light:{
            ambient: null,
            specular: null,
            diffuse: null
        },

        chunks: []
    };

    parse(){

        switch(Renderware.getVersion(this.header.version)){
            case 225282:
                this.parseVersion225282();
                break;
            default:
                this.parseVersionRegular();
                break;
        }


        this.rootData.geometries.push(this.result);


    }

    parseVersion225282(){

        let struct = this.processChunk(this.binary);
        assert(struct.type, Renderware.CHUNK_STRUCT);

        let unk1 = struct.binary.consume(2, 'uint16');  //0x37 - 55 (fixed)
        let unk2 = struct.binary.consume(2, 'uint16');  //0x01 - 01 (fixed)

        let faceCount = struct.binary.consume(4, 'uint32');
        this.rootData.vertexCount = struct.binary.consume(4, 'uint32');

        //1
        let flag = struct.binary.consume(4, 'uint32');

        for(let i = 0; i < this.rootData.vertexCount; i++){
            this.result.uv1.push([
                struct.binary.consume(4, 'float32'),
                struct.binary.consume(4, 'float32')
            ]);
        }

        for (let i = 0; i < faceCount; i++) {

            let f2 = struct.binary.consume(2, 'uint16');
            let f1 = struct.binary.consume(2, 'uint16');
            let matId = struct.binary.consume(2, 'uint16');
            let f3 = struct.binary.consume(2, 'uint16');

            this.result.faceMat.face.push([f1, f2, f3]);
            this.result.faceMat.matId.push(matId);
        }


        let unk3 = struct.binary.consume(2, 'uint16');  //0
        let unk4 = struct.binary.consume(2, 'uint16');  //14160 or 0

        this.result.boundingSphere.position = struct.binary.consumeMulti(3, 4, 'float32');

        let unk5 = struct.binary.consume(4, 'uint32');  //1
        let unk6 = struct.binary.consume(4, 'uint32');  //1

        for (let i = 0; i < this.rootData.vertexCount; i++) {
            this.result.vert.push(struct.binary.consumeMulti(3, 4, 'float32'));
        }

        for (let i = 0; i < this.rootData.vertexCount; i++) {
            this.result.normal.push(struct.binary.consumeMulti(3, 4, 'float32'));
        }

        this.validateParsing(struct);


        let matList = this.processChunk(this.binary);
        assert(matList.type, Renderware.CHUNK_MATLIST);

        let extension = this.processChunk(this.binary);
        assert(extension.type, Renderware.CHUNK_EXTENSION);

        while(extension.binary.remain() > 0){
            extension.result.chunks.push( this.processChunk(extension.binary) );
        }

        this.validateParsing(this);
    }


    parseVersionRegular(){

        let struct = this.processChunk(this.binary);
        assert(struct.type, Renderware.CHUNK_STRUCT);

        let formatFlags = struct.binary.consume(2, 'uint16'); // flags
        this.rootData.formatFlag = formatFlags;

        struct.binary.seek(1); //NumTexCoorsCustom  / numUVs
        this.rootData.hasNativeGeometry = struct.binary.consume(1, 'int8') !== 0; //GeometryNativeFlags

        let faceCount = struct.binary.consume(4, 'uint32');
        this.rootData.vertexCount = struct.binary.consume(4, 'uint32');
        this.result.numMorphTargets = struct.binary.consume(4, 'uint32'); //numMorphTargets


        //light info
        if (this.header.version < 0x34000) {
            this.result.light.ambient = struct.binary.consume(4, 'float32');
            this.result.light.specular = struct.binary.consume(4, 'float32');
            this.result.light.diffuse = struct.binary.consume(4, 'float32');
        }

        if (!this.rootData.hasNativeGeometry){

            if ((formatFlags & Renderware.rpGEOMETRYPRELIT) === Renderware.rpGEOMETRYPRELIT){
                // if (formatFlags & FLAGS_PRELIT){
                for(let i = 0; i < this.rootData.vertexCount; i++){
                    this.result.vColor.push(struct.binary.readColorRGBA());
                }
            }

            if ((formatFlags & Renderware.rpGEOMETRYTEXTURED) === Renderware.rpGEOMETRYTEXTURED || (formatFlags & Renderware.rpGEOMETRYTEXTURED2) === Renderware.rpGEOMETRYTEXTURED2){
                // if (formatFlags & FLAGS_TEXTURED){
                for(let i = 0; i < this.rootData.vertexCount; i++){
                    this.result.uv1.push([
                        struct.binary.consume(4, 'float32'),
                        struct.binary.consume(4, 'float32')
                    ]);
                }
            }

            if ((formatFlags & Renderware.rpGEOMETRYTEXTURED2) === Renderware.rpGEOMETRYTEXTURED2){
                // if (formatFlags & FLAGS_TEXTURED2){
                for(let i = 0; i < this.rootData.vertexCount; i++){
                    this.result.uv2.push([
                        struct.binary.consume(4, 'float32'),
                        struct.binary.consume(4, 'float32')
                    ]);
                }

                // for(let u = 0; u < numUv; u++){
                //     for(let i = 0; i < RW.parserTmp.vertexCount; i++){
                //         this.result.UV2_array.push([
                //             struct.binary.consume(4, 'float32'),
                //             struct.binary.consume(4, 'float32')
                //         ]);
                //     }
                // }
            }


            for (let i = 0; i < faceCount; i++) {

                let f2 = struct.binary.consume(2, 'uint16');
                let f1 = struct.binary.consume(2, 'uint16');
                let matId = struct.binary.consume(2, 'uint16');
                let f3 = struct.binary.consume(2, 'uint16');

                this.result.faceMat.face.push([f1, f2, f3]);
                this.result.faceMat.matId.push(matId);
            }

        }

        this.result.boundingSphere.position = struct.binary.consumeMulti(3, 4, 'float32');
        this.result.boundingSphere.radius = struct.binary.consume(4, 'float32');

        struct.binary.seek(4); //hasPosition
        struct.binary.seek(4); //hasNormal: need to recompute. Edit: hmmw why?
        // let hasNormals = (formatFlags & FLAGS_NORMALS) ? 1 : 0;

        // if (struct.binary.remain() > 0){
        if (!this.rootData.hasNativeGeometry){
            for (let i = 0; i < this.rootData.vertexCount; i++) {
                this.result.vert.push(struct.binary.consumeMulti(3, 4, 'float32'));
            }

            if (formatFlags & Renderware.FLAGS_NORMALS){
                for (let i = 0; i < this.rootData.vertexCount; i++) {
                    this.result.normal.push(struct.binary.consumeMulti(3, 4, 'float32'));
                }
            }

        }

        this.validateParsing(struct);

        while(this.binary.remain() > 0){
            this.result.chunks.push( this.processChunk(this.binary) );
        }

        this.validateParsing(this);
    }

}