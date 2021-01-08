/**
 * MDL Reader based on the awesome work from Majest1c_R3 and Allen
 */
MANHUNT.fileLoader.MDL = function () {

    return {
        load: function (level, file, callback ) {

            var results;

            MANHUNT.api.load(
                level._game,
                file,
                function ( data ) {

                    var binary = new NBinary(data);
                    var gameId = binary.consume(4, 'uint32');
                    var isManhunt2 = gameId === 1129074000;

                    binary.setCurrent(0);

                    results = MANHUNT.parser[ isManhunt2 ? 'mdl' : 'dff' ](binary);


                    //TODO !!!
                    var cache = {};

                    callback({
                        getModelNames: function(){
                            var result = [];

                            for(var i in results){
                                if (!results.hasOwnProperty(i)) continue;

                                var entry = results[i];
                                if (isManhunt2){
                                    if (entry.objects.length === 0) continue;
                                    result.push(entry.bone.boneName);
                                }else{
                                    result.push(entry.name);
                                }
                            }

                            return result;
                        },

                        find: function (name) {
                            for(var i in results){
                                if (!results.hasOwnProperty(i)) continue;

                                var entry = results[i];
                                var threeModel;

                                if (isManhunt2){
                                    if (entry.objects.length === 0) continue;

                                    if (entry.bone.boneName.toLowerCase() === name.toLowerCase()){

                                        threeModel = new MANHUNT.converter.mdl2mesh(level, entry);
                                        threeModel.mesh.name = entry.bone.boneName;
                                        return threeModel.mesh;
                                    }
                                }else{
                                    if (entry.name.toLowerCase() === name.toLowerCase()){

                                        threeModel = new MANHUNT.converter.dff2mesh(level, entry);
                                        threeModel.mesh.name = entry.name;
                                        return threeModel.mesh;

                                    }
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