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

            let tree = RW.parser(nBinary).parse();
            return RW.convert.map(tree, level);
        },

        getAnimation: function(nBinary, level){
            nBinary.setCurrent(0);

            let tree = RW.parser(nBinary).parse();
            return RW.convert.animation(tree, level);
        },

        getModel: function (nBinary, offset) {
            nBinary.setCurrent(offset);

            let tree = RW.parser(nBinary).parse();
            return RW.convert.model(tree);
        }
    };

    return {
        parse: self.parse,
        getAnimation: self.getAnimation,
        getMap: self.getMap,
        getModel: self.getModel
    }

})();
