export default class Scan{

    constructor( binary, version ){
        this.binary = binary;
        this.version = version || null;
        this.deep = 0;
        this.log = [];
        this.result = {
            good: [],
            weak: []
        };
    }

    searchFirstHeader(binary){
        let _this = this;

        /**
         * Search chunk block based on the given version
         * @returns {boolean|*|null|number|void}
         */
        function searchStartByVersion() {
            binary.setCurrent(0);
            while (binary.remain() > 11){
                let lookup = binary.consume(4, 'uint32');

                if (lookup === _this.version){
                    /*
                     * We found the exact version, go back to header start
                     */
                    binary.seek(-12);
                    let maybeOffset = binary.current();
                    let chunkId = binary.consume(4, 'uint32');
                    let chunkName = Renderware.getChunkNameById(chunkId);

                    // We found a valid chunk !
                    if (chunkName !== false){
                        binary.setCurrent(maybeOffset);
                        return maybeOffset;
                    }
                }
            }

            return false;
        }

        /**
         * We search for known start chunks
         */
        function searchStartLogic1() {
            binary.setCurrent(0);

            //search CHUNK_STRUCT
            while (binary.remain() > 11) {

                // let chunkName = Renderware.getChunkNameById(lookupInner);

                let maybeOffset = binary.current();
                let lookup = binary.consume(4, 'uint32');

                switch (lookup) {
                    /**
                     * CHUNK_WORLD represents a level / map
                     * CHUNK_TEXDICTIONARY represents texture list
                     * both contains a CHUNK_STRUCT
                     */
                    case Renderware.CHUNK_WORLD:
                    case Renderware.CHUNK_TEXDICTIONARY:
                        binary.seek(8); // size, version
                        if (binary.consume(4, 'uint32') !== Renderware.CHUNK_STRUCT){
                            _this.log.push(`Assert failed: CHUNK_WORLD found at ${maybeOffset} but it does not contains a CHUNK_STRUCT!`);
                            continue;
                        }

                        break;

                    /**
                     * represents a Model / Object
                     * can contain different types of chunks
                     */
                    case Renderware.CHUNK_CLUMP:
                        binary.seek(8); // size, version

                        let chunkId = binary.consume(4, 'uint32');

                        if ([
                            Renderware.CHUNK_STRUCT,
                            Renderware.CHUNK_FRAMELIST,
                            Renderware.CHUNK_GEOMETRYLIST,
                            Renderware.CHUNK_ATOMIC,
                            Renderware.CHUNK_EXTENSION
                        ].indexOf(chunkId) === -1){
                            _this.log.push(`Assert failed: CHUNK_WORLD found at ${maybeOffset} but it does not contain a valid chunk!`);
                            continue;
                        }

                        break;

                    /**
                     * Nothing matched :( start next loop
                     */
                    default:
                        continue;

                }


                /**
                 * All validations passed, return the offset
                 */
                binary.setCurrent(maybeOffset);
                return maybeOffset;

            }

            //we could not find the chain start
            return false;

        }

        /**
         * We search a CHUNK_STRUCT to get a version
         * We try to validate then the version
         */
        function searchStartLogic2() {
            binary.setCurrent(0);

            //search CHUNK_STRUCT
            while (binary.remain() > 11){

                let maybeOffset = binary.current();
                let lookup = binary.consume(4, 'uint32');
                if (lookup !== Renderware.CHUNK_STRUCT) continue;

                let maybeSize = binary.consume(4, 'uint32');

                //the value is larger as the remained data, its not our chunk :(
                if (maybeSize > binary.remain()){
                    _this.log.push(`Assert failed: The given size ${maybeSize}b (${binary.remain()}b available) is larger then the remained content.`);
                    binary.seek(-4); //revert maybeSize
                    continue;
                }

                let maybeVersion = binary.consume(4, 'uint32');

                /**
                 * check if the next entry is also a CHUNK
                 */
                let lookupInner = binary.consume(4, 'uint32');

                let chunkName = Renderware.getChunkNameById(lookupInner);
                if (chunkName !== false){
                    binary.seek(4); //seek size
                    let testVersion = binary.consume(4, 'uint32');

                    //We found a valid chunk chain :)
                    if (maybeVersion === testVersion){
                        binary.setCurrent(maybeOffset);
                        return maybeOffset;
                    }else{
                        _this.log.push(`Possible Chunk ${chunkName} found at relative offset ${maybeOffset} but the versions mismatches.`);
                    }

                }

                //test failed
                binary.setCurrent(maybeOffset + 4);
            }

            return false;
        }


        /**
         * Some Chunk files has just one chunk
         */
        function searchStartLogic3() {
            binary.setCurrent(0);


            if (binary.remain() < 12) return false;

            binary.setCurrent(4);
            let size = binary.consume(4, 'uint32');
            binary.seek(4);
            if (binary.remain() === size){
                binary.setCurrent(0);
                return 0;
            }

            binary.setCurrent(0);
            return false;

        }


        let startOffset = false;
        if (this.version !== null){
            _this.log.push(`Search chunk chain by Version ${this.version} `);
            startOffset = searchStartByVersion();

            if (startOffset === false){
                _this.log.push(`No chunks found with Version ${this.version}, start primary search logic`);
                startOffset = searchStartLogic1();
            }
        }else{
            startOffset = searchStartLogic1();
        }

        if (startOffset === false)
            startOffset = searchStartLogic2();


        if (startOffset === false)
            startOffset = searchStartLogic3();


        if (startOffset === false) return false;


        /**
         * Return the star offset
         */
        binary.setCurrent(startOffset);
        let chunkId = binary.consume(4, 'uint32');
        binary.setCurrent(startOffset);

        _this.log.push(`Start Chunk ${Renderware.getChunkNameById(chunkId)} found at Offset ${startOffset}.`);


        return startOffset;

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
         */
        let size = binary.consume(4, 'uint32');

        //do we have enough remained data for the chunk data?
        if (size + 4 > binary.remain()){ //+ version
            binary.setCurrent(chunkStartOffset);
            return false;
        }

        /**
         * Validate the Chunk Version
         */
        let version = Renderware.getVersion(binary.consume(4, 'uint32'));
        //we know only versions between 3.0.0.0 and 3.8.0.0
        if (version < 30 || version > 38){
            binary.setCurrent(chunkStartOffset);
            return false;
        }

        binary.setCurrent(chunkStartOffset);
        return chunkStartOffset;
    }

    scanChunk(binary, parentResult){

        let entries = [];
        let skippedBytes = 0;
        while (binary.remain() > 11){

            let startOffset = binary.current();
            let offset = this.validateHeader(binary);
            if (offset === false){
                binary.seek(4);
                skippedBytes += 4;
                continue;
            }

            let header = Renderware.parseHeader(binary);

            let chunkBinary = binary.consume(header.size, 'nbinary');

            if (binary.remain() > 11){
                //Do we have also after the start block a valid chunk ?
                let nextOffset = this.validateHeader(binary);

                // Next chunk is not there :( its not a CHUNK block
                if (nextOffset === false){
                    // console.log("jo fail");
                    // binary.seek(4);
                    continue;
                }

                //The next chunk has the expected start offset
                //So we assume the first chunk is valid
                else if (nextOffset === binary.current()){

                    let chunkName = Renderware.getChunkNameById(header.id);
                    console.log("-".repeat(this.deep), "Found " + chunkName, "size", header.size, "offset", startOffset, "par", parentResult);

                    if (skippedBytes > 0)
                        parentResult.children.push({ name: "BINARY", skippedBytes: skippedBytes});

                    let result = {
                        name: chunkName,
                        children:[]
                    };

                    this.deep++;
                    if (chunkBinary.length() > 0)
                        this.scanChunk(chunkBinary,result);
                    this.deep--;

                    parentResult.children.push(result);
                    skippedBytes = 0;
                    continue;
                }

                //We found a unexpected start for a chunk
                else{
                    debugger;
                }

            }else if (binary.remain() === 0){
                let chunkName = Renderware.getChunkNameById(header.id);
                console.log("-".repeat(this.deep), "Last Found " + chunkName, "size", header.size);

                if (skippedBytes > 0)
                    parentResult.children.push({ name: "BINARY", skippedBytes: skippedBytes});

                let result = {
                    name: chunkName,
                    children:[]
                };

                this.deep++;
                if (chunkBinary.length() > 0)
                    this.scanChunk(chunkBinary,result);
                this.deep--;

                parentResult.children.push(result);
                skippedBytes = 0;

            }
        }
        //
        // if (result.name === null)
        //     console.log("eh", result, binary.remain());
        //     // result.name = Renderware.getChunkNameById(header.id);
console.log(skippedBytes);
        // return entries;
    }

    scan(){

        // let offset = this.searchFirstHeader(this.binary);
        // if (offset === false){
        //     this.log.push(`No chain found :(`);
        //     return false;
        // }
        //
        // this.binary.setCurrent(offset);
        let result = {
            name: "root",
            skippedBytes: 0,
            children: [],
            children2: [],
        };

        this.scanChunk(this.binary, result);
        return result;



    }

}