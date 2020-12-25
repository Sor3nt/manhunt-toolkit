MANHUNT.sidebar.elements.Dropdown = function ( values ) {

    var self = {

        container: {},

        _init: function () {
            var container = jQuery('<select>');
            container.addClass("element dropdown");
            self.container = container;
        },

        setValues: function(values){
            self.container.html("");

            values.forEach(function (value) {

                var option = jQuery('<option>');
                option.val(value);
                option.html(value);

                self.container.append(option);

            });

            jQuery(self.container).select2();
        },

        onChangeCallback: function(callback){
            self.container.onchange = callback;
        }

    };

    self._init();

    return {
        container: self.container,
        setValues: self.setValues,
        onChangeCallback: self.onChangeCallback,
    }
};