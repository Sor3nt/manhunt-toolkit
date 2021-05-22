
MANHUNT.converter.pspBsp2mesh = function (level, bsp) {
    var meshRoot = new THREE.Mesh();

console.error("CONVERTT TODO....");
die;
    function parseSelector(selector){
        if (typeof selector.leftSelector !== "undefined")
            parseSelector(selector.leftSelector);

        if (typeof selector.rightSelector !== "undefined")
            parseSelector(selector.rightSelector);

        if (typeof selector.geometry !== "undefined"){

            let vertices = [];
            selector.geometry.vertices.forEach(function (vertex) {
                var geometry = new THREE.Geometry();

                let div = 0;
                switch (vertex.formats.positionFormat) {
                    case 0: break;
                    case 1: div = 32768.0; break;
                    case 2: div = 32768.0; break;
                }

                vertices.push(new THREE.Vector3(
                    vertex.position[0] / div,
                    vertex.position[1] / div,
                    vertex.position[2] / div
                ));
            });



        }

    }

    bsp.selectors.forEach(function (selector) {
        parseSelector(selector);
    });

    return meshRoot;

};
