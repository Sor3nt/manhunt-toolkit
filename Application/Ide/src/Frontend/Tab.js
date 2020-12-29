MANHUNT.frontend.tab = (function () {

    var self = {

        _element: {},
        _template: {},
        _tab2Content: {},
        _activeTab: false,

        init: function () {
            self._element.tab = jQuery('#tab-list');
            self._element.content = jQuery('#tab-content');
            self._template.tab = document.querySelector('#tab-list-entry');
        },

        add: function (name, content, closeCallback, focusCallback, blurCallback) {
            if (typeof self._tab2Content[name] !== "undefined"){
                console.log('[MANHUNT.frontend.tab] Unable to add tab', name, 'already added?!' );
                return;
            }
            var row = jQuery(self._template.tab.content).clone();
            self._element.tab.append(row);
            row = self._element.tab.find('>li:last-child');

            row.find('[data-field="name"]')
                .click(function () {
                    self.show(name);
                })
                .html(name);

            row.find('button').click(function () {
                row.remove();
                closeCallback();
            });

            self._tab2Content[name] = {
                content: content,
                tab: row,
                focusCallback: focusCallback,
                blurCallback: blurCallback
            };
            self._element.content.append(content);
        },

        show: function (name) {
            if (typeof self._tab2Content[name] === "undefined"){
                console.log('[MANHUNT.frontend.tab] Unable to find tab', name );
                return;
            }

            if (self._activeTab !== false){
                self._activeTab.content.hide();
                self._activeTab.tab.find("a").removeClass('active');
                self._activeTab.blurCallback();
            }

            self._activeTab = self._tab2Content[name];
            self._activeTab.content.show();
            self._activeTab.tab.find("a").addClass('active');
            self._activeTab.focusCallback();
        }

    };


    return {
        init: self.init,
        show: self.show,
        add: self.add
    }
})();