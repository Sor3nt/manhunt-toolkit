MANHUNT.control = (function () {

    var self = {

        walkSpeed: 350,
        crouchSpeed: 175,

        _controls: {
            moveForward: false,
            moveBackward: false,
            moveLeft: false,
            moveRight: false
        },

        _control: {
        },

        _active: 'default',

        init: function () {

            var renderer = MANHUNT.engine.getRenderer();
            self._control.transform = new TransformControls( MANHUNT.camera.getCamera(), renderer.domElement );

            self._control.orbit = new OrbitControls( MANHUNT.camera.getCamera(), renderer.domElement );
            self._control.orbit.enableDamping = true;
            self._control.orbit.dampingFactor = 0.05;
            self._control.orbit.screenSpacePanning = false;
            self._control.orbit.minDistance = 50 ;
            self._control.orbit.maxDistance = 500 ;
            self._control.orbit.maxPolarAngle = Math.PI / 2;
            self._control.orbit.enabled = false;
console.log(self._control.orbit);


            self._control.fly = new FlyControls( MANHUNT.camera.getCamera(), renderer.domElement );
            self._control.fly.movementSpeed = 800;
            self._control.fly.domElement = renderer.domElement;
            self._control.fly.rollSpeed = Math.PI / 8;
            self._control.fly.autoForward = false;
            self._control.fly.dragToLook = false;
            self._control.fly.mousedown = function(){};
            self._control.fly.mouseup = function(){};
            self._createEvents();

            MANHUNT.engine.getScene('world').add( self._control.transform );
        },

        _createEvents: function(){

            document.addEventListener( 'keydown', self._onKeyDown, false );
            document.addEventListener( 'keyup', self._onKeyUp, false );

            self._control.transform.addEventListener( 'change', function ( event ) {
                if (self._active !== "transform") return;

                var section = MANHUNT.sidebar.menu.getSection('entity');
                section && section.getView('xyz').update();
            } );

            self._control.transform.addEventListener( 'dragging-changed', function ( event ) {
                self._control.orbit.enabled = ! event.value;
            } );

        },

        update: function( delta ){
            if (MANHUNT.camera.lookAt() === null) return;


            if (self._active === "transform") {
                self._control.orbit.update();
            }else if (self._active === "fly"){
                self._control.fly.update(delta);
            }else{
                var lookAt = MANHUNT.camera.lookAt();
                var moveDistance = 700 * delta;

                // move forwards / backwards
                if ( self._controls.moveBackward )
                    lookAt.translateZ( -moveDistance );
                if ( self._controls.moveForward )
                    lookAt.translateZ(  moveDistance );
                // rotate left/right
                if ( self._controls.moveLeft )
                    lookAt.rotation.y += delta;
                if ( self._controls.moveRight )
                    lookAt.rotation.y -= delta;

            }

        },

        _onKeyDown: function (event) {

            switch (event.keyCode) {

                case 38: /*up*/
                case 87: /*W*/
                    self._controls.moveForward = true;
                    break;

                case 40: /*down*/
                case 83: /*S*/
                    self._controls.moveBackward = true;
                    break;

                case 37: /*left*/
                case 65: /*A*/
                    self._controls.moveLeft = true;
                    break;

                case 39: /*right*/
                case 68: /*D*/
                    self._controls.moveRight = true;
                    break;


            }

        },
        _onKeyUp: function (event) {

            switch (event.keyCode) {

                case 27: /*ESC*/
                    var player = MANHUNT.level.getStorage('entity').find('player(player)');
                    MANHUNT.camera.lookAt(player.object);
                    self.active('default');

                    MANHUNT.sidebar.menu.object(player.object);

                    break;
                case 38: /*up*/
                case 87: /*W*/
                    self._controls.moveForward = false;
                    break;

                case 40: /*down*/
                case 83: /*S*/
                    self._controls.moveBackward = false;
                    break;

                case 37: /*left*/
                case 65: /*A*/
                    self._controls.moveLeft = false;
                    break;

                case 39: /*right*/
                case 68: /*D*/
                    self._controls.moveRight = false;
                    break;


            }

        },

        active: function (state) {
            if (typeof state === "undefined") return self._active;

            //we have this active so we select something else...
            if (self._active === "transform"){
                self._control.orbit.enabled = false;
                self._control.transform.detach( MANHUNT.camera.lookAt() );
            }

            self._active = state;

            switch (self._active) {

                case 'transform':

                    var lookAt = MANHUNT.camera.lookAt();
                    self._control.orbit.enabled = true;
                    self._control.orbit.target.copy(lookAt.position);
                    self._control.transform.attach( lookAt );

                    break;

                case '':
                    break;

            }
        }
    };

    return {
        init: self.init,
        update: self.update,
        active: self.active
    }
})();