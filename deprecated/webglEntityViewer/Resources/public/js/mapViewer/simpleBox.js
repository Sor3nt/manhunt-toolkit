function SimpleBox(width, height, deep) {
    var color = new THREE.Color();

    var self = {
        _boxGeometry: new THREE.BoxBufferGeometry(width, height, deep),

        _boxMaterial: new THREE.MeshPhongMaterial( { specular: 0xffffff, flatShading: true, vertexColors: THREE.VertexColors } ),


        _init : function () {
            var color = new THREE.Color();

            self._boxGeometry = self._boxGeometry.toNonIndexed();

            var position = self._boxGeometry.attributes.position;
            colors = [];

            for ( var i = 0, l = position.count; i < l; i ++ ) {

                color.setHSL( Math.random() * 0.3 + 0.5, 0.75, Math.random() * 0.25 + 0.75 );
                colors.push( color.r, color.g, color.b );

            }

            self._boxGeometry.addAttribute( 'color', new THREE.Float32BufferAttribute( colors, 3 ) );


            self._boxMaterial.color.setHSL( 1,1,1 );

        }

    };

    self._init();

    return function(position, rotation){
        var box = new THREE.Mesh( self._boxGeometry, self._boxMaterial.clone() );
        box.position.x = position.x * 48;
        box.position.y = position.y * 48;
        box.position.z = position.z * 48;

        if (typeof rotation != "undefined"){
            box.rotation.x = rotation.x;
            box.rotation.y = rotation.y;
            box.rotation.z = rotation.z;

        }

        return box;
    };

}