/**
 * Utility to lookup the Renderware engine structure.
 * By Sor3nt 2021
 */
export default class Scan{

    constructor( binary, options ){
        this.binary = binary;
        this.options = options || {
            scanForNewChunks: false,      //search byte per byte for chunk headers (slow)
            forcedFirstVersion: true,    //the first "valid" version will be used for future validation
            forcedVersion: null
        };

        this.pos = 0;
        this.usedVersions = [];
    }

    validateHeader(binary){
        /**
         * Validate the Chunk ID
         */
        let chunkStartOffset = binary.current();
        let id = binary.consume(4, 'uint32');
        let chunkName = Renderware.getChunkNameById(id);
        if (chunkName === false){
            binary.setCurrent(chunkStartOffset);
            return false;
        }

        /**
         * Validate the Chunk Size
         *
         * Actual we can not validate this, in some (rare) cases the size is wrong
         * it could be larger or smaller
         */
        binary.seek(4);

        /**
         * Validate the Chunk Version
         */
        let version = binary.consume(4, 'uint32');

        if (this.options.forcedVersion !== null){

            if (version !== this.options.forcedVersion){
                binary.setCurrent(chunkStartOffset);
                return false;
            }
        }else{

            let versionRw = Renderware.getVersion(version);

            if (versionRw < 210000 || versionRw > 230000){
                binary.setCurrent(chunkStartOffset);
                return false;
            }
        }

        binary.setCurrent(chunkStartOffset);
        return chunkStartOffset;
    }

    scanChunk(binary, parentResult){
        let _this = this;
        let skippedBytes = 0;

        function add(header, chunkBinary, absoluteStartOffset) {
            let chunkName = Renderware.getChunkNameById(header.id);

            let result = {
                name: chunkName,
                offset: absoluteStartOffset,
                size: header.size + 12, //header (12 bytes) + block size
                children:[]
            };

            if (_this.usedVersions.indexOf(header.version) === -1)
                _this.usedVersions.push(header.version);

            if (chunkBinary.length() > 0)
                _this.scanChunk(chunkBinary,result);

            parentResult.children.push(result);
            header = null;
        }

        let header = null;
        let checkLen = null;
        while (binary.remain() > 0){

            /**
             * Data parsing
             * Search for chunk header if not present store as binary data
             */
            {
                let offset = false;
                if (binary.remain() > 11) offset = this.validateHeader(binary);

                //Some Chunk data are bad padded, it can happen that the content is 3bytes
                //in that case we need to search the next block byte per byte...
                if (checkLen === null)
                    checkLen = binary.remain() % 4 === 0 ? 4 : 1;
                //no header found, we walk trough binary block
                if (offset === false){
                    binary.seek(checkLen);
                    skippedBytes += checkLen;
                    this.pos += checkLen;
                    continue;
                }

                //reset the check rule for the next binary block
                checkLen = null;

                if (skippedBytes > 0){
                    parentResult.children.push({ name: "DATA", offset:  _this.pos - skippedBytes, size: skippedBytes});
                    skippedBytes = 0;
                }

                header = Renderware.parseHeader(binary);
                this.pos += 12; //add the 12bytes from the chunk header
            }

            //Set the version from the first "valid" header
            if (this.options.forcedFirstVersion === true && this.options.forcedVersion === null)
                this.options.forcedVersion = header.version;

            /**
             * Fix Chunk sizes
             */
            {
                // a chunk block could be smaller as the given size...
                if (header.size > binary.remain())
                    header.size = binary.remain();

                //some chunks sizes are too long... we need to validate it
                if (header.size > 0){
                    let currentStart = binary.current();
                    binary.setCurrent(binary.current() + header.size);

                    //we have space left - at least enough for bytes for the next header
                    let lookupDeep = 4;
                    if (binary.remain() >= lookupDeep){

                        while(lookupDeep--){
                            if (header.version !== binary.consume(4, 'uint32'))
                                continue;

                            header.size = binary.current() - 12 - currentStart;
                            break;
                        }
                    }

                    binary.setCurrent(currentStart);
                }
            }

            let chunkBinary = binary.consume(header.size, 'nbinary');

            //Do we have also after the start block a valid chunk ?
            let nextOffset = binary.remain() > 11 ? this.validateHeader(binary) : false;

            //Next chunk is not there or the next chunk has the expected start offset
            //So we assume the first chunk is anyway valid
            if (nextOffset === false || nextOffset === binary.current())
                add(header, chunkBinary, this.pos - 12);
        }

        //there could be content after a chunk
        if (skippedBytes > 0)
            parentResult.children.push({ name: "DATA", offset: this.pos - skippedBytes  , size: skippedBytes });
    }

    findCustomChunks(){
        let binary = this.binary;
        let offset = false;
        let version = false;
        while(binary.remain() > 11){
            offset = this.validateHeader(binary);
            if (offset !== false){
                binary.seek(8);
                version = binary.consumeMulti(4, 1, 'uint8');
                break;
            }else{
                binary.seek(4);
            }
        }

        if (!(offset !== false && version !== false)){
            console.log("No chunks found in the file, no renderware file ?!");
            binary.setCurrent(0);
            return;
        }

        let data = new Uint8Array(binary.data);
        let pos = 0;

        binary.setCurrent(0);
        while(pos < data.byteLength){
            if (
                data[pos] === version[0] &&
                data[pos + 1] === version[1] &&
                data[pos + 2] === version[2] &&
                data[pos + 3] === version[3]
            ){
                //Go back to chunk start and read the ID
                binary.setCurrent(pos - 8);
                let id = binary.consume(4, 'uint32');

                let chunkName = Renderware.getChunkNameById(id);

                //Name is unknown which mean this could be a new chunk type
                if (chunkName === false){
                    Renderware['CHUNK_UNKNOWN_' + id] = id;
                }
            }

            pos++;
        }

        binary.setCurrent(0);
    }

    scan(){

        if (this.options.scanForNewChunks === true)
            this.findCustomChunks();

        let result = {
            name: "root",
            children: [],
        };

        this.scanChunk(this.binary, result);

        result.usedVersions = this.usedVersions;
        return result;
    }
}

