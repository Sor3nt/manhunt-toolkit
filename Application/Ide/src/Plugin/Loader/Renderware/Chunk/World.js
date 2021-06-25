import Renderware from "./../Renderware.js";
import Chunk from "./Chunk.js";
import Helper from './../../../../Helper.js'
const assert = Helper.assert;

export default class World extends Chunk{

    result = {
        faceCount: null,
        vertexCount: null,
        sectorCount: null,
        numWorldSectors: null,
        colSectorSize: null,
        format: {},

        chunks: []
    };

    parse(){
        let struct = this.processChunk(this.binary);
        assert(struct.type, Renderware.CHUNK_STRUCT);

        this.result.rootIsWorldSector = struct.binary.consume(4, 'uint32');
        this.result.invWorldOrigin = struct.binary.consumeMulti(3, 4, 'float32');

        if (struct.header.version === 784){
            this.result.surfaceProps = {
                ambient: struct.binary.consume(4, 'float32'),
                specular: struct.binary.consume(4, 'float32'),
                diffuse: struct.binary.consume(4, 'float32')
            };

            this.result.faceCount = struct.binary.consume(4, 'uint32');
            this.result.vertexCount = struct.binary.consume(4, 'uint32');
            this.result.sectorCount = struct.binary.consume(4, 'uint32');
            this.result.numWorldSectors = struct.binary.consume(4, 'uint32');
            this.result.colSectorSize = struct.binary.consume(4, 'uint32');
            let format = struct.binary.consume(4, 'uint32');
            this.result.format = format;

            //Decode world flags
            this.rootData.worldFlags = {};
            for(let i in Renderware.WORLDFLAGS){
                if (!Renderware.WORLDFLAGS.hasOwnProperty(i)) continue;

                this.rootData.worldFlags[i] = !!(format & Renderware.WORLDFLAGS[i]);
            }

        }else{
            this.result.faceCount = struct.binary.consume(4, 'uint32');
            this.result.vertexCount = struct.binary.consume(4, 'uint32');
            struct.binary.seek(4);
            this.result.sectorCount = struct.binary.consume(4, 'uint32');

            struct.binary.seek(32);


        }

        this.validateParsing(struct);

        while(this.binary.remain() > 0){
            this.result.chunks.push(this.processChunk(this.binary));
        }

        this.validateParsing(this);

    }

}