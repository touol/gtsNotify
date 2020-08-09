(function (window, document, $, gtsNotifyConfig) {
    var gtsNotify = gtsNotify || {};
    gtsNotifyConfig.callbacksObjectTemplate = function () {
        return {
            // return false to prevent send data
            before: [],
            response: {
                success: [],
                error: []
            },
            ajax: {
                done: [],
                fail: [],
                always: []
            }
        }
    };
    gtsNotify.Callbacks = gtsNotifyConfig.Callbacks = {
        Channel: {
            load: gtsNotifyConfig.callbacksObjectTemplate(),
            remove: gtsNotifyConfig.callbacksObjectTemplate(),
        },
    };
    
    gtsNotify.Callbacks.add = function (path, name, func) {
        if (typeof func != 'function') {
            return false;
        }
        path = path.split('.');
        var obj = gtsNotify.Callbacks;
        for (var i = 0; i < path.length; i++) {
            if (obj[path[i]] == undefined) {
                return false;
            }
            obj = obj[path[i]];
        }
        if (typeof obj != 'object') {
            obj = [obj];
        }
        if (name != undefined) {
            obj[name] = func;
        }
        else {
            obj.push(func);
        }
        return true;
    };
    gtsNotify.Callbacks.remove = function (path, name) {
        path = path.split('.');
        var obj = gtsNotify.Callbacks;
        for (var i = 0; i < path.length; i++) {
            if (obj[path[i]] == undefined) {
                return false;
            }
            obj = obj[path[i]];
        }
        if (obj[name] != undefined) {
            delete obj[name];
            return true;
        }
        return false;
    };
    gtsNotify.setup = function () {
        // selectors & $objects
        this.actionName = 'gtsnotify';
        //this.action = ':submit[name=' + this.actionName + ']';
        //this.form = '.gts-form';
        this.$doc = $(document);

        this.sendData = {
            action: this.actionName,
            data: null
        };

        this.timeout = 300;
    };
    gtsNotify.initialize = function () {
        gtsNotify.setup();
        
        //noinspection JSUnresolvedFunction
        /*gtsNotify.$doc
            .on('submit', gtsNotify.form, function (e) {
                e.preventDefault();
                
            });*/
            
        gtsNotify.Channel.initialize();
    };
    gtsNotify.send = function (data, callbacks, userCallbacks) {
        var runCallback = function (callback, bind) {
            if (typeof callback == 'function') {
                return callback.apply(bind, Array.prototype.slice.call(arguments, 2));
            }
            else if (typeof callback == 'object') {
                for (var i in callback) {
                    if (callback.hasOwnProperty(i)) {
                        var response = callback[i].apply(bind, Array.prototype.slice.call(arguments, 2));
                        if (response === false) {
                            return false;
                        }
                    }
                }
            }
            return true;
        };
        // set context
        if ($.isArray(data)) {
            data.push({
                name: 'ctx',
                value: gtsNotifyConfig.ctx
            });
        }
        
        else if ($.isPlainObject(data)) {
            data.ctx = gtsNotifyConfig.ctx;
        }
        else if (typeof data == 'string') {
            data += '&ctx=' + gtsNotifyConfig.ctx;
        }

        // set action url
        var url =  gtsNotifyConfig.actionUrl;
        var method = 'post';
        // callback before
        if (runCallback(callbacks.before) === false || runCallback(userCallbacks.before) === false) {
            return;
        }
        // send
        var xhr = function (callbacks, userCallbacks) {
            return $[method](url, data, function (response) {
                if (response.success) {
                    if (response.message) {
                        //gtsNotify.Message.success(response.message);
                    }
                    runCallback(callbacks.response.success, gtsNotify, response);
                    runCallback(userCallbacks.response.success, gtsNotify, response);
                }
                else {
                    //gtsNotify.Message.error(response.message);
                    runCallback(callbacks.response.error, gtsNotify, response);
                    runCallback(userCallbacks.response.error, gtsNotify, response);
                }
            }, 'json').done(function () {
                runCallback(callbacks.ajax.done, gtsNotify, xhr);
                runCallback(userCallbacks.ajax.done, gtsNotify, xhr);
            }).fail(function () {
                runCallback(callbacks.ajax.fail, gtsNotify, xhr);
                runCallback(userCallbacks.ajax.fail, gtsNotify, xhr);
            }).always(function (response) {
                
                runCallback(callbacks.ajax.always, gtsNotify, xhr);
                runCallback(userCallbacks.ajax.always, gtsNotify, xhr);
            });
        }(callbacks, userCallbacks);
    };
    gtsNotify.Channel = {
        
        callbacks: {
            load: gtsNotifyConfig.callbacksObjectTemplate(),
            remove: gtsNotifyConfig.callbacksObjectTemplate(),
        },
        
        initialize: function () {
            gtsNotify.$doc
                .on('click', 'body', function (e) {
                    $(this).find('.gtsnotify-channel-menu').hide();
                });
            //get-autocomplect-all
            gtsNotify.$doc
                .on('click', '.gtsnotify-channel-btn', function (e) {
                    e.preventDefault();
                    $channel = $(this).closest('.gtsnotify-channel');
                    $menu = $channel.find('.gtsnotify-channel-menu');
                    if($menu.is(':visible')){
                        $menu.hide();
                        return;
                    }
                    //gtsNotify.sendData.$GtsApp = $table;
                    gtsNotify.sendData.$channel = $channel;
                    gtsNotify.sendData.data = {
                        gtsnotify_action: 'load_channel_notify',
                        name: $channel.data('name'),
                    };
                    var callbacks = gtsNotify.Channel.callbacks;
            
                    callbacks.load.response.success = function (response) {
                        $menu = gtsNotify.sendData.$channel.find('.gtsnotify-channel-menu');
                        $menu.html(response.data.html).show();
                    };
                    gtsNotify.send(gtsNotify.sendData.data, gtsNotify.Channel.callbacks.load, gtsNotify.Callbacks.Channel.load);
                });
            gtsNotify.$doc
                .on('click', '.gtsnotify-channel-notify-remove', function (e) {
                    e.preventDefault();
                    $channel = $(this).closest('.gtsnotify-channel');
                    $notify = $(this).closest('.gtsnotify-channel-notify');
                    gtsNotify.sendData.$channel = $channel;
                    gtsNotify.sendData.data = {
                        gtsnotify_action: 'remove_channel_notify',
                        name: $channel.data('name'),
                        notify_id: $notify.data('id'),
                    };
                    var callbacks = gtsNotify.Channel.callbacks;
            
                    callbacks.remove.response.success = function (response) {
                        $notify = gtsNotify.sendData.$channel.find('.gtsnotify-channel-notify');
                        $notify.remove();
                        $badge = gtsNotify.sendData.$channel.find('.gtsnotify-badge-notify');
                        $badge.text(response.data.count);
                        if(response.data.count == 0){
                            $badge.hide();
                        }else{
                            $badge.show();
                        }
                    };
                    gtsNotify.send(gtsNotify.sendData.data, gtsNotify.Channel.callbacks.remove, gtsNotify.Callbacks.Channel.remove);
                });
            
            document.addEventListener("gtsnotifyprovider", function(event) { 
                //console.log('notify',event.detail);
                for(var key in event.detail.channels) {
                    $badge = $('.gtsnotify-channel[data-name="' + key + '"]').find('.gtsnotify-badge-notify');
                    $badge.text(event.detail.channels[key].data.channel_count);
                    if(event.detail.channels[key].data.channel_count == 0){
                        $badge.hide();
                    }else{
                        $badge.show();
                    }
                }
            });
        },
    };
    $(document).ready(function ($) {
        gtsNotify.initialize();
    });
})(window, document, jQuery, gtsNotifyConfig);