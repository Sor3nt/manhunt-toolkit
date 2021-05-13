MANHUNT.resources.handler = (function () {

    let self = {

        // _level: {},

        fromLevel: function(gameId, levelName, callback){
            let info = MANHUNT.config.getGame(gameId);

            console.log('[MANHUNT.resources.handler] ', info.game, info.platform, levelName);

            new MANHUNT.resources[(info.game === "mh" ? "Manhunt" : "Manhunt2")][info.platform](gameId, levelName, function (storage) {
                callback(storage)
            });
        }

    };

    return {
        fromLevel: self.fromLevel
    }
})();