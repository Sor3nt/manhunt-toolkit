function Controls( environment ){

    var self = {

        controls: {},

        move: {
            up: false,
            left: false,
            down: false,
            right: false
        },

        _init : function () {
            self._createControls();
            self._createEvents();
        },


        _createControls: function () {
            self.controls = new THREE.PointerLockControls( environment.camera );
            console.log(self.controls);
            environment.scene.add( self.controls.getObject() );

        },

        _createEvents: function () {
            document.addEventListener( 'keydown', self._onKeyDown, false );
            document.addEventListener( 'keyup', self._onKeyUp, false );

        },

        _onKeyDown: function ( event ) {
            switch ( event.keyCode ) {

                case 38: // up
                case 87: // w
                    self.move.up = true;
                    break;

                case 37: // left
                case 65: // a
                    self.move.left = true;
                    break;

                case 40: // down
                case 83: // s
                    self.move.down = true;
                    break;

                case 39: // right
                case 68: // d
                    self.move.right = true;
                    break;

            }
        },

        _onKeyUp: function ( event ) {
            switch ( event.keyCode ) {

                case 38: // up
                case 87: // w
                    self.move.up = false;
                    break;

                case 37: // left
                case 65: // a
                    self.move.left = false;
                    break;

                case 40: // down
                case 83: // s
                    self.move.down = false;
                    break;

                case 39: // right
                case 68: // d
                    self.move.right = false;
                    break;

            }
        },

        moveTo: function (x, y, z) {

            self.controls.getObject().translateX( x * environment.worldScale);
            self.controls.getObject().translateY( y * environment.worldScale );
            self.controls.getObject().translateZ( z * environment.worldScale);


        }

    };

    self._init();


    return {
        controls: self.controls,
        move: self.move,
        moveTo: self.moveTo
    };

}