MANHUNT.sidebar.elements.InputGroup = function (entries, type, callback) {

    type = type || 'text';

    var self = {

        container: {},

        _fields: {},

        _init: function () {
            var container = jQuery('<div>');
            container.addClass("element input-group");

            for(var i in entries){
                if (!entries.hasOwnProperty(i)) continue;

                var field = self._createField(i, entries[i]);
                container.append(field.container);
                self._fields[i] = field;

                if (typeof callback === "function"){
                    self.setOnChangeCallback(i, callback);
                }

            }

            self.container = container;
        },

        updateValue: function(label, value) {
            self._fields[label].setValue(value);
        },

        setOnChangeCallback: function(label, callback){
            self._fields[label].onChange(callback);
        },

        _createField: function (label, value) {

            var _label = jQuery('<label>').html(label);

            var input = jQuery('<input>');
            input.val(label);
            input.attr('name', label);
            input.attr('type', type);

            if (type === "checkbox"){
                input.prop('checked', true);
            }else{
                input.val(value);
            }

            var container = jQuery('<div>');
            container.addClass("input label");
            container.append(_label, input);

            return {
                name: label,
                container: container,
                field: input,

                setValue: function (value) {
                    if (type === "checkbox"){
                        input.prop('checked', value);
                    }else{
                        input.val(value);
                    }
                },

                onChange: function (callback) {
                    if (type === "text"){
                        input.blur(callback);
                    }else{
                        input.change(callback);

                    }
                }
            }

        }


    };

    self._init();


    return {
        container: self.container,
        updateValue: self.updateValue,
        setOnChangeCallback: self.setOnChangeCallback,
    }
};