/**
 * Utility to lookup the Renderware engine structure.
 * By Sor3nt 2021
 */
export default class Scan{

    constructor( binary ){
        this.binary = binary;
        this.usedVersions = [];

        this.pos = 0;



//         //quick unit test todo move
//
//         var buffer = new ArrayBuffer(10);
//         var dataview = new DataView(buffer);
//
//         dataview.setUint8(0,0);
//         dataview.setUint8(1,1);
//         dataview.setUint8(2,2);
//         dataview.setUint8(3,0);
//         dataview.setUint8(4,1);
//         dataview.setUint8(5,2);
//         let teetBin = new NBinary(buffer);
//
//         let block1 = teetBin.consume(3, 'nbinary'); // 0=>0,1=>1,2=>2
//         let block1a = block1.consume(2, 'nbinary'); // 0=>0,1=>1
//
//         block1a.consume(1, 'uint8');
// console.log("offset", block1a.getAbsoluteOffset());
//         if (block1.getAbsoluteOffset() !== 3)
//             debugger;
//         //
//         // if (block1a.consume(1, 'uint8') !== 0)
//         //     debugger;
//         //
//         // if (block1a.getAbsoluteOffset() !== 0)
//         //     debugger;
//         //
//
//         die;
    }

    validateHeader(binary){
        /**
         * Validate the Chunk ID
         */
        let chunkStartOffset = binary.current();
        let id = binary.consume(4, 'uint32');
        let chunkName = Renderware.getChunkNameById(id);
        if (chunkName === false){
            // console.log("fail name");
            binary.setCurrent(chunkStartOffset);
            return false;
        }

        /**
         * Validate the Chunk Size
         */
        let size = binary.consume(4, 'uint32');

        // //do we have enough remained data for the chunk data?
        // if (size + 4 > binary.remain()){ //+ version
        //
        //     let version = binary.consume(4, 'uint32');
        //     binary.seek(-4);
        //     // this chunck block could be smaller as the given size...
        //     if (!(version === 469893134 && id === Renderware.CHUNK_CLUMP)){
        //         binary.setCurrent(chunkStartOffset);
        //         return false;
        //     }
        //
        // }

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

        function add(header, chunkBinary, absoluteStartOffset, deb) {
            let chunkName = Renderware.getChunkNameById(header.id);


            let result = {
                name: chunkName,
                version: header.version,
                offset: absoluteStartOffset,
                children:[],
                debug:deb,
                size: header.size + 12 //header (12 bytes) + block size
            };

            if (_this.usedVersions.indexOf(header.version) === -1)
                _this.usedVersions.push(header.version);

            if (chunkBinary.length() > 0)
                _this.scanChunk(chunkBinary,result);

            parentResult.children.push(result);
            header = null;
        }

        let header = null;
        let reachProcessment = null;
        let checkLen = null;
        while (binary.remain() > 0){

            let offset = false;

            if (binary.remain() > 11) offset = this.validateHeader(binary);

            //Check if the block is odd or even, define the scan size for next chunk
            if (checkLen === null)
                checkLen = binary.remain() % 4 === 0 ? 4 : 1;

            if (offset === false){
                binary.seek(checkLen);
                skippedBytes += checkLen;
                this.pos += checkLen;
                reachProcessment = false;
                continue;
            }
            reachProcessment = true;
            checkLen = null;

            if (skippedBytes > 0){
                parentResult.children.push({ name: "BINARY", offset:  _this.pos - skippedBytes, size: skippedBytes});
                skippedBytes = 0;
            }

            // let absoluteStartOffset = this.pos;// binary.getAbsoluteOffset();

            header = Renderware.parseHeader(binary);
            this.pos += 12;


            // this chunck block could be smaller as the given size...
            if (header.size > binary.remain()){
                header.size = binary.remain();
            }

            /**
             * some chunks sizes are too long... we need to validate it
             */

            if (header.size > 0){
                let currentStart = binary.current();
                binary.setCurrent(binary.current() + header.size);

                //we have space left - at least enough for bytes for the next header
                let lookupDeep = 8;
                if (binary.remain() >= lookupDeep){

                    while(lookupDeep--){
                        let versionTest = binary.consume(4, 'uint32');

                        //we found a nearby header part
                        if (versionTest === header.version){
                            let newSize = binary.current() - 12 - currentStart;
                            if (newSize !== header.size){
                                header.size = newSize;
                            }
                            break;
                        }

                    }

                }

                binary.setCurrent(currentStart);
            }

            console.log("process chunk", Renderware.getChunkNameById(header.id), 'offset', _this.pos - 12, ' size', header.size);



            let chunkBinary = binary.consume(header.size, 'nbinary');

            if (binary.remain() > 0){

                //Do we have also after the start block a valid chunk ?
                let nextOffset = false;
                if (binary.remain() > 11) nextOffset = this.validateHeader(binary);

                //Next chunk is not there or the next chunk has the expected start offset
                //So we assume the first chunk is anyway valid
                if (nextOffset === false)
                    add(header, chunkBinary, this.pos - 12, "case1");
                else if ( nextOffset === binary.current())
                    add(header, chunkBinary, this.pos - 12, "case2");
            }

            else if (binary.remain() === 0)
                add(header, chunkBinary, this.pos - 12, "case3");



            // this.lastSize = header.size;
        }

        let skippedSize = skippedBytes;
        if(reachProcessment === false && skippedBytes > 11){
            // skippedSize += 12;
            // console.log("JJJ");
        }

        if (skippedBytes > 0){
            parentResult.children.push({ name: "BINARY2", offset: this.pos - skippedBytes  , size: skippedSize });
            skippedBytes = 0;
        }
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

