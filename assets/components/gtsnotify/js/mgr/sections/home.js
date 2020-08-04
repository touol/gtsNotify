gtsNotify.page.Home = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        components: [{
            xtype: 'gtsnotify-panel-home',
            renderTo: 'gtsnotify-panel-home-div'
        }]
    });
    gtsNotify.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(gtsNotify.page.Home, MODx.Component);
Ext.reg('gtsnotify-page-home', gtsNotify.page.Home);