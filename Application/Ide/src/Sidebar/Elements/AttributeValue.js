MANHUNT.sidebar.elements.AttributeValue = function (entries) {

    var self = {

        container: {},

        _fields: {},

        _init: function () {
            var container = jQuery('<div>');
            container.addClass('element attribute-value');

            for(var i in entries){
                if (!entries.hasOwnProperty(i)) continue;

                var field = self._createField(i, entries[i]);
                container.append(field);
            }

            self.container = container;
        },

        _createField: function (label, value) {

            var template =
                "<label>" + label + "</label>" +
                "<div>" + value + "</div>"
            ;

            var attr = jQuery('<div>').html(label);
            var val = jQuery('<div>').html(value);

            self._fields[label] = val;

            return jQuery('<div>').append(attr, val);

        },


        updateValue: function(label, value) {
            self._fields[label].html(value);

        },


    };

    self._init();


    return {
        container: self.container,
        updateValue: self.updateValue
    }
};