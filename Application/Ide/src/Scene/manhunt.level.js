MANHUNT.scene.ManhuntLevel = function (levelName, doneCallback) {

    let base = new MANHUNT.scene.AbstractLevel(levelName, doneCallback);

    let self = Object.assign(base, {

        _game: 'manhunt',

        _onCreate: function () {

            let loadChain = [
                {
                    order: [
                        {
                            ifp: ['levels/' + levelName + '/allanims.ifp']
                        }
                    ],

                    callback: function () {

                    }
                },
                {
                    order: [
                        {
                            tex: [
                                'levels/GLOBAL/CHARPAK/cash_pc.txd',
                                'levels/' + levelName + '/pak/modelspc.txd',
                                'levels/' + levelName + '/picmap.txd',
                                // 'levels/' + levelName + '/picmmap.txd'
                            ],

                            glg: ['levels/GLOBAL/DATA/ManHunt.pak#./levels/' + levelName + '/entityTypeData.ini'],

                        },
                        {
                            mdl: [
                                'levels/GLOBAL/CHARPAK/cash_pc.dff',
                                'levels/' + levelName + '/pak/modelspc.dff'
                            ]
                        },
                        {
                            inst: [
                                'levels/' + levelName + '/entity.inst',
                                'levels/' + levelName + '/entity2.inst'
                            ]
                        }

                    ],

                    callback: self._createModels
                },

                {
                    order: [
                        {
                            tex: ['levels/' + levelName + '/pak/scene1pc.txd'],
                        },
                        {
                            bsp: [
                                'levels/' + levelName + '/scene1.bsp',
                                // 'levels/' + levelName + '/scene2.bsp',
                            ]
                        }

                    ],

                    callback: self._createMap
                }
            ];

            self._processChain(loadChain);
        }
    });

    self._init();

    return {
        addScene: self.addScene
    }
};