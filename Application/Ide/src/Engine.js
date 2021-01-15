
MANHUNT.engine = (function () {

    var self = {

        renderer: {},
        scene: {},
        activeScene: false,
        clock: {},

        container: {},

        init: function () {

            self.container = document.getElementById('webgl');
            self.renderer = new THREE.WebGLRenderer({antialias: true, alpha: true});
            self.clock = new THREE.Clock();

            // MANHUNT.camera.init();

            self.container.appendChild(self.renderer.domElement);
            // window.addEventListener('resize', self._onWindowResize, false);
        },

        createSceneInfo: function(element, name, camera, Control, onCreate, onUpdate, renderer){

            // const {left, right, top, bottom, width, height} =
            //     element.getBoundingClientRect();
            //

            self.scene[name] = {
                // active: false,
                scene: new THREE.Scene(),
                element: element.get(0),
                camera: camera,
                onUpdate: onUpdate,
                lookAt: null,
                renderer: renderer,
                renderOnlyOnce: false
            };

            if (Control !== null)
                self.scene[name].control = new Control(self.scene[name]);

            onCreate(self.scene[name]);

            return self.scene[name];
        },

        // _onWindowResize: function() {
        //     var width = jQuery(self.container).width();
        //
        //     self.renderer.setSize(width, width);
        // },


        render: function () {

            // if (self.activeScene.renderOnlyOnce === false)
                requestAnimationFrame(self.render);

            if (self.activeScene === false) return;

            var delta = MANHUNT.engine.getClock().getDelta();

            var scene = self.scene[self.activeScene];
            scene.onUpdate(scene, delta);

            if (typeof scene.renderer === "undefined") {
                self.renderer.render(scene.scene, scene.camera);
            }else{
                scene.renderer.render(scene.scene, scene.camera);
            }
        },

        getRenderer: function () {
            return self.renderer;
        },

        getScene: function (name) {
            if (typeof name === "undefined") return self.scene[self.activeScene].scene;
            return self.scene[name].scene;
        },

        getSceneInfo: function (name) {
            if (typeof name === "undefined") return self.scene[self.activeScene];
            return self.scene[name];
        },

        changeScene: function(name){
            if (self.activeScene !== false){
                // self.scene[self.activeScene].active = false;
            }
console.log("CHANGE TO ", name);
            self.activeScene = name;
            var scene = self.scene[name];
            scene.active = true;

            console.log("CHANGE TO scene ", scene);
            var width = scene.element.getBoundingClientRect().width;
            var height = scene.element.getBoundingClientRect().height;

            scene.camera.aspect = width / height;
            scene.camera.updateProjectionMatrix();

            if (typeof scene.renderer === "undefined"){
                scene.element.appendChild(self.renderer.domElement);
                self.renderer.setSize(width, height);
            }else{
                scene.renderer.setSize(width, height);
            }


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
        createSceneInfo: self.createSceneInfo,
        changeScene: self.changeScene,
        getRenderer: self.getRenderer,
        getClock: self.getClock,
        getScene: self.getScene,
        getSceneInfo: self.getSceneInfo,
        render: self.render
    }
})();