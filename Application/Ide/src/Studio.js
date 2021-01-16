
MANHUNT.studio = (function () {
    var self = {

        _globalStorage: {},

        _init: function ( config ) {
            MANHUNT.config.onLoadCallback( self._onConfigReceived );
        },

        _onConfigReceived: function () {
            MANHUNT.engine.init();
            MANHUNT.frontend.tab.init();

            if (MANHUNT.config.get('manhunt_folder') === false && MANHUNT.config.get('manhunt2_folder') === false){
                return MANHUNT.frontend.modal.handler.show('setup', self._onGamePathsKnown);
            }

            self._onGamePathsKnown();
        },

        _onGamePathsKnown: function () {
            MANHUNT.engine.render();

            var storage = new MANHUNT.storage.Storage({ _game: 'manhunt2'});
            self._globalStorage.tex = storage.create('tex');

            MANHUNT.frontend.modal.handler.show('levelSelection', function () {
                
            });
        }


    };

    self._init();

    return {
        getStorage: function (name) {
            return self._globalStorage[name];
        }
    }
})();