
MANHUNT.engine = (function () {

    var self = {

        renderer: {},
        scene: {},
        activeScene: false,
        clock: {},

        container: {},

        init: function () {

            self.container = document.getElementById('webgl');
            //
            // self.addViewport(
            //     document.getElementById('webgl-model'),
            //     'world',
            //     function (delta) {
            //         MANHUNT.camera.update();
            //         MANHUNT.control.update(delta);
            //         MANHUNT.animator.update(delta);
            //
            //     }
            // );
            //
            // self.addViewport(
            //     document.getElementById('webgl-model'),
            //     'world',
            //     function (delta) {
            //         MANHUNT.camera.update();
            //         MANHUNT.control.update(delta);
            //         MANHUNT.animator.update(delta);
            //
            //     }
            // );

            self.renderer = new THREE.WebGLRenderer({antialias: true, alpha: true});
            self.clock = new THREE.Clock();

            MANHUNT.camera.init();

            self.container.appendChild(self.renderer.domElement);
            // window.addEventListener('resize', self._onWindowResize, false);
        },

        addViewport: function(element, name, onUpdate){

            // const {left, right, top, bottom, width, height} =
            //     element.getBoundingClientRect();
            //

            console.log("boound", jQuery(element).width());
            self.scene[name] = {
                active: false,
                scene: new THREE.Scene(),
                element: element,
                onUpdate: onUpdate
            };

        },

        // _onWindowResize: function() {
        //     var width = jQuery(self.container).width();
        //
        //     self.renderer.setSize(width, width);
        // },


        render: function () {
            requestAnimationFrame(self.render);

            var delta = MANHUNT.engine.getClock().getDelta();

            var scene = self.scene[self.activeScene];
            scene.onUpdate(delta);
            self.renderer.render(scene.scene, MANHUNT.camera.getCamera());
        },

        getRenderer: function () {
            return self.renderer;
        },

        getScene: function (name) {
            if (typeof name === "undefined") return self.scene[self.activeScene].scene;
            return self.scene[name].scene;
        },

        changeScene: function(name){
            if (self.activeScene !== false){
                self.scene[self.activeScene].active = false;
            }

            self.activeScene = name;
            var scene = self.scene[name];
            scene.active = true;

            var camera = MANHUNT.camera.getCamera();
            var width = scene.element.getBoundingClientRect().width;
            var height = scene.element.getBoundingClientRect().height;

            camera.aspect = width / height;
            camera.updateProjectionMatrix();

            scene.element.appendChild(self.renderer.domElement);

            self.renderer.setSize(width, height);

        },

        getClock: function () {
            return self.clock;
        }

    };


    return {
        init: self.init,
        container: function () {
            return self.container;
        },
        addViewport: self.addViewport,
        changeScene: self.changeScene,
        getRenderer: self.getRenderer,
        getClock: self.getClock,
        getScene: self.getScene,
        render: self.render
    }
})();