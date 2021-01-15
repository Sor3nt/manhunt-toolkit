MANHUNT.frontend.modal.handler = (function () {

    var self = {

        _modals : {
            setup: new MANHUNT.frontend.modal.Setup(),
            levelSelection: new MANHUNT.frontend.modal.levelSelection()
        },

        _active: false,
        _onHideCallback : false,

        show: function ( name, onHideCallback ) {
            if (self._active !== false) self._active.hide();
            self._active = self._modals[name];
            self._active.show();

            self._onHideCallback = onHideCallback;
        },

        hide: function () {
            if (self._active === false) return;
            self._active.hide();
            self._active = false;

            self._onHideCallback && self._onHideCallback();
        }

    };

    return {
        show: self.show,
        hide: self.hide
    }
})();