MANHUNT.fileLoader.TEX = function () {


    return {
        load: function (level, file, callback ) {

            MANHUNT.api.load(
                level._gameId,
                file,
                function ( data ) {

                    var binary = new NBinary(data);
                    var gameId = binary.consume(4, 'uint32');

                    if (level._platform === "pc"){
                        let isManhunt2 = gameId === 1413759828;
                        binary.setCurrent(0);

                        callback(MANHUNT.converter.dds2texture(
                            MANHUNT.parser[isManhunt2 ? 'tex' : 'txd' ](binary, level._platform),
                            isManhunt2
                        ));

                    }else if (level._platform === "psp001"){
                        return callback(MANHUNT.converter.ps22texture(
                            MANHUNT.parser.tex(binary, level._platform)
                        ));


                    }else if (level._platform === "ps2064"){

                        if (gameId !== 22){
                            console.log("TODO: Not a MH1 ps2 txd file!");
                            return callback([]);
                        }

                        return callback(MANHUNT.converter.ps22texture(
                            MANHUNT.parser.manhuntPs2Txd(binary)
                        ));

                    }
                }
            );

        }
    };

};