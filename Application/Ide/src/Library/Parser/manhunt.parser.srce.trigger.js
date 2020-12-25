MANHUNT.parser.srce = MANHUNT.parser.srce || {};
MANHUNT.parser.srce.trigger = function (srce, callback) {

    function getSphereTrigger(tokens, current) {

        var result = {
            type: 'sphere'
        };

        var start = current;

        current++;
        var posVar = false;
        posVar = tokens[current];
        current++;

        result.radius = parseFloat(tokens[current]);
        current++;
        result.name = tokens[current].replace(/\'/g, '');

        current = start - 1;
        while (current > 0) {

            if (tokens[current] === posVar && tokens[current - 1] === "setvector") {
                result.position = {
                    x: parseFloat(tokens[current + 1]),
                    y: parseFloat(tokens[current + 2]),
                    z: parseFloat(tokens[current + 3])
                };

                return result;
            }

            current--;

        }

        return false;

    }

    function getBoxTrigger(tokens, current) {

        var result = {
            type: 'box',
            position: false,
            position2: false,
        };

        var start = current;
        current++;

        var posVar = tokens[current];
        current++;

        var posVar2 = tokens[current];
        current++;

        result.name = tokens[current].replace(/\'/g, '');

        current = start - 1;
        while (current > 0 && (result.position === false || result.position2 === false)) {

            if (tokens[current] === posVar && tokens[current - 1] === "setvector") {
                result.position = {
                    x: parseFloat(tokens[current + 1]),
                    y: parseFloat(tokens[current + 2]),
                    z: parseFloat(tokens[current + 3])
                };

            }

            if (tokens[current] === posVar2 && tokens[current - 1] === "setvector") {
                result.position2 = {
                    x: parseFloat(tokens[current + 1]),
                    y: parseFloat(tokens[current + 2]),
                    z: parseFloat(tokens[current + 3])
                };
            }

            current--;

        }

        return result;

    }

    srce = srce.toLowerCase().replace(/\(/g, ' ');
    srce = srce.replace(/\)/g, ' ');
    srce = srce.replace(/,/g, ' ');
    var tokens = srce.match(/([^\s]+)/g);

    var current = 0;

    var triggers = [];
    while (tokens.length > current) {


        if (tokens[current] === "createspheretrigger") {

            var sphereTrigger = getSphereTrigger(tokens, current);
            triggers.push(sphereTrigger);
            // console.log(sphereTrigger);
        } else if (tokens[current] === "createboxtrigger") {

            var boxTrigger = getBoxTrigger(tokens, current);
            triggers.push(boxTrigger);

        }

        current++;
    }

    return triggers;
};