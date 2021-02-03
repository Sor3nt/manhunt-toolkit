MANHUNT.frontend.Tab = function (tabListContainer, tabContentContainer) {

    let self = {

        _element: {},
        _template: {},
        _tab2Content: {},
        _activeTab: false,

        _init: function () {
            self._element.tab = tabListContainer;
            self._element.content = tabContentContainer;
            self._template.tab = document.querySelector('#tab-list-entry');
        },

        add: function (name, content, closeCallback, focusCallback, blurCallback, labelName) {
            if (typeof self._tab2Content[name] !== "undefined"){
                console.log('[MANHUNT.frontend.tab] Unable to add tab', name, 'already added?!' );
                return;
            }

            let row = jQuery(self._template.tab.content).clone();
            self._element.tab.append(row);
            row = self._element.tab.find('>li:last-child');

            row.find('[data-field="name"]')
                .click(function () {
                    self.show(name);
                })
                .html(labelName || name);

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

        addContent: function(element){
            self._element.content.append(element);
            return element;
        },

        remove: function(name){
            if (typeof self._tab2Content[name] === "undefined") return;

            self._tab2Content[name].tab.remove();
            self._tab2Content[name] = undefined;
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
        },

        get: function (name) {
            return self._tab2Content[name];
        }

    };

    self._init();


    return {
        addContent: self.addContent,
        get: self.get,
        show: self.show,
        remove: self.remove,
        add: self.add
    }
};