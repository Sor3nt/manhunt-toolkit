import Chunk from "./Chunk.js";

export default class RightToRender extends Chunk{

    result = {
        pluginIdentifier:  null,
        extraData:  null,
        chunks: []
    };

    parse(){
        /**
         * From: https://gtamods.com/wiki/Right_To_Render_(RW_Section)
         * After this has been read and if the plugin identified by the first value has registered a stream rights callback,
         * this callback is called using extra data as an argument and usually attaches a pipeline.
         * The extra data is typically used to select a pipeline if the plugin provides more than one; when streamed out,
         * the attached pipeline's extra data value is written out.
         * In GTA the Skin and PDS plugins are the only plugins which register a stream rights callback.
         * If no pipeline is attached or it's plugin ID is 0, this chunk is not written.
         */
        this.result.pluginIdentifier = this.binary.consume(4, 'uint32');
        this.result.extraData = this.binary.consume(4, 'uint32');

        this.validateParsing(this);
    }

}