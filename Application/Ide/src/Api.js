MANHUNT.api = (function () {
    var self = {

        _loader : new THREE.FileLoader(),

        load: function (game, file, callback) {
            var json = {
                action: 'read',
                game: game,
                file: file
            };

            var oReq = new XMLHttpRequest();
            oReq.open("POST", "/php/api.php");
            oReq.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
            oReq.responseType = "arraybuffer";

            oReq.onload = function(oEvent) {
                var arrayBuffer = oReq.response;
                callback(arrayBuffer);
            };

            oReq.send(JSON.stringify(json) );
        }
    };

    return {
        load: self.load
    }
})();