
MANHUNT.loader = (function () {
    var self = {

        _loaders : {},

        _init: function(){
            ['GLG', 'IFP', 'MDL', 'BSP', 'TEX', 'MLS', 'TVP', 'INST', 'IFP'].forEach(function (loader) {
                if (typeof MANHUNT.fileLoader[loader] === "undefined") return;
                self._loaders[loader.toLowerCase()] = new MANHUNT.fileLoader[loader]();

            })
        },

        get: function(loader){
            return self._loaders[loader];
        },

        load: function (loader, url, callback) {
            if (typeof self._loaders[loader] === "undefined"){
                console.log("[MANHUNT.loader] Loader unknown", loader);
                return;
            }

            self._loaders[loader].load(url, callback);
        }

    };

    self._init();

    return {
        get: self.get,
        load: self.load
    }
})();