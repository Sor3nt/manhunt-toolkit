MANHUNT.sidebar.elements.Button = function (label) {

    var self = {

        container: {},

        _button: {},

        _init: function () {

            self._button = jQuery('<option>').html(label);

            var container = jQuery('<div>');
            container.addClass("element button");
            container.append(self._button);

            self.container = container;
        },


        setOnClickCallback: function(callback){
            self._button.onclick = callback;
        },

    };

    self._init();

    return {
        container: self.container,
        setOnClickCallback: self.setOnClickCallback,
    }
};