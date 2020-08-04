var gtsNotify = function (config) {
    config = config || {};
    gtsNotify.superclass.constructor.call(this, config);
};
Ext.extend(gtsNotify, Ext.Component, {
    page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}, buttons: {}
});
Ext.reg('gtsnotify', gtsNotify);

gtsNotify = new gtsNotify();