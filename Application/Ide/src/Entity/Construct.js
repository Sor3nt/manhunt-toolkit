MANHUNT.entity.construct = (function ( ) {

    var self = {
        byInstEntry: function (entry, model) {
            if (typeof model === "undefined"){

                return false;
                // const geometry = new THREE.BoxGeometry( 1 / 48, 1 / 48, 1 / 48 );
                // const material = new THREE.MeshBasicMaterial( {color: 0x00ff00} );
                // model = new THREE.Mesh( geometry, material );
            }

            switch (entry.entityClass) {

                // case 'Trigger_Inst':
                //     return new MANHUNT.entity.Trigger(entry, callback);
                case 'Player_Inst':
                    // var modelObj = model.get();

                    // modelObj.children.forEach(function (child, index) {
                    //     if (index === 0) return;
                        // child.visible = true;
                    // });


                    return new MANHUNT.entity.Player(entry, model);
                case 'Hunter_Inst':
                    return new MANHUNT.entity.Hunter(entry, model);
                case 'Leader_Inst':
                    return false;
                case 'Light_Inst':
                    if (entry.name !== "CJ_LIGHT_on_(L)31") return false;

                    return new MANHUNT.entity.Light(entry, model);
                default:


                    // scene.add( cube );
                    // console.log(entry.entityClass);
                    return new MANHUNT.entity.Default(entry, model);
                    // return new MANHUNT.entity.Dummy(entry, callback);
                    // callback(false);
                    // console.log(
                    //     "[MANHUNT.entity.construct] Unknown class",
                    //     entry.entityClass
                    // );

                    return false;
            }
        }

    };

    return {
        byInstEntry: self.byInstEntry
    };

})();