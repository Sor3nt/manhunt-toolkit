import Chunk from "./Chunk.js";

export default class MaterialEffects extends Chunk{

    result = {

        chunks: []
    };

    parse(){

        //todo https://gtamods.com/wiki/Material_Effects_PLG_(RW_Section)
        this.binary.seek(this.header.size);

        this.validateParsing(this);

    }

}