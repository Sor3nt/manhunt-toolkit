export default class Config{

    constructor( onLoadCallback ){
        let _this = this;
        _this._config = { games: [] };

        Api.getConfig(function (config) {
            console.log('[MANHUNT.config] Config received', config.data);
            _this._config = config.data;
            onLoadCallback();
        });
    }

    addGame (folder, callback) {
        console.log('[MANHUNT.config] Add folder ', folder);
        Api.addGame(folder, function (result) {
            if (result.status === false) return callback(result);

            console.log('[MANHUNT.config] Add game ', result);
            this._config.games.push(result);
            callback(result);
        });
    }

    getGame (id) {
        return this._config.games[id];
    }

    getGames () {
        return this._config.games;
    }

}

//
// MANHUNT.config = (function () {
//
//     var self = {
//         _config: false,
//
//         _onLoadCallback : false,
//
//         _init: function () {
//             Api.getConfig(function (config) {
//                 console.log('[MANHUNT.config] Config received', config.data);
//                 self._config = config.data;
//                 if (self._onLoadCallback !== false){
//                     self._onLoadCallback();
//                     self._onLoadCallback = false;
//                 }
//             });
//         },
//
//         onLoadCallback: function (callback) {
//             if (self._config !== false) return callback();
//             self._onLoadCallback = callback;
//         },
//
//         addGame: function (folder, callback) {
//             console.log('[MANHUNT.config] Add folder ', folder);
//             Api.addGame(folder, function (result) {
//                 if (result.status === false) return callback(result);
//
//                 console.log('[MANHUNT.config] Add game ', result);
//                 self._config.games.push(result);
//                 callback(result);
//             });
//         },
//
//         getGame: function (id) {
//             return self._config.games[id];
//         },
//
//         getGames: function () {
//             return self._config.games;
//         }
//     };
//
//     self._init();
//
//     return {
//         getGame: self.getGame,
//         getGames: self.getGames,
//         addGame: self.addGame,
//         onLoadCallback: self.onLoadCallback
//     }
// })();