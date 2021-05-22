MANHUNT.resources.mh2.psp001 = function (gameId, levelName, doneCallback) {

    let base = new MANHUNT.resources.Abstract(levelName, doneCallback);

    let self = Object.assign(base, {

        _game: 'mh2',
        _platform: 'psp001',
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
                                'GLOBAL/BROKEN.TXD',
                                'LEVELS/' + levelName + '/MODELS.TXD'
                            ],
                            glg: ['LEVELS/' + levelName + '/ENTTDATA.INI'],

                        },
                        {
                            mdl: [
                                'GLOBAL/BROKEN.DFF',
                                // 'LEVELS/' + levelName + '/MODELS.DFF'
                            ]
                        },
                        {
                            inst: ['LEVELS/' + levelName + '/ENTINST.BIN']
                        }

                    ],

                    callback: function () {}
                },
                //
                {
                    order: [
                        {
                            tex: ['LEVELS/' + levelName + '/SCENE1.TXD'],
                        },
                        {
                            bsp: [
                                'LEVELS/' + levelName + '/SCENE1.BSP',
                                'LEVELS/' + levelName + '/SCENE2.BSP',
                                'LEVELS/' + levelName + '/SCENE3.BSP'
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