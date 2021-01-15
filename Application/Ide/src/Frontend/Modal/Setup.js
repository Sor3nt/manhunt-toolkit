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
            ['manhunt_folder', 'manhunt2_folder'].forEach(function (game) {
                if (MANHUNT.config.get(game) === false) return;


                self._container.find('[data-game-folder="' + game + '"]')
                    .removeClass('success error')
                    .addClass('success')
                    .val(MANHUNT.config.get(game))
                ;

            });
        },

        show: function(){
            self.refresh();
            self._container.show();
        },

        hide: function(){
            self._container.hide();
        },

        save: function(){
            var config = MANHUNT.config.get();
            ['manhunt_folder', 'manhunt2_folder'].forEach(function (game) {
                var val = self._container.find('[data-game-folder="' + game + '"]' ).val();
                if (val === "") return;
                config[game] = val;
            });

            MANHUNT.config.set(config);

            MANHUNT.config.save(function (response) {

                if (response.status === true){
                    return MANHUNT.frontend.modal.handler.hide();
                }

                self._container.find('[data-game-folder="' + response.field + '"]')
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