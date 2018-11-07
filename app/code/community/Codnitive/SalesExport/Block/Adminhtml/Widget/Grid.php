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
 * Adminhtml grid widget block
 *
 * @category   Codnitive
 * @package    Codnitive_SalesExport
 * @author     Hassan Barza <support@codnitive.com>
 */
class Codnitive_SalesExport_Block_Adminhtml_Widget_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function _exportIterateCollection($callback, array $args)
    {
        $originalCollection = $this->getCollection();
        $count   = null;
        $page    = 1;
        $counter = 1;
        $lPage   = null;
        $break   = false;

        while ($break !== true) {
            $collection = clone $originalCollection;
            $collection->setPageSize($this->_exportPageSize);
            $collection->setCurPage($page);
            $collection->load();
            if (is_null($count)) {
                $count = $collection->getSize();
                $lPage = $collection->getLastPageNumber();
            }
            if ($lPage == $page) {
                $break = true;
            }
            $page ++;

            foreach ($collection as $gridItem) {
                if ($this->_getConfig()->isProductRowExport()) {
                    $invoice = Mage::getModel('sales/order_invoice')->load($gridItem->getId());
                    $invoiceCollection = $invoice->getItemsCollection();
                    foreach ($invoiceCollection as $item) {
                        $product = Mage::getModel('catalog/product')->load($item->getId());
                        $data    = $product->getData();
                        
                        if ($this->_getConfig()->addOrderData()) {
                            $orderData = Mage::getModel('sales/order')->load($gridItem->getOrderId())->getData();
                            $data      = array_merge($data, $orderData);
                        }
                        
                        if ($this->_getConfig()->addInvoiceData()) {
                            $invoiceData = $item->getData();
                            $data        = array_merge($data, $invoiceData);
                        }
                        
                        $product->setData($data);
                        $product->setIncrementId($gridItem->getIncrementId())
                                ->setCreatedAt($gridItem->getCreatedAt());
                        $product->setCurrencyCode($gridItem->getOrderCurrencyCode());
                        
                        $arguments = array_merge(array($product), $args);
                        if (in_array($product->getShippingDescription(), $this->_getConfig()->getShippingMethods())) {
                            $arguments = array_merge($arguments,  array($counter));
                        }
                        
                        call_user_func_array(array($this, $callback), $arguments);
                        $counter++;
                    }
                }
                else {
                    $data = $gridItem->getData();
                    if ($this->_getConfig()->addOrderData()) {
                        $orderData = Mage::getModel('sales/order')->load($gridItem->getOrderId())->getData();
                        $data      = array_merge($data, $orderData);
                    }
                    $gridItem->setData($data);
                        
                    $arguments = array_merge(array($gridItem), $args);
                    if (in_array($gridItem->getShippingDescription(), $this->_getConfig()->getShippingMethods())) {
                        $arguments = array_merge($arguments,  array($counter));
                    }
                        
                    call_user_func_array(array($this, $callback), $arguments);
                    $counter++;
                }
            }
        }
    }

    protected function _exportExcelItem(Varien_Object $item, $parser = null, $styleId = null)
    {
        if (is_null($parser)) {
            $parser = new Varien_Convert_Parser_Xml_Excel();
        }

        $row = array();
        foreach ($this->_columns as $column) {
            if (!$column->getIsSystem()) {
                $row[] = $column->getRowFieldExport($item);
            }
        }
        $this->_xmlItemsRows[] = $parser->getRowXml($row, $styleId);
        if ($styleId) {
            $this->_xmlStyles[] = $parser->getRowStyle($styleId);
        }
    }

    public function getExcelFile($sheetName = '')
    {
        $this->_isExport = true;
        $this->_prepareGrid();

        $parser = new Codnitive_SalesExport_Model_Varien_Convert_Parser_Xml_Excel();
        $io     = new Varien_Io_File();

        $path = Mage::getBaseDir('var') . DS . 'export' . DS;
        $name = md5(microtime());
        $file = $path . DS . $name . '.xml';

        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $path));
        $io->streamOpen($file, 'w+');
        $io->streamLock(true);
        $io->streamWrite($parser->getHeaderXml());

        $this->_exportIterateCollection('_exportExcelItem', array($parser));
        
        $io->streamWrite($parser->getStyleStartXml());
        foreach ($this->_xmlStyles as $style) {
            $io->streamWrite($style);
        }
        $io->streamWrite($parser->getStyleEndXml());
        
        $io->streamWrite($parser->getWorkSheetStart($sheetName));
            
        $io->streamWrite($parser->getRowXml($this->_getExportHeaders()));
        foreach ($this->_xmlItemsRows as $data) {
            $io->streamWrite($data);
        }

        if ($this->getCountTotals()) {
            $io->streamWrite($parser->getRowXml($this->_getExportTotals()));
        }

        $io->streamWrite($parser->getFooterXml());
        $io->streamUnlock();
        $io->streamClose();

        return array(
            'type'  => 'filename',
            'value' => $file,
            'rm'    => true
        );
    }
}
