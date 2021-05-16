window.RW = {
    parser: {},
    convert: {},
};

Renderware = (function () {

    var self = {
        getModel: function (nBinary, offset) {
            nBinary.setCurrent(offset);

            let tree = RW.parser(nBinary).parse();
            return RW.convert.model(tree);

        }
    };

    return {
        getModel: self.getModel
    }

})();
