/**
 * MDL Reader based on the awesome work from Majest1c_R3 and Allen
 */
MANHUNT.fileLoader.MDL = function () {

    return {
        load: function (level, file, callback) {

            let modelList = [];

            MANHUNT.api.load(
                level._game,
                file,
                function (data) {

                    let binary = new NBinary(data);
                    let gameId = binary.consume(4, 'uint32');
                    let isManhunt2 = gameId === 1129074000;

                    //Parse the file list
                    binary.setCurrent(0);
                    modelList = MANHUNT.parser[isManhunt2 ? 'mdl' : 'dff'](binary);
console.log("lIST", modelList);
                    callback({
                        getModelNames: function () {
                            let result = [];

                            modelList.forEach(function (model) {
                                result.push(model.name);
                            });

                            return result;
                        },

                        find: function (name) {
                            name = name.toLowerCase();

                            //Note: we keep here a old school for loop
                            //      it is the best way to return the result instant.
                            for (let i in modelList) {
                                if (!modelList.hasOwnProperty(i)) continue;

                                let model = modelList[i];
                                if (model.name.toLowerCase() === name) {

                                    let generic2Mesh = new MANHUNT.converter.generic2mesh(level);
                                    return generic2Mesh.convert(model.data());
                                }
                            }

                            return false;
                        }
                    });

                }
            );

        }
    };

};