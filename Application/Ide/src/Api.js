export default class Api{
    
    static loader = new THREE.FileLoader();


    static load(gameId, file, callback) {
        Api.request( {
            action: 'read',
            gameId: gameId,
            file: file
        }, function (data) {
            console.log(file);
            callback(data);
        });
    }

    static  request( json, callback){
        let oReq = new XMLHttpRequest();
        oReq.open("POST", "/php/api.php", true);
        oReq.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
        oReq.responseType = "arraybuffer";

        oReq.onload = function() {
            callback(oReq.response);
        };

        oReq.send(JSON.stringify(json) );
    }

    static getLevelList(gameId, callback) {

        Api.text( {
            action: 'getLevels',
            id: gameId
        }, callback);
    }

    static getConfig(callback) {

        Api.text( {
            action: 'getConfig'
        }, callback);
    }

    static addGame(folder, callback) {

        Api.text( {
            action: 'addGame',
            data: folder
        }, callback);
    }

    static text(data, callback) {
        Api.request( data, function (data) {
            let text = JSON.parse( (new NBinary(data)).toString() );
            callback && callback(text);
        });
    }

}