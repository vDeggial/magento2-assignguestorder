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
        ManagerInterface $messageManager,
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
            if ($this->helperData->isModuleEnabled()) {
                $this->processOrder($order);
            }
            $this->helperData->log("Ending Guest Order Observer");
        } catch (\Throwable $e) {
            $this->helperLog->errorLog(__METHOD__, $this->helperLog->getExceptionTrace($e));
            $this->messageManager->addErrorMessage($e->getMessage());
        } finally {
            return $this;
        }
    }

    private function processOrder($order = null)
    {
        try {
            $orderNumber = $order->getIncrementId();
            switch ($this->helperOrder->isGuestOrder($order)) {
                case true:
                    $this->helperData->log("Guest Order #$orderNumber detected");
                    $this->helperData->log("Attempting to assign to existing customer");
                    switch ($this->helperOrder->assignGuestOrder($order)) {
                        case true:
                            $this->helperData->log("Guest Order assigned successfully");
                            break;

                        default:
                            $this->helperData->log("Did not assign the Guest Order to any existing customer");
                            break;
                    }
                    break;

                default:
                    $this->helperData->log("Order #$orderNumber is not a Guest Order");
                    break;
            }
        } catch (\Throwable $e) {
            $this->helperLog->errorLog(__METHOD__, $this->helperLog->getExceptionTrace($e));
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }
}
