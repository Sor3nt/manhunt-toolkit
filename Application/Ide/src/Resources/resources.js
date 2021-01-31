MANHUNT.resources.handler = (function () {

    let self = {

        // _level: {},

        fromLevel: function(game, levelName, callback){

            new MANHUNT.resources[game === "manhunt" ? "Manhunt" : "Manhunt2"](levelName, function (storage) {
                // self._level[game + '_' + levelName] = storage;
                callback(storage)
            });
        }

    };

    return {
        fromLevel: self.fromLevel
    }
})();