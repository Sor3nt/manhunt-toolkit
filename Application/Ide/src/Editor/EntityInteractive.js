MANHUNT.entityInteractive = (function () {

    var self = {

        init: function () {
            MANHUNT.engine.getRenderer().domElement.addEventListener( 'click', self._onClick, true );
        },

        _onClick: function ( event ) {

            if (MANHUNT.control.active() === "transform") return;

            var camera = MANHUNT.camera.getCamera();
            var domElement = MANHUNT.engine.getRenderer().domElement;
            var scene = MANHUNT.engine.getScene('world');

            var _raycaster = new THREE.Raycaster();
            var _mouse = new THREE.Vector2();

            var rect = domElement.getBoundingClientRect();

            _mouse.x = ( ( event.clientX - rect.left ) / rect.width ) * 2 - 1;
            _mouse.y = - ( ( event.clientY - rect.top ) / rect.height ) * 2 + 1;

            _raycaster.setFromCamera( _mouse, camera );

            var intersects = _raycaster.intersectObjects( scene.children );

            if (intersects.length === 1){
                console.log('[MANHUNT.entityInteractive] Object clicked', intersects[0].object);
                MANHUNT.camera.lookAt(intersects[0].object);
                MANHUNT.control.active('transform');
                MANHUNT.sidebar.menu.object(intersects[0].object);
            }
        }
    };

    return {
        init: self.init,
    }
})();