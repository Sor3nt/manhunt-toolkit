export default class Tab{

    constructor(tabListContainer, tabContentContainer) {
        this.element = {};
        this.template = {};
        this.tab2Content = {};
        this.activeTab = false;

        this.element.tab = tabListContainer;
        this.element.content = tabContentContainer;
        this.template.tab = document.querySelector('#tab-list-entry');
    }

    add(name, content, closeCallback, focusCallback, blurCallback, labelName) {
        if (typeof this.tab2Content[name] !== "undefined"){
            console.log('[MANHUNT.frontend.tab] Unable to add tab', name, 'already added?!' );
            return;
        }

        let row = jQuery(this.template.tab.content).clone();
        this.element.tab.append(row);
        row = this.element.tab.find('>li:last-child');

        let _this = this;
        row.find('[data-field="name"]')
            .click(function () {
                _this.show(name);
            })
            .html(labelName || name);

        row.find('button').click(function () {
            row.remove();
            closeCallback();
        });

        this.tab2Content[name] = {
            content: content,
            tab: row,
            focusCallback: focusCallback,
            blurCallback: blurCallback
        };

        this.element.content.append(content);
    }

    addContent(element){
        this.element.content.append(element);
        return element;
    }

    remove(name){
        if (typeof this.tab2Content[name] === "undefined"){
            console.error('[MANHUNT.frontend.tab] Unable to remove tab', name );
            return;
        }

        this.tab2Content[name].tab.remove();
        this.tab2Content[name] = undefined;
    }

    show(name) {
        if (typeof this.tab2Content[name] === "undefined"){
            return;
        }

        if (this.activeTab !== false){
            this.activeTab.content.hide();
            this.activeTab.tab.find("a").removeClass('active');
            this.activeTab.blurCallback();
        }

        this.activeTab = this.tab2Content[name];
        this.activeTab.content.show();
        this.activeTab.tab.find("a").addClass('active');
        this.activeTab.focusCallback();
    }

    get(name) {
        return this.tab2Content[name];
    }


}
