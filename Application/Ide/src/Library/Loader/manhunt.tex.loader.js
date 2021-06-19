MANHUNT.fileLoader.TEX = function () {


    return {
        load: function (level, file, callback ) {

            Api.load(
                level._gameId,
                file,
                function ( data ) {

                    var binary = new NBinary(data);
                    var gameId = binary.consume(4, 'uint32');

                    if (gameId === 542327876){ //DDS

                        binary.seek(-4);
                        let texture = (new DDSLoader()).parse(binary.data);

                        let realTexture = new THREE.CompressedTexture(texture.mipmaps, texture.width, texture.height);
                        realTexture.name = file.replace(/^.*[\\\/]/, '').split(".")[0];
                        realTexture.wrapS =  THREE.RepeatWrapping;
                        realTexture.wrapT =  THREE.RepeatWrapping;
                        realTexture.format = texture.format;
                        realTexture.needsUpdate = true;

                        return callback([realTexture]);

                    }else{


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

                }
            );

        }
    };

};