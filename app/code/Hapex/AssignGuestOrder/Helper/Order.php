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
            $customerEmail = $order->getCustomerEmail();
            switch (isset($customerEmail)) {
                case true:
                    $assigned = $this->assignToCustomer($order, $customerEmail);
                    break;
            }
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $assigned = false;
        } finally {
            return $assigned;
        }
    }

    private function assignToCustomer($order = null, $customerEmail = null)
    {
        $assigned = false;
        try {
            $data = $this->getCustomerData($customerEmail = null);
            switch ($data["exists"]) {
                case true:
                    $this->helperData->log("Existing customer found for $customerEmail");
                    $customerId = $data["id"];
                    $customerFirstName = $data["first-name"];
                    $customerLastName = $data["last-name"];
                    $customerGroupId = $data["group-id"];
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
        } finally {
            return $assigned;
        }
    }

    private function getCustomerData($customerEmail = null)
    {
        $data = ["id" => 0, "first-name" => null, "last-name" => null, "group-id" => 0, "exists" => false];
        try {
            $helperCustomer = $this->generateClassObject(CustomerHelper::class);
            $customer = $helperCustomer->getCustomerByEmail($customerEmail);
            $customerId = $customer->getId();
            $exists = isset($customerId);
            switch ($exists) {
                case true:
                    $data["first-name"] = $customer->getFirstname();
                    $data["last-name"] = $customer->getLastname();
                    $data["group-id"] = $customer->getGroupId();
                    $data["id"] = $customerId;
                    $data["exists"] = $exists;
                    break;
            }
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $data = ["id" => 0, "first-name" => null, "last-name" => null, "group-id" => 0, "exists" => false];
        } finally {
            return $data;
        }
    }
}
