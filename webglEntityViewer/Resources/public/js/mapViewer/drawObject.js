function DrawObject( environment, controls ) {
    var objLoader = new THREE.OBJLoader();

    return function (filename, rotatePosition) {
        objLoader.load(

            filename,

            function ( object ) {
//

                if (typeof rotatePosition != "undefined"){
                    //apply position
                    if (rotatePosition.x) object.position.x = rotatePosition.x;
                    if (rotatePosition.y) object.position.y = rotatePosition.y;
                    if (rotatePosition.z) object.position.z = rotatePosition.z;

                    //apply rotation
                    if (rotatePosition.ry) object.rotation.y = rotatePosition.ry *(Math.PI/180);
                    if (rotatePosition.rx) object.rotation.x = rotatePosition.rx *(Math.PI/180);
                    if (rotatePosition.rz) object.rotation.z = rotatePosition.rz *(Math.PI/180);
                    // if (rotatePosition.rw) object.rotation.y = rotatePosition.rw *(Math.PI/180);
                }

                object.scale.set(environment.worldScale, environment.worldScale, environment.worldScale);

                environment.scene.add( object );
            },

            function ( xhr ) { },

            function ( error ) {
                console.log("Unable to load " + filename + ": " + error);
            }
        );

    };

}