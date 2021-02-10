
MANHUNT.studio = (function () {
    let self = {

        _globalStorage: {},
        _tabHandler: {},

        _init: function () {
            //timeout hack for FF (?!)
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

            //for level selection mini pic
            let storage = new MANHUNT.storage.Storage({ _game: 'manhunt2'});
            self._globalStorage.tex = storage.create('tex');

            // MANHUNT.frontend.modal.handler.show('levelSelection');
            new MANHUNT.scene.AnimationPortView();
        },

        loadLevel: function (game, levelName) {
            MANHUNT.resources.handler.fromLevel(game, levelName, function(storage){
                new MANHUNT.scene.Level(game, levelName, storage);

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