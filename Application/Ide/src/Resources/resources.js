MANHUNT.resources.handler = (function () {

    let self = {

        // _level: {},

        fromLevel: function(gameId, levelInfo, callback){
            let info = MANHUNT.config.getGame(gameId);

            console.log('[MANHUNT.resources.handler] ', info.game, info.platform, levelInfo.name);

            new MANHUNT.resources[info.game][info.platform](gameId, levelInfo.folderName, function (storage) {
                callback(storage)
            });
        }

    };

    return {
        fromLevel: self.fromLevel
    }
})();