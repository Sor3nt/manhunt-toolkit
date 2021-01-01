MANHUNT.frontend.Model = function (level) {

    var self = {

        _storage: {},
        _template: {},
        _container: {},

        _init: function () {
            self._container = jQuery('#model-list');
            self._filter = jQuery('#model-filter');
            self._template = document.querySelector('#model-list-entry');
            self._templatePos = document.querySelector('#model-list-info-position');

            self._filter.keyup(function () {
                jQuery.each(self._row, function (name, row) {
                    if (name.indexOf(self._filter.val()) === -1)
                        row.hide();
                    else
                        row.show();
                });
            });

        },

    };

    self._init();

    return {
        loadResources: self.loadResources,
    }
};