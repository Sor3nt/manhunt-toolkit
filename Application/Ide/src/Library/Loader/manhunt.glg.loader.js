MANHUNT.fileLoader.GLG = function () {

    return {
        load: function (level, file, callback) {

            Api.load(
                level._gameId,
                file,
                function ( data ) {


                    let list = Loader.parse(new NBinary(data));

                    callback(list);

                }
            );

        }
    };

};