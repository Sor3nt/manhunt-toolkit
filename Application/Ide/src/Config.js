
MANHUNT.config = (function () {

    var self = {
        _config: false,

        _onLoadCallback : false,

        _init: function () {
            MANHUNT.api.getConfig(function (config) {
                self.set(config.data);
            });
        },

        set: function (config) {
            self._config = config;
        },

        update: function(field, value){
            self._config[field] = value;
        },

        get: function ( attr ) {
            if (typeof attr === "undefined") return self._config;

            return self._config[ attr ];
        },

        onLoadCallback: function (callback) {
            if (self._config !== false) return callback();
            self._onLoadCallback = callback;
        },

        save: function (callback) {
            MANHUNT.api.setConfig(self._config, callback);
        }
    };

    self._init();

    return {
        save: self.save,
        onLoadCallback: self.onLoadCallback,
        set: self.set,
        update: self.update,
        get: self.get
    }
})();