<?php

namespace Hapex\AssignGuestOrder\Helper;

use Hapex\Core\Helper\DataHelper;

class Data extends DataHelper
{
    protected const XML_PATH_CONFIG_ENABLED = "hapex_assignguestorder/general/enable";
    protected const FILE_PATH_LOG = "hapex_assignguestorder";

    public function isModuleEnabled()
    {
        return $this->getConfigFlag(self::XML_PATH_CONFIG_ENABLED);
    }

    public function log($message)
    {
        $this->helperLog->printLog(self::FILE_PATH_LOG, $message);
    }
}
