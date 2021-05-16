window.RW = {
    parser: {},
    convert: {},
};

Renderware = (function () {

    var self = {
        getMap: function(nBinary, level){
            nBinary.setCurrent(0);

            let tree = RW.parser(nBinary).parse();
            return RW.convert.map(tree, level);
        },

        getModel: function (nBinary, offset) {
            nBinary.setCurrent(offset);

            let tree = RW.parser(nBinary).parse();
            return RW.convert.model(tree);
        }
    };

    return {
        getMap: self.getMap,
        getModel: self.getModel
    }

})();
