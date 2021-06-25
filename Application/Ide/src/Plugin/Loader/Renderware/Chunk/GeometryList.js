import Renderware from "./../Renderware.js";
import Chunk from "./Chunk.js";
import Helper from './../../../../Helper.js'
const assert = Helper.assert;

export default class GeometryList extends Chunk{

    parse(){
        let struct = this.processChunk(this.binary);
        assert(struct.type, Renderware.CHUNK_STRUCT);

        let GeometryCount = struct.binary.consume(4, 'uint32');

        this.validateParsing(struct);

        while(GeometryCount--){

            let geoChunk = this.processChunk(this.binary);
            assert(geoChunk.type, Renderware.CHUNK_GEOMETRY);

            this.result.chunks.push( geoChunk );
        }

        this.validateParsing(this);
    }

}