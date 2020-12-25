MANHUNT.sidebar.view.Xyz = function () {
    var base = new MANHUNT.sidebar.view.construct();

    var self = Object.assign(base, {

        _elements: {
            container: {}
        },

        _position : false,
        _save : false,

        _object : false,
        _initialPosition : false,

        _init: function () {


            var container = jQuery('<div>');
            container.addClass("view entity");
            self._elements.container = container;

            self._position = MANHUNT.sidebar.elements.InputGroup({
                x: 0,
                y: 0,
                z: 0
            });

            self._position.container.hide();
            container.append(self._position.container);
        },

        onObjectChanged: function( object ){

            self._object = object;
            self._initialPosition = object.position.clone();

            self._observeProperty('x', object.position, 'x');
            self._observeProperty('y', object.position, 'y');
            self._observeProperty('z', object.position, 'z');

            self._position.container.show();
        },

        _observeProperty: function(property, object, field){
            self._position.setOnChangeCallback(property, function (event) {
                object[field] = parseFloat(event.target.value);
            });

        },

        update: function () {
            if (self._object === false) return false;

            //todo_ change to entity.getPosition().... it returns alread the wanted values
            self._position.updateValue('x', self._object.position.x / MANHUNT.scale);
            self._position.updateValue('y', self._object.position.y / MANHUNT.scale);
            self._position.updateValue('z', self._object.position.z / MANHUNT.scale);
        },

    });


    self._init();

    MANHUNT.sidebar.menu.onObjectChanged(self.onObjectChanged);

    return {
        hide: self.hide,
        show: self.show,
        container: self._elements.container,
        update: self.update
    }
};