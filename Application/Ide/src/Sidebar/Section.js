MANHUNT.sidebar.Section = function (settings) {

    var self = {

        container: {},
        sectionButton: {},

        _view : {},

        _init: function () {
            self.container = jQuery('<div>');
            self.container.addClass("section");


            self.sectionButton = jQuery('<div>');
            self.sectionButton.html(settings.icon);

            self.hide();
        },

        addView: function (viewName, view ) {
            self.container.append(view.container);
            self._view[viewName] = view;
        },

        getView: function (viewName) {
            if (typeof self._view[viewName] === "undefined") return false;

            return self._view[viewName];
        },

        hide: function (view) {
            if (typeof view === "undefined") self.container.hide();
            else self._view[view].hide();
            self.sectionButton.removeClass('active');
        },

        show: function (view) {
            if (typeof view === "undefined") self.container.show();
            else self._view[view].show();
            self.sectionButton.addClass("active");
        },

    };

    self._init();

    return {
        container: self.container,
        sectionButton: self.sectionButton,

        hide: self.hide,
        show: self.show,
        addView: self.addView,
        getView: self.getView
    }
};