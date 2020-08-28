<?php

namespace Hapex\AssignGuestOrder\Helper;

use Hapex\Core\Helper\OrderHelper;
use Hapex\Core\Helper\OrderItemHelper;
use Hapex\Core\Helper\OrderGridHelper;
use Hapex\Core\Helper\OrderAddressHelper;
use Hapex\Core\Helper\CustomerHelper;
use Magento\Sales\Model\OrderRepository;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;

class Order extends OrderHelper
{
    protected $helperData;
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        OrderItemHelper $helperItem,
        OrderGridHelper $helperGrid,
        OrderAddressHelper $helperAddress,
        OrderRepository $orderRepository,
        Data $helperData
    ) {
        parent::__construct($context, $objectManager, $helperItem, $helperGrid, $helperAddress, $orderRepository);
        $this->helperData = $helperData;
    }

    public function assignGuestOrder(&$order = null)
    {
        $assigned = false;
        try {
            $helperCustomer = $this->generateClassObject(CustomerHelper::class);
            $customerEmail = $order->getCustomerEmail();
            switch (!empty($customerEmail)) {
                case true:
                    $customer = $helperCustomer->getCustomerByEmail($customerEmail);
                    $assigned = $this->assignToCustomer($order, $customer, $customerEmail);
                    break;
            }
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $assigned = false;
        }
        finally{
            return $assigned;
        }
    }

    private function assignToCustomer($order = null, $customer = null, $customerEmail = null)
    {
        $assigned = false;
        try {
            switch (isset($customer)) {
                case true:
                    $this->helperData->log("Existing customer found for $customerEmail");
                    $customerId = $customer->getId();
                    $customerFirstName = $customer->getFirstname();
                    $customerLastName = $customer->getLastname();
                    $customerGroupId = $customer->getGroupId();
                    $order->setCustomerId($customerId);
                    $order->setCustomerFirstname($customerFirstName);
                    $order->setCustomerLastname($customerLastName);
                    $order->setCustomerGroupId($customerGroupId);
                    $order->setCustomerIsGuest(0);
                    $this->helperData->log("Assigned order to $customerFirstName $customerLastName ($customerId / $customerEmail)");
                    $assigned = true;
                    break;
            }
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $assigned = false;
        }
        finally
        {
            return $assigned;
        }
    }
}
