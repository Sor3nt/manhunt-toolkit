RW = {
    parser: {},
    convert: {},
};

Renderware = (function () {

    var self = {
        parse: function(nBinary, level){
            nBinary.setCurrent(0);
            return RW.parser(nBinary).parse();

        },

        getMap: function(nBinary, level){
            nBinary.setCurrent(0);

            let tree = RenderwareNew.parse(nBinary);
            let normalizedMesh = (new NormalizeMap(tree)).normalize();
            normalizedMesh.name = "TODO";

            let mesh = generateMesh(level._storage.tex, normalizedMesh);
            mesh.children.forEach(function (subMesh) {
                subMesh.visible = true;
            });

            return mesh;
        },

        getAnimation: function(nBinary, level){
            nBinary.setCurrent(0);

            let tree = RW.parser(nBinary).parse();
            return RW.convert.animation(tree, level);
        },

        getModel: function (nBinary, offset) {
            nBinary.setCurrent(offset);
            let oldTree = RW.parser(nBinary).parse();
            return RW.convert.model(oldTree);

            let tree = RenderwareNew.parse(nBinary);
            let normalizedMesh = (new NormalizeModel(tree)).normalize();
            return normalizedMesh;

        }
    };

    return {
        parse: self.parse,
        getAnimation: self.getAnimation,
        getMap: self.getMap,
        getModel: self.getModel
    }

})();
