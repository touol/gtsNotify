<?php

class CometServer
{
    /** @var modX $modx */
    public $modx;

    /** @var pdoFetch $pdoTools */
    public $pdoTools;

    public $client = null;
    public $provider = null;
    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;

        $this->config = array_merge([
            /*'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'processorsPath' => $corePath . 'processors/',
            'customPath' => $corePath . 'custom/',

            'connectorUrl' => $assetsUrl . 'connector.php',
            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',*/
        ], $config);
        //$gtsNotify = $modx->getService('gtsNotify', 'gtsNotify', MODX_CORE_PATH . 'components/gtsnotify/model/', []);

        $this->modx->addPackage('gtsnotify', MODX_CORE_PATH . 'components/gtsnotify/model/');
        if($provider = $this->modx->getObject("gtsNotifyProvider",['active'=>1])){
            $this->provider = $provider;
        }
        
        if ($this->pdoTools = $this->modx->getService('pdoFetch')) {
            $this->pdoTools->setConfig($this->config);
        }
    }
    
    public function handleRequest($action, $data = array())
    {
        // switch($action){
        //     case 'reg_client':
        //         return $this->reg_client($data);
        //         break;
        //     case 'delete_client':
        //         return $this->delete_client($data);
        //         break;
        //     default:
        //         return $this->error("Метод $action в классе $class не найден!");
        // }
    }
    // public function delete_client($data)
    // {
    //     if($ws_client = $this->modx->getObject("gtsNotifyWSClient",['ws_id'=>$data['ws_id']])){
    //         if($ws_client->remove()) return $this->success('',array('ws_id'=>$data['ws_id']));
    //     }
    //     return $this->error("delete_client error!", $data);
    // }

   
    // public function getJS()
    // {
    //     return [
    //         trim($this->modx->getOption('gtsnotify_vendor_comet_server_js')),
    //         trim($this->modx->getOption('gtsnotify_provider_comet_server_js')),
    //     ];
    // }

    public function regJS($config)
    {
        if($this->provider){
            $comet = [
                'id' => $this->provider->host,
                'key' => $this->provider->secret_key,
                'address' => $this->provider->ws_address,
            ];
            $link = mysqli_connect($comet['address'], $comet['id'], $comet['key'], 'CometQL_v1');
            if ($link) {
                $result = mysqli_query($link, 'INSERT INTO users_auth (id, hash) VALUES ('.$this->modx->user->id.', "'.md5($this->modx->user->password).'")');
                if(mysqli_errno($link) != 0) {
                    $modx->log(modX::LOG_LEVEL_ERROR, '[gtsnotify] Comet error #' . mysqli_errno($link). ' ' . mysqli_error($link));
                }
            }
            $this->modx->regClientScript(str_replace($config['pl'], $config['vl'], 
                trim($this->modx->getOption('gtsnotify_vendor_comet_server_js'))));
            $this->modx->regClientScript(str_replace($config['pl'], $config['vl'], 
                trim($this->modx->getOption('gtsnotify_provider_comet_server_js'))));
            $this->modx->regClientHTMLBlock('<script>
                cometApi.start({ dev_id: ' . $comet['id'] . ', user_id: '.$this->modx->user->id.', user_key: "'.md5($this->modx->user->password).'" });
                cometApi.subscription("re_messages_'.$this->modx->user->id.'", function(data) {
                    gtsNotifyProvider.newMessage(data);
                });
                cometApi.subscription("track_online");
            </script>');
            return true;
        }
        return false;
        // return [
        //     trim($this->modx->getOption('gtsnotify_vendor_comet_server_js')),
        //     trim($this->modx->getOption('gtsnotify_provider_comet_server_js')),
        // ];
    }
    
    public function sendNotyfyUsers($users = array(), $channels, $data = array(),$send_only_channel_count = true){
        if($this->provider){
            $comet = [
                'id' => $this->provider->host,
                'key' => $this->provider->secret_key,
                'address' => $this->provider->ws_address,
            ];
            $link = mysqli_connect($comet['address'], $comet['id'], $comet['key'], 'CometQL_v1');
            
            
            if ($link) {
                foreach($users as $user_id){
                    
                    $channels0 = [];
                    foreach($channels as $channel){
                        $channel['data']['channel_count'] = $channel['user_ids'][$user_id]['channel_count'];
                        $channel['data']['user_data'] = $channel['user_ids'][$user_id]['user_data'];
                        $channels0[$channel['name']] = $channel;
                    }
                    foreach($channels0 as &$channel){
                        unset($channel['user_ids']);
                    }
                    $send = [
                        'data'=>$send_only_channel_count ? [] : $data,
                        'channels'=>$channels0,
                    ];
                    //$this->modx->log(modX::LOG_LEVEL_ERROR, '[gtsnotify] Comet $user_id:' .$user_id." ". json_encode($send) );
                    $result = mysqli_query($link, 'INSERT INTO pipes_messages (name, event, message) ' .
                              'VALUES ("re_messages_'.$user_id.'", "message", \''. json_encode($send) . '\')');
                    if(mysqli_errno($link) != 0) {
                        $this->modx->log(modX::LOG_LEVEL_ERROR, '[gtsnotify] Comet error #' . mysqli_errno($link). ' ' . mysqli_error($link));
                    }
                }
            }
            
            return $this->success('Отправлено ');
        }
                
        return $this->error("error!");
    }
    
    public function error($message = '', $data = array())
    {
        if(is_array($message)) $message = $this->modx->lexicon($message['lexicon'], $message['data']);
        $response = array(
            'success' => false,
            'message' => $message,
            'data' => $data,
        );

        return $response;
    }
    
    public function success($message = '', $data = array())
    {
        if(is_array($message)) $message = $this->modx->lexicon($message['lexicon'], $message['data']);
        $response = array(
            'success' => true,
            'message' => $message,
            'data' => $data,
        );

        return $response;
    }
}