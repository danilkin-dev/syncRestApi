syncRestApi.page.Home = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        components: [{
            xtype: 'syncrestapi-panel-home',
            renderTo: 'syncrestapi-panel-home-div'
        }]
    });
    syncRestApi.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(syncRestApi.page.Home, MODx.Component);
Ext.reg('syncrestapi-page-home', syncRestApi.page.Home);