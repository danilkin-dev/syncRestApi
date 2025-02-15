syncRestApi.panel.Home = function (config) {
    config = config || {};
    Ext.apply(config, {
        baseCls: 'modx-formpanel',
        layout: 'anchor',
        /*
         stateful: true,
         stateId: 'syncrestapi-panel-home',
         stateEvents: ['tabchange'],
         getState:function() {return {activeTab:this.items.indexOf(this.getActiveTab())};},
         */
        hideMode: 'offsets',
        items: [{
            html: '<h2>' + _('syncrestapi') + '</h2>',
            cls: '',
            style: {margin: '15px 0'}
        }, {
            xtype: 'modx-tabs',
            defaults: {border: false, autoHeight: true},
            border: true,
            hideMode: 'offsets',
            items: [{
                title: _('syncrestapi_items'),
                layout: 'anchor',
                items: [{
                    html: _('syncrestapi_intro_msg'),
                    cls: 'panel-desc',
                }, {
                    xtype: 'syncrestapi-grid-items',
                    cls: 'main-wrapper',
                }]
            }]
        }]
    });
    syncRestApi.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(syncRestApi.panel.Home, MODx.Panel);
Ext.reg('syncrestapi-panel-home', syncRestApi.panel.Home);
