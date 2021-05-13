MANHUNT.api = (function () {
    var self = {

        _loader : new THREE.FileLoader(),

        load: function (gameId, file, callback) {
            self._request( {
                action: 'read',
                gameId: gameId,
                file: file
            }, callback);
        },

        _request: function( json, callback){
            var oReq = new XMLHttpRequest();
            oReq.open("POST", "/php/api.php", true);
            oReq.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
            oReq.responseType = "arraybuffer";

            oReq.onload = function(oEvent) {
                callback(oReq.response);
            };

            oReq.send(JSON.stringify(json) );
        },

        getLevelList: function (gameId, callback) {

            self.text( {
                action: 'getLevels',
                id: gameId
            }, callback);
        },

        getConfig: function (callback) {

            self.text( {
                action: 'getConfig'
            }, callback);
        },

        addGame: function (folder, callback) {

            self.text( {
                action: 'addGame',
                data: folder
            }, callback);
        },

        text: function (data, callback) {
            self._request( data, function (data) {
                var text = JSON.parse( (new NBinary(data)).toString() );
                callback && callback(text);
            });
        }
    };

    return {
        text: self.text,
        getLevelList: self.getLevelList,
        addGame: self.addGame,
        getConfig: self.getConfig,
        load: self.load
    }
})();