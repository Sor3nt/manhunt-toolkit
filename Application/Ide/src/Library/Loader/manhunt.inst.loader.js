
MANHUNT.fileLoader.INST = function () {


    var loader = new THREE.FileLoader();
    loader.setResponseType( 'arraybuffer' );

    var map = {
            m_02952f00: 'TYPE',
            m_05098c97: 'LODNEAR',
            m_0c62150c: 'CONE ANGLE',   //(signed int)(v12 * 180.0 / 3.141592741012573))
            // m_15d86efb: '15d86efb',
            m_162691c2: 'EXECUTION TYPE',
            m_19ead097: 'STREAM ID',
            // m_1c98f13d: '1c98f13d',
            // m_21c6e310: '21c6e310',
            m_257857a2: 'SWITCH OFF AFTER DURATION',
            // m_28347608: '28347608',
            // m_29424c42: '29424c42',
            m_2bda3537: 'AI_CHECK_CRAWLSPACE',
            m_2be76c78: 'AI_PISS_HERE',
            m_32a503b7: 'LOCKED',
            m_363d8ec0: 'COLOUR: BLUE',
            m_37459972: 'FADE OUT TIME',
            m_37e5d5b0: 'OBJECT ANIMATION',
            // m_3d4ea211: '3d4ea211',
            // m_3e2bf86c: '3e2bf86c',
            // m_421db23f: '421db23f',
            m_4d0ac9ed: 'FADE IN TIME IN MS',
            m_4ea6c7e9: 'ADJACENTDOOR',
            m_4ecdbb56: 'DETECTION RADIUS IN METRES',
            m_4fe68d23: 'SWITCH ON BY DEFAULT',
            m_51652b86: 'IS STREAMED',
            m_5248fa51: 'TRANSPARENT',
            // m_543cecfb: '543cecfb',
            m_564860cc: 'KICKABLE',
            m_5846b304: 'IS REAL LIGHT',
            m_5c0ac4ce: 'WEAPON2',
            // m_6220faf6: '6220faf6',
            m_63b19fbc: 'PHYSICS',
            m_66ff2476: 'AFFECTS OBJECTS',
            m_6b8d3d10: 'AI_SMOKE_HERE',
            // m_6cb9a0b8: '6cb9a0b8',
            m_7371a36a: 'PLAYERSTART X',
            m_738f4ffa: 'EFFECT DURATION',
            // m_743c962b: '743c962b',
            m_7471a36a: 'PLAYERSTART Y',
            m_7571a36a: 'PLAYERSTART Z',
            // m_775a62a7: '775a62a7',
            m_78565cce: 'VOLUME',
            m_78658efc: 'HAS LENSFLARE',
            // m_79ffe893: '79ffe893',
            m_7b5b3ea1: 'TRIGGER TIMEOUT',
            m_7c1309af: 'USE DEFAULT AI',
            m_7cccb959: 'HUNTERSTART X',
            m_7dccb959: 'HUNTERSTART Y',
            m_7eb4d520: 'DROP_AMMO',
            m_7eccb959: 'HUNTERSTART Z',
            m_7fc8ce3b: 'FLICKER/STROBE OFF TIME IN MS',
            m_8357b601: 'LENSFLARE INTENSITY',
            m_8509822f: 'OCCLUSION IGNORANCE',
            // m_885de242: '885de242',
            m_890948d2: 'LOD_DATA1',
            m_8a0948d2: 'LOD_DATA2',
            m_8b0948d2: 'LOD_DATA3',
            m_8bc3259e: 'EXECUTION OBJECT',
            m_8c0948d2: 'LOD_DATA4',
            m_8c552d2b: 'CUSHIONS',
            m_8f9a5664: 'MATERIAL',
            // m_94af5ed1: '94af5ed1',
            // m_94e706e6: '94e706e6',
            // m_9afa2bb7: '9afa2bb7',
            m_a1903346: 'FLICKER/STROBE ON TIME IN MS',
            m_a706d9b0: 'LIGHT EFFECT TYPE',
            m_a840c3de: 'AI_VENDING_MACHINE',
            m_aa30fb1d: 'AI_NO_ANIM',
            m_aab57d84: 'ANIMATION_BLOCK',
            // m_add18d93: 'add18d93',
            m_b3f90806: 'SLOT1',
            m_b439eab7: 'ATTENUATION RADIUS',
            m_b4f90806: 'SLOT2',
            m_b5f90806: 'SLOT3',
            m_ba69a65d: 'LIGHT TYPE',
            // m_bb6def2c: 'bb6def2c',
            m_bcd42800: 'HP%_',
            m_bd897f86: 'LOCKABLE',
            m_bd8d6c2a: 'BANK NAME',
            m_be38809f: 'BLOCKS',
            // m_bf4d0100: 'bf4d0100',
            m_c3c9378d: 'HUNTERLOOK X',
            m_c5c9378d: 'HUNTERLOOK Z',
            m_cd066eb5: 'CREATES CHARACTER SHADOWS',
            // m_d165ac23: 'd165ac23',
            m_d76afd1a: 'LENSFLARE SIZE',
            m_d790e1e4: 'FADE CONTINOUSLY',
            m_da2b7576: 'PLAYERLOOK X',
            m_dbc52e00: 'SIZE',
            m_dc2b7576: 'PLAYERLOOK Z',
            // m_e1746506: 'e1746506',
            // m_e3746506: 'e3746506',
            m_e8c705c4: 'RADIUS',
            m_e92552f6: 'COLOUR: RED',
            m_ea6cf6cf: 'WEAPON',
            m_ecbdb0d9: 'NOT CLIMBABLE',
            m_ef337ffd: 'HAS SEARCHLIGHT CONE',
            m_f0ef8626: 'SMASHABLE',
            m_f45114fb: 'LIGHT FOG',
            m_f5d32758: 'TRIGGER PROBABILITY',
            m_f6847fd9: 'NAME IN SAMPLEBANK',
            // m_f99bec77: 'f99bec77',
            m_fa04936e: 'AFFECTS MAP',
            m_ff0d4afc: 'DETECTION HEIGHT IN METRES',
            m_ff64b3d2: 'COLOUR: GREEN'

    };
    function buf2hex(buffer) { // buffer is an ArrayBuffer
        return Array.prototype.map.call(new Uint8Array(buffer), x => ('00' + x.toString(16)).slice(-2)).join('');
    }

    function camelName(str){
        str = str.toLowerCase();
        str = str.replace(/:/g, ' ');
        str = str.replace(/_/g, ' ');
        str = str.replace(/\//g, ' ');

        return str.replace(/(?:^\w|[A-Z]|\b\w)/g, function(word, index) {
            return index === 0 ? word.toLowerCase() : word.toUpperCase();
        }).replace(/\s+/g, '');
    }

    return {
        load: function (file, callback ) {

            loader.load(
                file,
                function ( data ) {

                    var binary = new NBinary(data);
                    var placements = binary.consume(4, 'int32');

                    var entityDataSize = [];
                    while(placements--){
                        entityDataSize.push(binary.consume(4, 'int32'));
                    }

                    var results = [];

                    entityDataSize.forEach(function (size, index) {

                        var entryEndOffset = binary.current() + size;
                        var glgRecord = binary.getString(0, true);
                        var internalName = binary.getString(0, true);
                        var position = binary.readXYZ();

                        var posZ = position.z;
                        position.z = position.y * -1;
                        position.y = posZ;

                        var rotation = binary.readXYZW();
                        // posZ = rotation.z;
                        // rotation.z = rotation.y * -1;
                        // rotation.y = posZ;

                        var settings = [];
                        if (binary.current() < entryEndOffset){
                            var entityClass = binary.getString(0, true);

                            while(binary.current() < entryEndOffset){
                                var setting = {};
                                setting.hash = buf2hex(binary.consume(4, 'arraybuffer'));

                                if (typeof map['m_' + setting.hash] !== "undefined"){
                                    setting.name = camelName(map['m_' + setting.hash]);
                                }else{
                                    setting.name = "unk_" + setting.hash;
                                }

                                setting.type = binary.consume(3, 'string');
                                binary.consume(1, 'uint8');

                                if (
                                    setting.name === "colourBlue" ||
                                    setting.name === "colourGreen" ||
                                    setting.name === "colourRed"
                                ){
                                    setting.value = parseInt(binary.consume(4, 'float32') * 255.0);

                                }else{

                                    if (setting.type === "int") {
                                        setting.value = binary.consume(4, 'uint32');
                                    }else if (setting.type === "boo"){
                                        setting.value = binary.consume(4, 'uint32');
                                    }else if (setting.type === "flo"){
                                        setting.value = binary.consume(4, 'float32');
                                    }else if (setting.type === "str"){
                                        setting.value = binary.getString(0, true);
                                    }
                                }


                                settings[setting.name] = setting.value;
                            }
                        }

                        results.push({
                            name: internalName,
                            glgRecord: glgRecord,
                            position: position,
                            rotation: rotation,
                            entityClass: entityClass,
                            settings: settings
                        });

                    });

                    callback(results);

                }
            );

        }
    };

};