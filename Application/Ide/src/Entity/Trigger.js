MANHUNT.entity.Trigger = function ( entity, callback ) {

    var radius = 0.5;

    entity.settings.forEach(function (setting) {
        if (setting.hash === 3065307){
            radius = setting.value;
        }
    });

    var object = new THREE.Mesh(
        new THREE.SphereGeometry(radius, 32, 32),
        new THREE.MeshBasicMaterial({
            color: 0xffff00,
            opacity: 0.12,
            transparent: true
        })
    );

    object.name = entity.internalName;
    var base = new MANHUNT.entity.abstract(object);
    var self = Object.assign(base, {

    });

    callback(self);
};