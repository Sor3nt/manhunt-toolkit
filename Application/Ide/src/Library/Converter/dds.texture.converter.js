
MANHUNT.converter.dds2texture = function (textures, isManhunt2) {

    var ddsLoader = new DDSLoader();
    var result = [];

    textures.forEach(function (texture) {
        var parsed;
        var realTexture;

        if (isManhunt2) {
            parsed = ddsLoader.parse(texture.data);
            realTexture = new THREE.CompressedTexture(parsed.mipmaps, parsed.width, parsed.height);
            realTexture.format = parsed.format;
        }else{
            realTexture = new THREE.DataTexture(texture.data, texture.width, texture.height, texture.format);
        }

        if (realTexture.format === THREE.RGBA_S3TC_DXT5_Format){
            realTexture.magFilter = THREE.LinearFilter;
            realTexture.minFilter = THREE.LinearFilter;
        }

        realTexture.wrapS =  THREE.RepeatWrapping;
        realTexture.wrapT =  THREE.RepeatWrapping;
        realTexture.needsUpdate = true;
        realTexture.name = texture.name;

        result.push(realTexture);

    });

    return result;

};
