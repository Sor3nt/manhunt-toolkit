MANHUNT.resources.mh1.ps2064 = function (gameId, levelName, doneCallback) {
    let base = new MANHUNT.resources.Abstract(levelName, doneCallback);

    let self = Object.assign(base, {

        _game: 'mh1',
        _platform: 'ps2064',
        _gameId: gameId,

        _buildChain: function () {

            let loadChain = [
                {
                    order: [
                        {
                            ifp: ['LEVELS/' + levelName + '/ALLANIMS.IFP']
                        }
                    ],

                    callback: function () {}

                },
                {
                    order: [
                        {
                            tex: [
                                'GLOBAL/GMODELS.TXD',
                                'LEVELS/' + levelName + '/MODELS.TXD',
                                'LEVELS/' + levelName + '/PICMAP.TXD',
                                // 'levels/' + levelName + '/picmmap.txd'
                            ],

                            glg: ['LEVELS/' + levelName + '/ENTTDATA.INI'],

                        },
                        {
                            mdl: [
                                'GLOBAL/GMODELS.DFF',
                                'LEVELS/' + levelName + '/MODELS.DFF'
                            ]
                        },
                        {
                            inst: [
                                'LEVELS/' + levelName + '/ENTINST.BIN'
                            ]
                        }

                    ],

                    callback: function () {}
                },

                {
                    order: [
                        {
                            tex: ['LEVELS/' + levelName + '/SCENE1.TXD'],
                        },
                        {
                            bsp: [
                                'LEVELS/' + levelName + '/SCENE1.BSP',
                                // 'levels/' + levelName + '/scene2.bsp',
                            ]
                        }

                    ],

                    callback: function () {}
                }
            ];

            self._processChain(loadChain);
        }
    });

    self._init();

    return {
    }
};