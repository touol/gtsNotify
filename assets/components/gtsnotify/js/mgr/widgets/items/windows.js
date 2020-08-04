gtsNotify.window.CreateItem = function (config) {
    config = config || {}
    config.url = gtsNotify.config.connector_url

    Ext.applyIf(config, {
        title: _('gtsnotify_item_create'),
        width: 600,
        cls: 'gtsnotify_windows',
        baseParams: {
            action: 'mgr/item/create',
            resource_id: config.resource_id
        }
    })
    gtsNotify.window.CreateItem.superclass.constructor.call(this, config)

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
Ext.extend(gtsNotify.window.CreateItem, gtsNotify.window.Default, {

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
                xtype: 'textarea',
                fieldLabel: _('gtsnotify_item_description'),
                name: 'description',
                id: config.id + '-description',
                height: 150,
                anchor: '99%'
            },  {
                xtype: 'gtsnotify-combo-filter-resource',
                fieldLabel: _('gtsnotify_item_resource_id'),
                name: 'resource_id',
                id: config.id + '-resource_id',
                height: 150,
                anchor: '99%'
            }, {
                xtype: 'xcheckbox',
                boxLabel: _('gtsnotify_item_active'),
                name: 'active',
                id: config.id + '-active',
                checked: true,
            }
        ]


    }
})
Ext.reg('gtsnotify-item-window-create', gtsNotify.window.CreateItem)

gtsNotify.window.UpdateItem = function (config) {
    config = config || {}

    Ext.applyIf(config, {
        title: _('gtsnotify_item_update'),
        baseParams: {
            action: 'mgr/item/update',
            resource_id: config.resource_id
        },
    })
    gtsNotify.window.UpdateItem.superclass.constructor.call(this, config)
}
Ext.extend(gtsNotify.window.UpdateItem, gtsNotify.window.CreateItem)
Ext.reg('gtsnotify-item-window-update', gtsNotify.window.UpdateItem)