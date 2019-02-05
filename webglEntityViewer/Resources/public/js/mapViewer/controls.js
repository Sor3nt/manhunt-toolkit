function Controls( environment ){

    var self = {

        controls: {},
        raycaster: {},

        move: {
            up: false,
            left: false,
            down: false,
            right: false
        },

        _init : function () {
            self._createControls();
            self._createEvents();

            self.raycaster = new THREE.Raycaster( new THREE.Vector3(), new THREE.Vector3( 0, - 1, 0 ), 0, 10 );

        },


        _createControls: function () {
            self.controls = new THREE.PointerLockControls( environment.camera );
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


        },

        update: function () {
            if ( self.controls.isLocked === true ) {


//            if (window.testLabel) window.testLabel.LookAt(camera.position);

                self.raycaster.ray.origin.copy( self.controls.getObject().position );
                self.raycaster.ray.origin.y -= 10;

                var time = performance.now();
                var delta = ( time - prevTime ) / 1000;

                velocity.x -= velocity.x * 10.0 * delta;
                velocity.z -= velocity.z * 10.0 * delta;


                direction.z = Number( self.move.up ) - Number( self.move.down );
                direction.x = Number( self.move.left ) - Number( self.move.right );
                direction.normalize(); // this ensures consistent movements in all directions

                if ( self.move.up || self.move.down ) velocity.z -= direction.z * 4000.0 * delta;
                if ( self.move.left || self.move.right ) velocity.x -= direction.x * 4000.0 * delta;


                self.controls.getObject().translateX( velocity.x * delta );
//            controls.getObject().translateY( velocity.y * delta );
                self.controls.getObject().translateZ( velocity.z * delta );

                self.controls.getObject().position.y = 70;



                prevTime = time;

            }
        }

    };

    self._init();


    return {
        controls: self.controls,
        move: self.move,
        moveTo: self.moveTo,
        update: self.update
    };

}