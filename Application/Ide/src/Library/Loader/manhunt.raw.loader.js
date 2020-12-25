
MANHUNT.fileLoader.RAW = function () {


    var loader = new THREE.FileLoader();
    loader.setResponseType( 'arraybuffer' );

    return {
        load: function (file, callback ) {

            loader.load(
                file,
                function ( data ) {

                    callback(data);


                }
            );

        }
    };

};