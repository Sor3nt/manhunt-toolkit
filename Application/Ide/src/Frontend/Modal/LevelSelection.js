MANHUNT.frontend.modal.levelSelection = function () {

    var self = {

        _entryTemplate: document.querySelector('#modal-level-entry'),

        _container : jQuery('#level-selection'),
        _picLoad: jQuery('#level-selection [data-field="picload"]'),

        _loaded: false,

        _init: function () {

            // self._createEvents();
        },

        _createLevelList: function(gameId, result){

            let info = MANHUNT.config.getGame(gameId);
            result.data.forEach(function (levelInfo) {

                var row = jQuery(self._entryTemplate.content).clone();
                self._container.find('[data-field="level-list"]').append(row);
                row = self._container.find('[data-field="level-list"]').find('a:last-child');


                row.find('[data-field="name"]').html(levelInfo.name);
                row.find('[data-field="image"]').attr(
                    'src',
                    info.game === "mh" ?
                        'data/mh1-icon.png' :
                        'data/mh2-icon.png'
                );

                row.hover(function () {
                    // self._onHover(levelInfo, row);
                }).click(function () {

                    Studio.loadLevel(gameId, levelInfo);
                    MANHUNT.frontend.modal.handler.hide();
                });

            });

            //
            // if (info.game === "mh2" && info.platform === "pc"){
            //     var storage = MANHUNT.studio.getStorage('tex');
            //     storage.load('global/pictures/gui_pc.tex', function () {
            //         console.log("gui_pc LOADED");
            //     });
            // }

            self._textureView = MANHUNT.scene.views.load(self._picLoad, 'textureView');
            MANHUNT.engine.changeScene( 'textureView');
        },

        _onHover: function(levelInfo, row){
            if (levelInfo.icon === "") return;

            var storage = MANHUNT.studio.getStorage('tex');
            var texture = storage.find(levelInfo.icon);

            self._textureView.display(texture);
        },

        show: function(options){
            if (self._loaded === false){
                self._loaded = true;
                MANHUNT.api.getLevelList(options.gameId, function (list) {
                    self._createLevelList(options.gameId, list);
                });
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