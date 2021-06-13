
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
console.log(Tab);
            self._tabHandler = new Tab(jQuery('#studio-tab-list'), jQuery('#studio-tab-content'));

            if (MANHUNT.config.getGames().length === 0){
                return MANHUNT.frontend.modal.handler.show('setup', self._onGamePathsKnown);
            }

            self._onGamePathsKnown();
        },

        _onGamePathsKnown: function () {

            MANHUNT.engine.render();

            //for level selection mini pic
            // let storage = new MANHUNT.storage.Storage({ _game: 'mh2', _platform: 'pc'});
            // self._globalStorage.tex = storage.create('tex');

            MANHUNT.frontend.modal.handler.show('levelSelection', { gameId: 0 });
            // new MANHUNT.scene.AnimationPortView();
        },

        loadLevel: function (gameId, levelInfo) {
            MANHUNT.resources.handler.fromLevel(gameId, levelInfo, function(storage){
                new MANHUNT.scene.Level(gameId, levelInfo, storage);

            });
        }


    };


    //TODO TMP: remove , braucchen wir wegen dem modul lloader... alles etwas asyncron hier
    window.setTimeout(function () {
        self._init();

    }, 500);

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