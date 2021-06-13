
import Renderware from "./../Renderware.js";
import Helper from './../../../Helper.js'
const assert = Helper.assert;

export default class Chunk {
    result = {
        unknown: null,
        chunks: []
    };

    /**
     *
     * @param binary {NBinary}
     * @param header {Object}
     * @param rootData {Object}
     */
    constructor(binary, header, rootData){
        this.binary = binary;
        this.header = header;

        //hold global data storage within the tree
        this.rootData = rootData;

        //Renderware Chunk ID
        this.type = header.id;

    }

    /**
     * Remove the unused elements of the chunk chain
     * @returns {Chunk}
     */
    cleanup(){
        this.result.chunks.forEach(function (chunk) {
            chunk.cleanup();
        });
        delete this.binary;
        delete this.header;
        return this;
    }

    /**
     * Validates the remained data of a chunk
     * @param chunk {Chunk}
     */
    validateParsing(chunk){

        if (chunk.binary.remain() > 0){
            let remain = chunk.binary.consume(chunk.binary.remain(), 'arraybuffer');

            console.log("Buffer", remain);
            assert(
                false,
                chunk.type + ': Unable to parse fully the data!'
            );

        }
    }

    /**
     * @param binary {NBinary}
     */
    processChunk(binary){
        return Renderware.parse(binary, this.rootData);
    }

    /**
     * Parse the binary data
     */
    parse(){}

}