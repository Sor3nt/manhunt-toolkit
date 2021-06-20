MANHUNT.resources.mh1.pc = function (gameId, levelName, doneCallback) {
    let base = new MANHUNT.resources.Abstract(levelName, doneCallback);

    let self = Object.assign(base, {

        _game: 'mh1',
        _platform: 'pc',
        _gameId: gameId,

        _buildChain: function () {

            let loadChain = [
                // {
                //     order: [
                //         {
                //             mdl: [
                //
                //                 'levels/' + levelName + '/boss00_clean.dff',
                //
                //             ],
                //         }
                //     ],
                //
                //     callback: function () {}
                //
                // },
                {
                    order: [
                        {
                            ifp: ['levels/' + levelName + '/allanims.ifp']
                        }
                    ],

                    callback: function () {}

                },
                {
                    order: [
                        {
                            tex: [
                                'levels/GLOBAL/CHARPAK/cash_pc.txd',
                                'levels/' + levelName + '/pak/modelspc.txd',
                                // 'levels/' + levelName + '/picmap.txd',
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

                    callback: function () {}
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