<?php
/*
* category JRB
* module JRB_Frontendorderpdf
* 
* author Jyotiranjan Biswal <biswal@jyotiranjan.in>
*/
class JRB_Frontendorderpdf_PdfController extends Mage_Core_Controller_Front_Action{
    /*
    * category JRB
    * module JRB_Frontendorderpdf
    * create invoice by order ID
    * 
    * author Jyotiranjan Biswal <biswal@jyotiranjan.in>
    */
    public function invoicespdfAction() {
        $orderId = (int) $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        if ($this->_canViewOrder($order)) {
            $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                ->setOrderFilter($orderId)
                ->load();
            if ($invoices->getSize() > 0) {
                $flag = true;
                if (!isset($pdf)){
                    $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
                } else {
                    $pages = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
                    $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                }
            }
	    if ($flag) {
		return $this->_prepareDownloadResponse(
		    'invoice'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(),
		    'application/pdf'
		);
	    } else {
		Mage::getSingleton('core/session')->addError($this->__('There are no printable documents related to selected orders.'));
		$this->_redirectReferer();
	    }
        }
    }
    /*
    * category JRB
    * module JRB_Frontendorderpdf
    * check for order visibility
    * 
    * author Jyotiranjan Biswal <biswal@jyotiranjan.in>
    */
    protected function _canViewOrder($order)
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        $availableStates = Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates();
        if ($order->getId() && $order->getCustomerId() && ($order->getCustomerId() == $customerId)
            && in_array($order->getState(), $availableStates, $strict = true)
            ) {
            return true;
        }
        return false;
    }
}