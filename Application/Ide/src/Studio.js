
MANHUNT.studio = (function () {
    var self = {

        _globalStorage: {},
        _tabHandler: {},

        _init: function ( config ) {
            window.setTimeout(function () {

                MANHUNT.config.onLoadCallback( self._onConfigReceived );
            }, 100);
        },

        _onConfigReceived: function () {
            MANHUNT.engine.init();

            self._tabHandler = new MANHUNT.frontend.Tab(jQuery('#studio-tab-list'), jQuery('#studio-tab-content'));

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
        },

        loadLevel: function (game, levelPath) {
            MANHUNT.scene.views.loadLevel(game, levelPath, function(level){
                level.addScene(MANHUNT.scene.modelView);
                level.addScene(MANHUNT.scene.animationView);
            });
        }


    };

    self._init();

    return {
        loadLevel: self.loadLevel,
        getStorage: function (name) {
            return self._globalStorage[name];
        },

        getTabHandler: function () {
            return self._tabHandler;
        }
    }
})();