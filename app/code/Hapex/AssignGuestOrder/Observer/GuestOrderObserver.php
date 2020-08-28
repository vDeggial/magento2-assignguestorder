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
        try {
            $this->helperData->log("");
            $this->helperData->log("Starting Guest Order Observer");
            $order = $observer->getEvent()->getOrder();
            $this->helperData->log("Checking for Guest Order");
            if ($this->helper->isModuleEnabled() && $this->helperOrder->isGuestOrder($order)) {
                $orderNumber = $order->getIncrementId();
                $this->helperData->log("Guest Order #$orderNumber detected");
                $this->helperData->log("Attempting to assign to existing customer");
                if ($this->helperOrder->assignGuestOrder($order)) {
                    $this->helperData->log("Guest Order assigned successfully");
                } else {
                    $this->helperData->log("Could not assign guest order to existing customer");
                }
            }
            $this->helperData->log("Ending Guest Order Observer");
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }
}
