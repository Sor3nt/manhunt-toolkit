MANHUNT.scene.textureView = function (canvas) {

    var self = {

        _name : 'textureView',

        _camera: new THREE.PerspectiveCamera( 35, 1, 1, 100 ),

        _sceneInfo : {},
        _outputCube : {},

        _init: function(){
            self._sceneInfo = MANHUNT.engine.createSceneInfo(
                canvas,
                self._name,
                self._camera,
                null,
                self._onCreate,
                self._onUpdate,
                new THREE.WebGLRenderer({antialias: true, alpha: true})
            );

            self._sceneInfo.renderOnlyOnce = true;

            canvas.append(self._sceneInfo.renderer.domElement);

        },

        _onCreate: function (sceneInfo) {

            sceneInfo.camera.position.set(0, 0, -100);
            sceneInfo.camera.lookAt(sceneInfo.scene.position);

            // sceneInfo.scene.add(new THREE.HemisphereLight(0xffffff, 0x444444));
            sceneInfo.scene.add(new THREE.HemisphereLight(0xffffff, 0xffffff));

            var cubeGeometry = new THREE.CubeGeometry( 50, 50, 50 );
            //Note: we need to flip the UVs because DDS are stored bottom to top
            cubeGeometry.faceVertexUvs[0].forEach(function (uv, index) {
                for(var i = 0; i < 3; i++){
                    cubeGeometry.faceVertexUvs[0][index][i].y *= -1;
                }
            });
            cubeGeometry.needsUpdate = true;

            // var materialRed = new THREE.MeshLambertMaterial({ color: 0xdd0000, overdraw: true });
            self._outputCube = new THREE.Mesh( cubeGeometry );
            self._outputCube.visible = false;

            sceneInfo.scene.add(self._outputCube);
        },

        _resize: function( width, height ){
            self._sceneInfo.camera.aspect = width / height;
            self._sceneInfo.camera.updateProjectionMatrix();
            self._sceneInfo.renderer.setSize(width, height);
        },

        display: function( texture ){
            self._resize(texture.image.width, texture.image.height);

            var mat = new THREE.MeshStandardMaterial();
            mat.name = 'displayTexture';
            mat.map = texture;
            mat.transparent = texture.format === THREE.RGBA_S3TC_DXT5_Format;

            self._outputCube.material = mat;
            self._outputCube.visible = true;
        },

        _onUpdate: function (sceneInfo, delta) {},
    };

    self._init();

    return {
        display: self.display,
    }
};