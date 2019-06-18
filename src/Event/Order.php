<?php

namespace Concrete\Package\HwCommunityStoreGoogleAnalytics\Src\Event;

use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;
use Log;
use Config;
use view;
use Concrete\Core\Support\Facade\Session;
use Concrete\Core\Http\Client\Client;

class Order
{

    public function orderPlaced($event)
    {

        $order = $event->getOrder();

        $taxCalc = \Concrete\Core\Support\Facade\Config::get('community_store.calculation');
        $totals = StoreCalculator::getTotals();
        $taxtotal = StoreCalculator::getTaxTotals();
        $shippingTotal = number_format($totals['shippingTotal'], 2, '.', '');

        foreach ($taxtotal as $taxtotals) {
            $shippingTaxTotal = number_format($taxtotals['shippingtaxamount'], 2, '.', '');
        }

        if ('extract' != $taxCalc) {
            $shippingNetAmount = number_format($shippingTotal, 2, '.', '');
            $shippingGrossTotal = number_format($shippingTotal + $shippingTaxTotal, 2, '.', '');
        } else {
            $shippingNetAmount = number_format($shippingTotal - $shippingTaxTotal, 2, '.', '');
            $shippingGrossTotal = number_format($shippingTotal, 2, '.', '');
        }

        $order_details = [];

        $order_details['order_id'] = $order->getOrderID();
        $order_details['store_name'] = Config::get('concrete.site');
        $order_details['total'] = number_format(StoreCalculator::getGrandTotal(), 2, '.', '');
        $order_details['tax'] = number_format($totals['taxTotal'], 2, '.', '');
        $order_details['shipping'] = $shippingGrossTotal;
//$order_details['products'] = $order_lines;



            $purchase_info = "<script>";

            $purchase_info .= "gtag('event', 'purchase', {
                    'transaction_id': '" . $order_details['order_id'] . "', 
                    'affiliation': '" . $order_details['store_name'] . "',
                    'value': '" . $order_details['total'] . "', 
                    'shipping': '" . $order_details['shipping'] . "' , 
                    'tax': '" . $order_details['tax'] . "', ";

            //ADD INFO FOR EACH PRODUCT


            $items = $order->getOrderItems();

        $purchase_info .= "'items': [";
            foreach ($items as $item) {
                $purchase_info .= "{
                'id': '" . $item->getSKU() . "',
                'name': '" . $item->getProductName() . "', 
                'brand': '',
                'category': '', 
                'quantity': '". $item->getQty() . "',
                'price': '" . number_format($item->getPricePaid(), 2, '.', '') . "'},";

            }
            //remove last comma
        $purchase_info = rtrim($purchase_info, ",");
        $purchase_info .= ']});';

        $purchase_info .= '</script>';


        Session::set('purchase_info', $purchase_info);

    }





}
