MANHUNT.fileLoader.TVP = function () {
    function parseTVPLine(str){
        var parts = str.replace(/\t/g, ' ')
            .replace(/  /g, ' ')
            .split(" ");

        var dur = parseFloat(parts[1]);

        if (dur !== 0.0){
            dur -= 0.01666666753590107; //exe 0x5CB40A
        }

        return {
            type: parts[0],
            dur: dur,
            posX: parseFloat(parts[2]),
            posZ: parseFloat(parts[3]),
            posY: parseFloat(parts[4]),

            lokX: parseFloat(parts[5]),
            lokZ: parseFloat(parts[6]),
            lokY: parseFloat(parts[7]),

            thr: parseFloat(parts[8]),
            rol: parseFloat(parts[9])
        };

    }

    var loader = new THREE.FileLoader();

    return {
        load: function (file, callback) {

            loader.load(
                file,
                function ( data ) {
                    var results = {};

                    var lines = data.split("\n");

                    var weapon = false;

                    lines.forEach(function (line) {
                        line = line.trim();
                        if (line === "END") return;
                        if (line === "") return;

                        if (line.indexOf('RECORD') !== -1){

                            weapon = line.replace('RECORD', '').trim();
                            weapon = weapon.split("#")[0];

                            results[weapon] = [];

                        }else{
                            results[weapon].push(parseTVPLine(line));
                        }

                    });

                    callback(results);

                }
            );

        }
    };

};