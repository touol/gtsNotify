gtsNotify.window.CreateChannel = function (config) {
    config = config || {}
    config.url = gtsNotify.config.connector_url

    Ext.applyIf(config, {
        title: _('gtsnotify_item_create'),
        width: 600,
        cls: 'gtsnotify_windows',
        baseParams: {
            action: 'mgr/channel/create',
            resource_id: config.resource_id
        }
    })
    gtsNotify.window.CreateChannel.superclass.constructor.call(this, config)

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
Ext.extend(gtsNotify.window.CreateChannel, gtsNotify.window.Default, {

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
                fieldLabel: _('gtsnotify_channel_icon'),
                name: 'icon',
                id: config.id + '-icon',
                anchor: '99%',
                allowBlank: false,
            }, {
                xtype: 'textfield',
                fieldLabel: _('gtsnotify_channel_icon_empty'),
                name: 'icon_empty',
                id: config.id + '-icon_empty',
                anchor: '99%',
                allowBlank: false,
            }, {
                xtype: 'textfield',
                fieldLabel: _('gtsnotify_channel_tpl'),
                name: 'tpl',
                id: config.id + '-tpl',
                anchor: '99%',
                allowBlank: false,
            },  {
                xtype: 'xcheckbox',
                boxLabel: _('gtsnotify_channel_email_send'),
                name: 'email_send',
                id: config.id + '-email_send',
                checked: true,
            }, {
                xtype: 'textfield',
                fieldLabel: _('gtsnotify_channel_email_tpl'),
                name: 'email_tpl',
                id: config.id + '-email_tpl',
                anchor: '99%',
                allowBlank: false,
            }, {
                xtype: 'textfield',
                fieldLabel: _('gtsnotify_channel_email_sleep'),
                name: 'email_sleep',
                id: config.id + '-email_sleep',
                anchor: '99%',
                allowBlank: false,
            },  {
                xtype: 'xcheckbox',
                boxLabel: _('gtsnotify_channel_hidden'),
                name: 'hidden',
                id: config.id + '-hidden',
                checked: true,
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
Ext.reg('gtsnotify-channel-window-create', gtsNotify.window.CreateChannel)

gtsNotify.window.UpdateChannel = function (config) {
    config = config || {}

    Ext.applyIf(config, {
        title: _('gtsnotify_item_update'),
        baseParams: {
            action: 'mgr/channel/update',
            resource_id: config.resource_id
        },
    })
    gtsNotify.window.UpdateChannel.superclass.constructor.call(this, config)
}
Ext.extend(gtsNotify.window.UpdateChannel, gtsNotify.window.CreateChannel)
Ext.reg('gtsnotify-channel-window-update', gtsNotify.window.UpdateChannel)