(function (window, document, gtsNotifyConfig) {
    var gtsNotifyProvider = gtsNotifyProvider || {};
    gtsNotifyProvider.socket;
    
    gtsNotifyProvider.newMessage = function(data){
        //console.log('gtsNotifyProvider.newMessage',data);
        document.dispatchEvent(new CustomEvent("gtsnotifyprovider", {
            detail: data.data
          }));
    };

    gtsNotifyProvider.Online = function(user_id, callback){
        cometApi.subscription("user_status_" + user_id + ".online", callback);
    };
    gtsNotifyProvider.Offline = function(user_id, callback){
        cometApi.subscription("user_status_" + user_id + ".offline", callback);
    };
    
    window.gtsNotifyProvider = gtsNotifyProvider;
})(window, document, gtsNotifyConfig);