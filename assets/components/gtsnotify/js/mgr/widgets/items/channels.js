gtsNotify.grid.Channels = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'gtsnotify-grid-channels';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/channel/getlist',
            sort: 'id',
            dir: 'DESC'
        },
        stateful: true,
        stateId: config.id,
        viewConfig: {
            forceFit: true,
            enableRowBody: true,
            autoFill: true,
            showPreview: true,
            scrollOffset: 0,
            getRowClass: function (rec) {
                return !rec.data.active
                  ? 'gtsnotify-grid-row-disabled'
                  : '';
            }
        },
        paging: true,
        remoteSort: true,
        autoHeight: true,
    });
    gtsNotify.grid.Channels.superclass.constructor.call(this, config);
};
Ext.extend(gtsNotify.grid.Channels, gtsNotify.grid.Default, {

    getFields: function () {
        return [
            'id', 'name', 'icon', 'icon_empty', 'tpl', 'email_send', 'email_sleep', 'email_tpl', 'hidden', 'description', 'active', 'actions'
        ];
    },

    getColumns: function () {
        return [
            {
                header: _('gtsnotify_item_id'), 
                dataIndex: 'id', 
                width: 20, 
                sortable: true
            },
            {
                header: _('gtsnotify_item_name'),
                dataIndex: 'name', 
                sortable: true, 
                width: 200
            },
            {
                header: _('gtsnotify_channel_icon'),
                dataIndex: 'icon', 
                sortable: true, 
                width: 200
            },
            {
                header: _('gtsnotify_channel_icon_empty'),
                dataIndex: 'icon_empty', 
                sortable: true, 
                width: 200
            },
            {
                header: _('gtsnotify_channel_tpl'),
                dataIndex: 'tpl', 
                sortable: true, 
                width: 200
            },
            {
                header: _('gtsnotify_channel_email_send'), 
                dataIndex: 'email_send', 
                width: 75, 
                renderer: gtsNotify.utils.renderBoolean
            },
            
            {
                header: _('gtsnotify_channel_email_tpl'),
                dataIndex: 'email_tpl', 
                sortable: true, 
                width: 200
            },
            {
                header: _('gtsnotify_channel_email_sleep'),
                dataIndex: 'email_sleep', 
                sortable: true, 
                width: 200
            },
            {
                header: _('gtsnotify_channel_hidden'), 
                dataIndex: 'hidden', 
                width: 75, 
                renderer: gtsNotify.utils.renderBoolean
            },
            {
                header: _('gtsnotify_item_description'), 
                dataIndex: 'description', 
                sortable: false, 
                width: 250
            },
            {
                header: _('gtsnotify_item_active'), 
                dataIndex: 'active', 
                width: 75, 
                renderer: gtsNotify.utils.renderBoolean
            },
            {
                header: _('gtsnotify_grid_actions'),
                dataIndex: 'actions',
                id: 'actions',
                width: 50,
                renderer: gtsNotify.utils.renderActions
            }
        ];
    },

    getTopBar: function () {
        return [{
            text: '<i class="icon icon-plus"></i>&nbsp;' + _('gtsnotify_item_create'),
            handler: this.createItem,
            scope: this
        
        }];
    },

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateItem(grid, e, row);
            },
        };
    },

    createItem: function (btn, e) {
        var w = MODx.load({
            xtype: 'gtsnotify-channel-window-create',
            id: Ext.id(),
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        w.reset();
        w.setValues({active: true});
        w.show(e.target);
    },

    updateItem: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }
        else if (!this.menu.record) {
            return false;
        }
        var id = this.menu.record.id;

        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/channel/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w = MODx.load({
                            xtype: 'gtsnotify-channel-window-update',
                            id: Ext.id(),
                            record: r,
                            listeners: {
                                success: {
                                    fn: function () {
                                        this.refresh();
                                    }, scope: this
                                }
                            }
                        });
                        w.reset();
                        w.setValues(r.object);
                        w.show(e.target);
                    }, scope: this
                }
            }
        });
    },

    removeItem: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('gtsnotify_items_remove')
                : _('gtsnotify_item_remove'),
            text: ids.length > 1
                ? _('gtsnotify_items_remove_confirm')
                : _('gtsnotify_item_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/channel/remove',
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        return true;
    },

    disableItem: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/channel/disable',
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        })
    },

    enableItem: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/channel/enable',
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        })
    },
});
Ext.reg('gtsnotify-grid-channels', gtsNotify.grid.Channels);