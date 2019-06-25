<?php

namespace Concrete\Package\HwCommunityStoreGoogleAnalytics\Src;

use Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer as StoreCustomer;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order as StoreOrder;
use Concrete\Package\CommunityStore\Controller\SinglePage\Checkout\Complete as StoreComplete;
use Concrete\Core\Support\Facade\Config;

class Order extends StoreComplete
{
    public function view()
    {

        $customer = new StoreCustomer();
        $purchase_info = $this->orderPlaced($customer->getLastOrderID());

        $this->view->addHeaderItem('<!--  Start of Google eCommerce tracking-->' . PHP_EOL . '<script>' . 'window.dataLayer = window.dataLayer || [];' . PHP_EOL . $purchase_info . '</script>' . PHP_EOL . '<!--  End of Google eCommerce tracking-->');

        return parent::view();

    }

    public function orderPlaced($lastorderid)
    {
        $totaltax = '';

        if ($lastorderid) {
            $order = StoreOrder::getByID($lastorderid);


            foreach ($order->getTaxes() as $tax) {
                $totaltax = $tax['amount'] ? $tax['amount'] : $tax['amountIncluded'];
            }

            $order_details = [];

            $order_details['order_id'] = $order->getOrderID();
            $order_details['store_name'] = Config::get('concrete.site');
            $order_details['total'] = number_format($order->getTotal(), 2, '.', '');
            $order_details['tax'] = number_format($totaltax, 2, '.', '');
            $order_details['shipping'] = number_format($order->getShippingTotal(), 2, '.', '');


            $purchase_info = "dataLayer.push({
                    'transactionId': '" . $order_details['order_id'] . "', 
                    'transactionAffiliation': '" . $order_details['store_name'] . "',
                    'transactionTotal': " . $order_details['total'] . ", 
                    'transactionShipping': " . $order_details['shipping'] . ", 
                    'transactionTax': " . $order_details['tax'] . ", ";

            $items = $order->getOrderItems();
            $purchase_info .= "'transactionProducts': [";
            foreach ($items as $item) {
                $purchase_info .= "{
                'sku': '" . $item->getSKU() . "',
                'name': '" . $item->getProductName() . "', 
                'brand': '',
                'category': '', 
                'quantity': " . $item->getQty() . ",
                'price': " . number_format($item->getPricePaid(), 2, '.', '') . "},";

            }
            //remove last comma
            $purchase_info = rtrim($purchase_info, ",");
            $purchase_info .= ']});';

            return $purchase_info;

        } else {
            return '';
        }
    }

}
