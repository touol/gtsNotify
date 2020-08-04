<?php
include_once dirname(__FILE__) . '/update.class.php';
class gtsNotifyItemEnableProcessor extends gtsNotifyItemUpdateProcessor
{
    public function beforeSet()
    {
        $this->setProperty('active', true);
        return true;
    }
}
return 'gtsNotifyItemEnableProcessor';