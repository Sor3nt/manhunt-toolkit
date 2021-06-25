
import Chunk from "./Chunk.js";
import Renderware from "../Renderware.js";

export default class UserDataPlugin extends Chunk{

    result = {
        chunks: []
    };

    parse(){

        /*
            struct RpUserDataArray
            {
                RwChar              *name;          /< Identifier for this data array /
                RpUserDataFormat    format;         /< Data format of this array /
                RwInt32             numElements;    /< Number of elements in this array /
                void                *data;          /< Pointer to the array data /
            };

            or older (7sin)
            struct RpUserDataArray
            {
                int 1
                string size
                string
                ...
            };
         */

        this.binary.seek(this.binary.remain());
    }


}