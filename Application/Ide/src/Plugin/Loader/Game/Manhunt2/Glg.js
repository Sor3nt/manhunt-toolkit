import AbstractLoader from "./../../Abstract.js";

export default class Glg extends AbstractLoader{
    static name = "GLG";

    static canHandle(binary){
        let current = binary.current();
        let text = binary.toString();
        binary.setCurrent(current);

        text = text.replace(/\#.*/g, '');
        var matches = text.match(/(\#FORCE\n)?RECORD\s(.*\s)*?END/mig);

        return matches !== null && matches.length !== 0;
    }

    static parseRecord( data ){
        let options = [];
        data.forEach(function (singleOption) {

            singleOption = singleOption.trim();

            if (singleOption === "") return;
            if (singleOption.indexOf('#') === 0) return;

            if (singleOption.indexOf(' ') !== -1 || singleOption.indexOf("\t") !== -1){

                singleOption = singleOption.replace("\t", ' ');
                var attrValue = singleOption.split(' ');

                var attr = attrValue[0].trim();
                var value = attrValue[1].trim();

                if (attr === "LOD_DATA"){
                    var vec4 = [];
                    value.split(',').forEach(function (val) {
                        vec4.push( parseInt(val) )
                    });

                    value = new THREE.Vector4(vec4[0],vec4[1],vec4[2],vec4[3]);
                }

                options.push({
                    'attr' : attr,
                    'value' : value
                });

            }else{
                options.push({
                    'attr' : singleOption
                });
            }
        });

        return options;
    }

    static list(binary, options){

        let results = [];

        let text = binary.toString();
        text = text.replace(/\#.*/g, '');
        var matches = text.match(/(\#FORCE\n)?RECORD\s(.*\s)*?END/mig);

        matches.forEach(function (match) {

            match = match.replace(/\r/g, '');
            match = match.substr(7);

            var optionsRaw = match.split("\n");
            var name = optionsRaw[0];

            if (name === "dummy") return;

            delete optionsRaw[0];
            delete optionsRaw[optionsRaw.length - 1];

            let options = Glg.parseRecord(optionsRaw);

            results.push({
                type: Studio.GLG,
                name: name,
                data: function(){
                    return options;
                },
                getValue: function(attr){
                    if (attr === "NAME") return name;

                    var found = false;
                    options.forEach(function (option) {
                        if (option.attr === attr) found = typeof option.value === "undefined" ? true : option.value;
                    });

                    return found;
                },
                getValues: function(attr, index){
                    if (attr === "NAME") return [name];

                    var found = [];
                    options.forEach(function (option) {
                        if (option.attr === attr) found.push(option.value);
                    });

                    if (typeof index !== "undefined") return found[index];

                    return found;
                },
                options: options,
            });

        });

        return results;
    }

}