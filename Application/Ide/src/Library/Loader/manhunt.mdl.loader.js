/**
 * MDL Reader based on the awesome work from Majest1c_R3 and Allen
 */
MANHUNT.fileLoader.MDL = function () {

    return {
        load: function (level, file, callback) {

            let modelList = [];

            MANHUNT.api.load(
                level._gameId,
                file,
                function (data) {

                    let binary = new NBinary(data);
                    let gameId = binary.consume(4, 'uint32');

                    //mh2 pc
                    if (gameId === 1129074000) {// PMLC
                        //Parse the file list
                        binary.setCurrent(0);
                        modelList = MANHUNT.parser.mdl(binary, level);

                    //mh2 psp
                    }else if (gameId === 1413697089){
                            //Parse the file list
                        binary.setCurrent(0);
                        modelList = MANHUNT.parser.mdl(binary, level);


                    }else{

                        //TODO : still in usee ?!!?
                        //Parse the file list
                        binary.setCurrent(0);
                        modelList = MANHUNT.parser.dff(binary, level);

                    }

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

                                    let old = MANHUNT.converter.generic2mesh(level, model.data());
                                    // console.log("old mesh", old);
                                    return old;


                                    let gen = generateMesh(level._storage.tex, model.data());
                                    console.log("new mesh", gen);

                                    return gen;

                                    // return
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