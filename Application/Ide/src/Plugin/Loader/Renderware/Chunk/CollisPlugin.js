
import Chunk from "./Chunk.js";

export default class CollisPlugin extends Chunk{

    parse(){

        //TODO: https://github.com/Maufeat/DragonBallOnline/blob/201c28d8067fe0ddfde9da49c76516de82c394bf/DboClient/Lib/Ntl_Plugin_Collis/collis/colldata.c#L156
        this.binary.seek(this.header.size);

        this.validateParsing(this);
    }

}