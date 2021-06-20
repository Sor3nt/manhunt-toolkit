/**
 * Utility to lookup the Renderware engine structure.
 * By Sor3nt 2021
 */
export default class Scan{

    constructor( binary ){
        this.binary = binary;
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
         */
        let size = binary.consume(4, 'uint32');

        //do we have enough remained data for the chunk data?
        if (size + 4 > binary.remain()){ //+ version

            let version = binary.consume(4, 'uint32');

            //this chunck block could be smaller as the given size...
            if (!(version === 469893134 && id === Renderware.CHUNK_CLUMP)){
                binary.setCurrent(chunkStartOffset);
                return false;
            }

        }

        /**
         * Validate the Chunk Version
         */
        let version = binary.consume(4, 'uint32');
        let versionRw = Renderware.getVersion(version);
        //we know only versions between 3.0.0.0 and 3.8.0.0


        if (versionRw < 30 || versionRw > 38 || version < 10000){
            binary.setCurrent(chunkStartOffset);
            return false;
        }

        binary.setCurrent(chunkStartOffset);
        return chunkStartOffset;
    }

    scanChunk(binary, parentResult){

        let _this = this;
        let skippedBytes = 0;

        function add(header, chunkBinary, absoluteStartOffset) {
            let chunkName = Renderware.getChunkNameById(header.id);

            if (skippedBytes > 0)
                parentResult.children.push({ name: "BINARY", offset: parentResult.offset + absoluteStartOffset - skippedBytes, size: skippedBytes});

            let result = {
                name: chunkName,
                version: header.version,
                offset: absoluteStartOffset,
                children:[],
                size: header.size + 12 //header (12 bytes) + block size
            };

            if (_this.usedVersions.indexOf(header.version) === -1)
                _this.usedVersions.push(header.version);

            if (chunkBinary.length() > 0)
                _this.scanChunk(chunkBinary,result);

            parentResult.children.push(result);
            skippedBytes = 0;
        }

        while (binary.remain() > 0){

            let offset = false;
            if (binary.remain() > 11) offset = this.validateHeader(binary);

            if (offset === false){
                binary.seek(4);
                skippedBytes += 4;
                continue;
            }

            let absoluteStartOffset = binary.getAbsoluteOffset();

            let header = Renderware.parseHeader(binary);

            //this chunck block could be smaller as the given size...
            if (
                header.version === 469893134 && header.id === Renderware.CHUNK_CLUMP &&
                header.size > binary.remain()
            ){
                header.size = binary.remain();
            }

            let chunkBinary = binary.consume(header.size, 'nbinary');

            if (binary.remain() > 0){

                //Do we have also after the start block a valid chunk ?
                let nextOffset = false;
                if (binary.remain() > 11) nextOffset = this.validateHeader(binary);

                //Next chunk is not there or the next chunk has the expected start offset
                //So we assume the first chunk is anyway valid
                if (nextOffset === false || nextOffset === binary.current())
                    add(header, chunkBinary, absoluteStartOffset);
            }

            else if (binary.remain() === 0)
                add(header, chunkBinary, absoluteStartOffset);
        }

        if (skippedBytes > 0)
            parentResult.children.push({ name: "BINARY2", offset: parentResult.offset + binary.current() + 12 - skippedBytes, size: skippedBytes});
    }

    scan(){
        let result = {
            name: "root",
            size: this.binary.remain()  ,
            offset: this.binary.current(),
            children: [],
        };

        this.scanChunk(this.binary, result);

        result.usedVersions = this.usedVersions;
        return result;
    }
}