MANHUNT.frontend.modal.handler = (function () {

    var self = {

        _modals : {
            setup: new MANHUNT.frontend.modal.Setup(),
            levelSelection: new MANHUNT.frontend.modal.levelSelection()
        },

        _active: false,

        show: function ( name, options ) {
            if (self._active !== false) self._active.hide();
            self._active = self._modals[name];
            self._active.show(options);
        },

        hide: function () {
            if (self._active === false) return;
            self._active.hide();
            self._active = false;
        }

    };

    return {
        show: self.show,
        hide: self.hide
    }
})();