MANHUNT.control.ThirdPerson = function (sceneInfo) {

    var self = {

        _controls: {
            moveForward: false,
            moveBackward: false,
            moveLeft: false,
            moveRight: false
        },

        _active: false,

        _init: function () {
            document.addEventListener( 'keydown', self._onKeyDown, false );
            document.addEventListener( 'keyup', self._onKeyUp, false );
        },

        enable: function (object) {
            if (typeof object === "undefined") return self._active;
            self._active = object;
        },

        update: function( delta ){
            if (self._active === false) return;

            var lookAt = self._active;
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
                //
                // case 27: /*ESC*/
                //     var player = MANHUNT.level.getStorage('entity').find('player(player)');
                //     MANHUNT.camera.lookAt(player.object);
                //     self.active('default');
                //
                //     MANHUNT.sidebar.menu.object(player.object);
                //
                //     break;
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

        }
    };

    self._init();

    return {
        update: self.update,
        enable: self.enable
    }
};