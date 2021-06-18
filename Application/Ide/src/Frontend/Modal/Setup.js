MANHUNT.frontend.modal.Setup = function () {

    var self = {

        _container : jQuery('#game-setup'),


        _init: function () {
            self._createEvents();
        },

        _createEvents: function () {
            self._container.find('[data-field="save"]').click(self.save);
        },

        refresh: function(){
            // ['manhunt_folder', 'manhunt2_folder'].forEach(function (game) {
            //     if (MANHUNT.config.get(game) === false) return;
            //
            //
            //     self._container.find('[data-game-folder="' + game + '"]')
            //         .removeClass('success error')
            //         .addClass('success')
            //         .val(MANHUNT.config.get(game))
            //     ;
            //
            // });
        },

        show: function(){
            self.refresh();
            self._container.show();
        },

        hide: function(){
            self._container.hide();
        },

        save: function(){

            Studio.config.addGame(self._container.find('[data-game-folder]' ).val(), function (response) {

                if (response.status === true){
                    return MANHUNT.frontend.modal.handler.hide();
                }

                self._container.find('[data-game-folder]')
                    .removeClass('success error')
                    .addClass('error')
                ;
            });
        }
    };

    self._init();


    return {
        refresh: self.refresh,
        save: self.save,
        show: self.show,
        hide: self.hide
    }
};