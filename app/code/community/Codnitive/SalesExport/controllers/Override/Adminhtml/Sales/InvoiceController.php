<?php
/**
 * CODNITIVE
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE_EULA.html.
 * It is also available through the world-wide-web at this URL:
 * http://www.codnitive.com/en/terms-of-service-softwares/
 * http://www.codnitive.com/fa/terms-of-service-softwares/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @category   Codnitive
 * @package    Codnitive_SalesExport
 * @author     Hassan Barza <support@codnitive.com>
 * @copyright  Copyright (c) 2012 CODNITIVE Co. (http://www.codnitive.com)
 * @license    http://www.codnitive.com/en/terms-of-service-softwares/ End User License Agreement (EULA 1.0)
 */

/**
 * Adminhtml sales orders controller
 *
 * @category   Codnitive
 * @package    Codnitive_SalesExport
 * @author     Hassan Barza <support@codnitive.com>
 */
require_once 'Mage/Adminhtml/controllers/Sales/InvoiceController.php';
class Codnitive_SalesExport_Override_Adminhtml_Sales_InvoiceController 
    extends Mage_Adminhtml_Sales_InvoiceController
{
    private function _getConfig()
    {
        return Mage::getModel('salesexport/config');
    }
    
    public function exportCsvAction()
    {
        if (!$this->_getConfig()->isInvoicesActive()) {
            parent::exportCsvAction();
            return;
        }
        
        $fileName   = 'invoices.csv';
        $grid       = $this->getLayout()->createBlock('salesexport/adminhtml_sales_invoice_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }
    
    public function exportExcelAction()
    {
        if (!$this->_getConfig()->isInvoicesActive()) {
            parent::exportExcelAction();
            return;
        }
        
        $fileName   = 'invoices.xml';
        $grid       = $this->getLayout()->createBlock('salesexport/adminhtml_sales_invoice_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
}
