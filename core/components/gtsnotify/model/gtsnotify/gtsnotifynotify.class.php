<?php
class gtsNotifyNotify extends xPDOSimpleObject {
    
    public function addPurpose($user_id,$channels,$url = '')
    {
        if(empty($channels)) $this->error("empty channels!");
        if(is_string($channels) or (!is_array($channels) and (int)$channels > 0)) $channels = explode(',',$channels);
        if(!is_array($channels) or empty($channels)) $this->error("empty channels!");

        $channel_ids = []; $channel_names = [];
        foreach($channels as $channel){
            $channel = trim($channel);
            if((int)$channel > 0){
                $channel_ids[(int)$channel] = (int)$channel;
            }else{
                $channel_names[] = $channel;
            }
        }
        if(!empty($channel_names)){
            $default = array(
                'class' => 'gtsNotifyChannel',
                'where' => [
                    'name:IN'=> $channel_names,
                ],
                'select' => [
                    'gtsNotifyChannel'=>'id'
                ],
                'return' => 'data',
                'limit' => 0,
            );
            if ($pdoTools = $this->xpdo->getService('pdoFetch')) {
                $pdoTools->setConfig($default, false);
                $rows = $pdoTools->run();
                if(count($rows) > 0){
                    foreach($rows as $row){
                        $channel_ids[(int)$row['id']] = (int)$row['id'];
                    }
                }
            }
        }
        if(empty($channel_ids)) return $this->error("empty channel_ids!");
        foreach($channel_ids as $channel){
            if($notifyPurpose = $this->xpdo->newObject("gtsNotifyNotifyPurpose")){
                $notifyPurpose->fromArray([
                    'notify_id'=>$this->id,
                    'user_id'=>$user_id,
                    'channel_id'=>$channel,
                    'url'=>$url,
                ]);
                $notifyPurpose->save();
            }
        }
        return $this->success();;
    }
    
    public function send($user_data = [], $send_only_channel_count = false)
    {
        if (!$pdoTools = $this->xpdo->getService('pdoFetch')) $this->error("empty pdoTools!");
        $data = json_decode($this->json,1);
        $Purposes = $this->xpdo->getIterator('gtsNotifyNotifyPurpose',['notify_id'=>$this->id]);
        $users = []; $channel_ids = [];
        foreach($Purposes as $p){
            $users[] = $p->user_id;
            $channel_ids[] = $p->channel_id;
        }
        $default = array(
            'class' => 'gtsNotifyChannel',
            'where' => [
                'id:IN'=> $channel_ids,
            ],
            'select' => [
                'gtsNotifyChannel'=>'id, name, tpl'
            ],
            'return' => 'data',
            'limit' => 0,
        );
        
        $pdoTools->setConfig($default, false);
        $channels = $pdoTools->run();
        $channels0 = [];
        foreach($users as $user_id){
            foreach($channels as &$channel){
                $content = $pdoTools->getChunk($channel['tpl'], $data);
                //$channel['content'] = '<li><a href="'.$url.'" data-id="'.$row['id'].'">'.$content.'</a></li>';
                $channel['user_ids'][$user_id]['channel_count'] = $this->xpdo->getCount('gtsNotifyNotifyPurpose',[
                    'active'=>1,
                    'channel_id'=>$channel['id'],
                    'user_id'=> $user_id,
                ]);
                if(isset($user_data[$user_id])) $channel['user_ids'][$user_id]['user_data'] = $user_data[$user_id];
            }
        }
        
            foreach($channels as $channel){
                $content = $pdoTools->getChunk($channel['tpl'], $data);
                $channels0[$channel['name']] = $channel;
            }
        
        if($provider = $this->xpdo->getObject("gtsNotifyProvider",['active'=>1])){
            if ($providerClass = $this->xpdo->loadClass($provider->class, xpdo_CORE_PATH . $provider->path, false, true)) {
                $provider = new $providerClass($this->xpdo, []);
            }
        }
        $resp = $provider->sendNotyfyUsers($users, $channels0, $data, $send_only_channel_count);
        return $resp;
    }

    public function error($message = '', $data = array())
    {
        if(is_array($message)) $message = $this->xpdo->lexicon($message['lexicon'], $message['data']);
        $response = array(
            'success' => false,
            'message' => $message,
            'data' => $data,
        );

        return $response;
    }
    
    public function success($message = '', $data = array())
    {
        if(is_array($message)) $message = $this->xpdo->lexicon($message['lexicon'], $message['data']);
        $response = array(
            'success' => true,
            'message' => $message,
            'data' => $data,
        );

        return $response;
    }
}