<?php
include_once dirname(__FILE__) . '/update.class.php';
class gtsNotifyItemDisableProcessor extends gtsNotifyItemUpdateProcessor
{
    public function beforeSet()
    {
        $this->setProperty('active', false);
        return true;
    }
}
return 'gtsNotifyItemDisableProcessor';
