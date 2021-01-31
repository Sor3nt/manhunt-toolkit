MANHUNT.frontend.modal.levelSelection = function () {

    var self = {

        _entryTemplate: document.querySelector('#modal-level-entry'),

        _container : jQuery('#level-selection'),
        _picLoad: jQuery('#level-selection [data-field="picload"]'),

        _loaded: false,

        _init: function () {

            // self._createEvents();
        },

        _createLevelList: function(result){

            var levelFromGame = { manhunt: false, manhunt2: false};
            result.data.forEach(function (levelInfo) {

                var row = jQuery(self._entryTemplate.content).clone();
                self._container.find('[data-field="level-list"]').append(row);
                row = self._container.find('[data-field="level-list"]').find('a:last-child');

                if (levelInfo.game === "manhunt") levelFromGame.manhunt = true;
                else levelFromGame.manhunt2 = true;

                row.find('[data-field="name"]').html(levelInfo.name);
                row.find('[data-field="image"]').attr(
                    'src',
                    levelInfo.game === "manhunt" ?
                        'data/mh1-icon.png' :
                        'data/mh2-icon.png'
                );

                row.hover(function () {
                    self._onHover(levelInfo, row);
                }).click(function () {

                    MANHUNT.studio.loadLevel(levelInfo.game, levelInfo.folderName);
                    MANHUNT.frontend.modal.handler.hide();
                });
            });

            if (levelFromGame.manhunt2 === true){
                var storage = MANHUNT.studio.getStorage('tex');
                storage.load('global/pictures/gui_pc.tex', function () {
                    console.log("LOADED");
                });
            }

            // self._textureView = new MANHUNT.scene.textureView();
            self._textureView = MANHUNT.scene.views.load(self._picLoad, 'textureView');
            MANHUNT.engine.changeScene( 'textureView');
        },

        _onHover: function(levelInfo, row){
            if (levelInfo.icon === "") return;

            var storage = MANHUNT.studio.getStorage('tex');
            var texture = storage.find(levelInfo.icon);

            self._textureView.display(texture);
        },

        show: function(){
            if (self._loaded === false){
                self._loaded = true;
                MANHUNT.api.getLevelList(self._createLevelList);
            }

            self._container.show();
        },

        hide: function(){
            self._container.hide();
        }
    };

    self._init();


    return {
        show: self.show,
        hide: self.hide
    }
};