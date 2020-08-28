<?php

namespace Hapex\AssignGuestOrder\Observer;

use Hapex\Core\Helper\LogHelper;
use Hapex\Core\Observer\BaseObserver;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
use Hapex\AssignGuestOrder\Helper\Data as DataHelper;
use Hapex\AssignGuestOrder\Helper\Order as OrderHelper;

class GuestOrderObserver extends BaseObserver
{
    protected $helperOrder;

    public function __construct(
        DataHelper $helperData,
        OrderHelper $helperOrder,
        LogHelper $helperLog,
        ManagerInterface $messageManager
    ) {
        parent::__construct($helperData, $helperLog, $messageManager);
        $this->helperOrder = $helperOrder;
    }

    public function execute(Observer $observer)
    {
        
    }
}
