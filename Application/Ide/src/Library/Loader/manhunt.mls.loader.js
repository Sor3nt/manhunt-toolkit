
MANHUNT.fileLoader.MLS = function () {


    var loader = new THREE.FileLoader();
    loader.setResponseType( 'arraybuffer' );

    function getLabelSizeData( binary ){
        var label = binary.consume(4, 'string');
        var size = binary.consume(4, 'uint32');

        var data = binary.consume(size, 'arraybuffer');
        return [label, data];
    }

    function parse(data){
        var binary = new NBinary(data);
        do {
            var labelData = getLabelSizeData(binary);

            switch (labelData[0]) {

                case 'DBUG':
                    var dBugBinary = new NBinary(labelData[1]);

                    var labelData2 = getLabelSizeData(dBugBinary);
                    var srce = new NBinary(labelData2[1]);
                    return srce.consume(labelData2[1].byteLength, 'string');
            }
        }while(binary.remain() > 0);
    }

    return {
        load: function (file, callback ) {
            loader.load(
                file,
                function ( data ) {

                    var binary = new NBinary(data);
                    var fourCC = binary.consume(4, 'string');
                    var version = binary.consume(4, 'string');

                    var mhscs = [];

                    do{
                        var labelData = getLabelSizeData(binary);
                        mhscs.push({
                            srce: parse(labelData[1])
                        });
                    }while(binary.remain() > 0);

                    callback(mhscs);

                }
            );
        }
    };


};