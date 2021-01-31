MANHUNT.resources.Manhunt2 = function (levelName, doneCallback) {

    let base = new MANHUNT.resources.Abstract(levelName, doneCallback);

    let self = Object.assign(base, {

        _game: 'manhunt2',

        _buildChain: function () {

            let loadChain = [
                {
                    order: [
                        {
                            ifp: ['levels/' + levelName + '/allanims_pc.ifp']
                        }
                    ],

                    callback: function () {}
                },
                {
                    order: [
                        {
                            tex: [
                                'global/danny_asylum_bloody_pc.tex',
                                'levels/' + levelName + '/modelspc.tex'
                            ],
                            glg: ['levels/' + levelName + '/resource3.glg'],

                        },
                        {
                            mdl: [
                                'global/danny_asylum_bloody_pc.mdl',
                                'levels/' + levelName + '/modelspc.mdl'
                            ]
                        },
                        {
                            inst: ['levels/' + levelName + '/entity_pc.inst']
                        }

                    ],

                    callback: function () {}
                },

                {
                    order: [
                        {
                            tex: ['levels/' + levelName + '/scene1_pc.tex'],
                        },
                        {
                            bsp: [
                                'levels/' + levelName + '/scene1_pc.bsp',
                                'levels/' + levelName + '/scene2_pc.bsp',
                                'levels/' + levelName + '/scene3_pc.bsp'
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