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
      gtsNotifyProvider.reg_client('reg_client','hello', gtsNotifyConfig.host, gtsNotifyConfig.ws_id);
    };

    // обработчик входящих сообщений
    gtsNotifyProvider.socket.onmessage = function(event) {
      var incomingMessage = event.data;
      //console.info('gtsNotifyProvider message',incomingMessage);
      try {
        var json = JSON.parse(incomingMessage);
        //console.info('json',json);
        if(json.type == 'send_notify'){
          document.dispatchEvent(new CustomEvent("gtsnotifyprovider", {
            detail: json
          }));
        }
        if(json.type == 'reg_client'){
          var xhr = new XMLHttpRequest();

          var body = 'action=' + encodeURIComponent('reg_client') +
            '&ws_id=' + encodeURIComponent(json.ws_id);
            //action: 'reg_client', ws_id: json.ws_id
          xhr.open("POST", '/assets/components/gtsnotify/gtsnotifyru.php', true);
          xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

          xhr.onreadystatechange = function() {
            if (this.readyState != 4) return;
            //console.log( 'reg_client', this.responseText );
          }

          xhr.send(body)
        }
      } catch (e) {
        console.log('Invalid JSON');
        return;
      }
    };
  };
  

  gtsNotifyProvider.reg_client = function (command, message, host,ws_id, data) {
    //console.log("reg_client");
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

