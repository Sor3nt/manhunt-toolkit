MANHUNT.fileLoader.GLG = function () {
    function convertRecords( text ){

        var result = [];


        var matches = text.match(/(\#FORCE\n)?RECORD\s(.*\s)*?END/mig);

        matches.forEach(function (match) {

            match = match.substr(7);

            var optionsRaw = match.split("\n");
            var name = optionsRaw[0];
            delete optionsRaw[0];
            delete optionsRaw[optionsRaw.length - 1];

            var options = [];
            optionsRaw.forEach(function (singleOption) {

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



            result.push({
                name: name,
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

        return result;
    }

    var loader = new THREE.FileLoader();

    return {
        load: function (file, callback) {

            loader.load(
                file,
                function ( data ) {

                    callback(convertRecords(data));

                }
            );

        }
    };

};