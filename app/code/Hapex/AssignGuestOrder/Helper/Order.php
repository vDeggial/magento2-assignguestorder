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
            $customerEmail = $this->helperAddress->getOrderCustomerEmail($order);
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
            $data = $this->getCustomerData($customerEmail);
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
                    $this->helperData->log("Assigned order to existing customer: $customerFirstName $customerLastName");
                    $assigned = true;
                    break;

                default:
                    $this->helperData->log("No existing customer found for email: $customerEmail");
                    $assigned = false;
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
        $data = ["exists" => false];
        try {
            $helperCustomer = $this->generateClassObject(CustomerHelper::class);
            $customerId = $helperCustomer->getCustomerIdByEmail($customerEmail);
            if ($customerId > 0) {
                $data = [
                    "first-name" => $helperCustomer->getCustomerFirstName($customerId),
                    "last-name" => $helperCustomer->getCustomerLastName($customerId),
                    "group-id" => $helperCustomer->getCustomerGroup($customerId),
                    "id" => $customerId,
                    "exists" => true
                ];
            }
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $data = ["exists" => false];
        } finally {
            return $data;
        }
    }
}
