gtsNotify.window.CreateProvider = function (config) {
    config = config || {}
    config.url = gtsNotify.config.connector_url

    Ext.applyIf(config, {
        title: _('gtsnotify_item_create'),
        width: 600,
        cls: 'gtsnotify_windows',
        baseParams: {
            action: 'mgr/provider/create',
            resource_id: config.resource_id
        }
    })
    gtsNotify.window.CreateProvider.superclass.constructor.call(this, config)

    this.on('success', function (data) {
        if (data.a.result.object) {
            // Авто запуск при создании новой подписик
            if (data.a.result.object.mode) {
                if (data.a.result.object.mode === 'new') {
                    var grid = Ext.getCmp('gtsnotify-grid-items')
                    grid.updateItem(grid, '', {data: data.a.result.object})
                }
            }
        }
    }, this)
}
Ext.extend(gtsNotify.window.CreateProvider, gtsNotify.window.Default, {

    getFields: function (config) {
        return [
            {xtype: 'hidden', name: 'id', id: config.id + '-id'},
            {
                xtype: 'textfield',
                fieldLabel: _('gtsnotify_item_name'),
                name: 'name',
                id: config.id + '-name',
                anchor: '99%',
                allowBlank: false,
            }, {
                xtype: 'textfield',
                fieldLabel: _('gtsnotify_provider_class'),
                name: 'class',
                id: config.id + '-class',
                anchor: '99%',
                allowBlank: false,
            }, {
                xtype: 'textfield',
                fieldLabel: _('gtsnotify_provider_path'),
                name: 'path',
                id: config.id + '-path',
                anchor: '99%',
                allowBlank: false,
            }, {
                xtype: 'textfield',
                fieldLabel: _('gtsnotify_provider_ws_address'),
                name: 'ws_address',
                id: config.id + '-ws_address',
                anchor: '99%',
                allowBlank: false,
            }, {
                xtype: 'textfield',
                fieldLabel: _('gtsnotify_provider_secret_key'),
                name: 'secret_key',
                id: config.id + '-secret_key',
                anchor: '99%',
                allowBlank: false,
            }, {
                xtype: 'textfield',
                fieldLabel: _('gtsnotify_provider_host'),
                name: 'host',
                id: config.id + '-host',
                anchor: '99%',
                allowBlank: false,
            }, {
                xtype: 'textarea',
                fieldLabel: _('gtsnotify_item_description'),
                name: 'description',
                id: config.id + '-description',
                height: 150,
                anchor: '99%'
            },  {
                xtype: 'xcheckbox',
                boxLabel: _('gtsnotify_item_active'),
                name: 'active',
                id: config.id + '-active',
                checked: true,
            }
        ]


    }
})
Ext.reg('gtsnotify-provider-window-create', gtsNotify.window.CreateProvider)

gtsNotify.window.UpdateProvider = function (config) {
    config = config || {}

    Ext.applyIf(config, {
        title: _('gtsnotify_item_update'),
        baseParams: {
            action: 'mgr/provider/update',
            resource_id: config.resource_id
        },
    })
    gtsNotify.window.UpdateProvider.superclass.constructor.call(this, config)
}
Ext.extend(gtsNotify.window.UpdateProvider, gtsNotify.window.CreateProvider)
Ext.reg('gtsnotify-provider-window-update', gtsNotify.window.UpdateProvider)