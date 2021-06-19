
MANHUNT.converter.ps22texture = function (textures) {

    var ddsLoader = new DDSLoader();
    var result = [];

    textures.forEach(function (texture) {
        var realTexture;
        realTexture = new THREE.DataTexture(texture.data, texture.width, texture.height, texture.format);

        realTexture.wrapS =  THREE.RepeatWrapping;
        realTexture.wrapT =  THREE.RepeatWrapping;
        realTexture.needsUpdate = true;
        realTexture.name = texture.name;
        result.push(realTexture);

    });

    return result;

};
