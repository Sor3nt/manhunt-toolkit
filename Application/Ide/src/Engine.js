
MANHUNT.engine = (function () {

    var self = {

        renderer: {},
        sceneInfos: {},
        activeScene: false,
        clock: {},

        container: {},

        init: function () {

            self.container = document.getElementById('webgl');
            self.renderer = new THREE.WebGLRenderer({antialias: true, alpha: true});
            self.clock = new THREE.Clock();

            self.container.appendChild(self.renderer.domElement);
            window.addEventListener('resize', self.resize, false);
        },

        createSceneInfo: function(element, name, camera, Control, onCreate, onUpdate, renderer){

            self.sceneInfos[name] = {
                scene: new THREE.Scene(),
                element: element.get(0),
                camera: camera,
                onUpdate: onUpdate,
                lookAt: null,
                renderer: renderer,
                renderOnlyOnce: false
            };

            if (Control !== null)
                self.sceneInfos[name].control = new Control(self.sceneInfos[name]);

            onCreate(self.sceneInfos[name]);

            return self.sceneInfos[name];
        },

        resize: function(){
            var sceneInfo = self.getSceneInfo();

            var bbox = sceneInfo.element.getBoundingClientRect();

            sceneInfo.camera.aspect = bbox.width / bbox.height;
            sceneInfo.camera.updateProjectionMatrix();

            if (typeof sceneInfo.renderer === "undefined"){
                sceneInfo.element.appendChild(self.renderer.domElement);
                self.renderer.setSize(bbox.width, bbox.height);
            }else{
                sceneInfo.renderer.setSize(bbox.width, bbox.height);
            }
        },

        render: function () {

            requestAnimationFrame(self.render);

            if (self.activeScene === false) return;

            var delta = MANHUNT.engine.getClock().getDelta();

            var scene = self.sceneInfos[self.activeScene];
            scene.onUpdate(scene, delta);

            if (typeof scene.renderer === "undefined") {
                self.renderer.render(scene.scene, scene.camera);
            }else{
                scene.renderer.render(scene.scene, scene.camera);
            }
        },

        getScene: function (name) {
            if (typeof name === "undefined") return self.sceneInfos[self.activeScene].scene;
            return self.sceneInfos[name].scene;
        },

        getSceneInfo: function (name) {
            if (typeof name === "undefined") return self.sceneInfos[self.activeScene];
            return self.sceneInfos[name];
        },

        changeScene: function(name){
            self.activeScene = name;
            self.sceneInfos[name].active = true;
            self.resize();
        },
        
        getRenderer: function (){ return self.renderer; },
        getClock: function () { return self.clock; }
    };

    return {
        init: self.init,
        container: function () { return self.container; },
        createSceneInfo: self.createSceneInfo,
        changeScene: self.changeScene,
        getRenderer: self.getRenderer,
        getClock: self.getClock,
        getScene: self.getScene,
        getSceneInfo: self.getSceneInfo,
        render: self.render
    }
})();