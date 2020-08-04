(function (window, document, gtsNotifyConfig) {
  var gtsNotifyProvider = gtsNotifyProvider || {};
  gtsNotifyProvider.socket;
  gtsNotifyProvider.initialize = function () {
    if (!window.WebSocket) {
      //document.body.innerHTML = 'WebSocket в этом браузере не поддерживается.';
      return;
    }
    gtsNotifyProvider.socket = new WebSocket(gtsNotifyConfig.ws_address);
    
    gtsNotifyProvider.socket.onopen = function() {
      gtsNotifyProvider.reg_client('reg_client','hello', location.hostname, gtsNotifyConfig.ws_id);
    };

    // обработчик входящих сообщений
    gtsNotifyProvider.socket.onmessage = function(event) {
      var incomingMessage = event.data;
      console.info('gtsNotifyProvider message',incomingMessage);
      try {
        var json = JSON.parse(incomingMessage);
        //console.info('json',json);
        if(json.type == 'send_notify'){
          document.dispatchEvent(new CustomEvent("gtsnotifyprovider", {
            detail: json
          }));
        }
      } catch (e) {
        console.log('Invalid JSON');
        return;
      }
    };
  };
  

  gtsNotifyProvider.reg_client = function (command, message, host,ws_id, data) {
    var json = JSON.stringify({ 
      type:'command', 
      command: 'reg_client',
      message: message,
      host: host,
      ws_id: ws_id,
      data: data,
    });
    gtsNotifyProvider.socket.send(json);
  };

  document.addEventListener("DOMContentLoaded", gtsNotifyProvider.initialize);
})(window, document, gtsNotifyConfig);

