MANHUNT.fileLoader.TEX = function () {


    return {
        load: function (level, file, callback ) {

            MANHUNT.api.load(
                level._game,
                file,
                function ( data ) {

                    var binary = new NBinary(data);
                    var gameId = binary.consume(4, 'uint32');
                    var isManhunt2 = gameId === 1413759828;
                    binary.setCurrent(0);

                    callback(MANHUNT.converter.dds2texture(
                        MANHUNT.parser[isManhunt2 ? 'tex' : 'txd' ](binary),
                        isManhunt2
                    ));

                }
            );

        }
    };

};