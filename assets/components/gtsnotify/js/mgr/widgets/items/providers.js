gtsNotify.grid.Providers = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'gtsnotify-grid-providers';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/provider/getlist',
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
    gtsNotify.grid.Providers.superclass.constructor.call(this, config);
};
Ext.extend(gtsNotify.grid.Providers, gtsNotify.grid.Default, {

    getFields: function () {
        return [
            'id', 'name', 'class', 'path', 'ws_address', 'secret_key', 'host', 'path', 'description', 'active', 'actions'
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
                header: _('gtsnotify_provider_class'),
                dataIndex: 'class', 
                sortable: true, 
                width: 200
            },
            {
                header: _('gtsnotify_provider_path'),
                dataIndex: 'path', 
                sortable: true, 
                width: 200
            },
            {
                header: _('gtsnotify_provider_ws_address'),
                dataIndex: 'ws_address', 
                sortable: true, 
                width: 200
            },
            {
                header: _('gtsnotify_provider_secret_key'),
                dataIndex: 'secret_key', 
                sortable: true, 
                width: 200
            },
            {
                header: _('gtsnotify_provider_host'),
                dataIndex: 'host', 
                sortable: true, 
                width: 200
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
        },{
            xtype: 'gtsnotify-combo-filter-active',
            name: 'active',
            width: 210,
            custm: true,
            clear: true,
            addall: true,
            value: '',
            listeners: {
                select: {
                    fn: this._filterByCombo,
                    scope: this
                },
                afterrender: {
                    fn: this._filterByCombo,
                    scope: this
                }
            }
        },{
            xtype: 'gtsnotify-combo-filter-resource',
            name: 'resource',
            width: 210,
            custm: true,
            clear: true,
            addall: true,
            value: '',
            listeners: {
                select: {
                    fn: this._filterByCombo,
                    scope: this
                },
                afterrender: {
                    fn: this._filterByCombo,
                    scope: this
                }
            }
        },
            '->', this.getSearchField()];
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
            xtype: 'gtsnotify-provider-window-create',
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
                action: 'mgr/provider/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w = MODx.load({
                            xtype: 'gtsnotify-provider-window-update',
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
        this.action('remove')
    },
    disableItem: function () {
        this.action('disable')
    },
    enableItem: function () {
        this.action('enable')
    },
});
Ext.reg('gtsnotify-grid-providers', gtsNotify.grid.Providers);