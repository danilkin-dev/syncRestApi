var syncRestApi = function (config) {
    config = config || {};
    syncRestApi.superclass.constructor.call(this, config);
};
Ext.extend(syncRestApi, Ext.Component, {
    page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}
});
Ext.reg('syncrestapi', syncRestApi);

syncRestApi = new syncRestApi();