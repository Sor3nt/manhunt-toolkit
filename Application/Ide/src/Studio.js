export default class Studio{

    static tabHandler = null;
    static config = null;

    static boot() {
        Studio.config = new Config(Studio.onConfigReceived);
    }

    static onConfigReceived() {
        MANHUNT.engine.init();
        Studio.tabHandler = new Tab(jQuery('#studio-tab-list'), jQuery('#studio-tab-content'));

        if (Studio.config.getGames().length === 0){
            return MANHUNT.frontend.modal.handler.show('setup', self._onGamePathsKnown);
        }

        Studio.onGamePathsKnown();
    }

    static onGamePathsKnown () {

        MANHUNT.engine.render();

        //for level selection mini pic
        // let storage = new MANHUNT.storage.Storage({ _game: 'mh2', _platform: 'pc'});
        // self._globalStorage.tex = storage.create('tex');

        MANHUNT.frontend.modal.handler.show('levelSelection', { gameId: 0 });
        // new MANHUNT.scene.AnimationPortView();
    }

    static loadLevel (gameId, levelInfo) {
        MANHUNT.resources.handler.fromLevel(gameId, levelInfo, function(storage){
            new MANHUNT.scene.Level(gameId, levelInfo, storage);

        });
    }

}
