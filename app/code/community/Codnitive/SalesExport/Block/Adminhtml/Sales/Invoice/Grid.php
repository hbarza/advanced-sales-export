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
 * Adminhtml sales orders grid
 *
 * @category   Codnitive
 * @package    Codnitive_SalesExport
 * @author     Hassan Barza <support@codnitive.com>
 */
class Codnitive_SalesExport_Block_Adminhtml_Sales_Invoice_Grid 
    extends Codnitive_SalesExport_Block_Adminhtml_Widget_Grid
{
    protected $_xmlStyles    = array();
    
    protected $_xmlItemsRows = array();
    
    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_invoice_export_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        
        if ($exportPageSize = $this->_getConfig()->getExportPageSize()) {
            $this->_exportPageSize = $exportPageSize;
        }
    }
    
    protected function _getConfig()
    {
        return Mage::getModel('salesexport/config');
    }

    protected function _getCollectionClass()
    {
        return 'sales/order_invoice_grid_collection';
    }

    protected function _prepareCollection()
    {
        if (!$this->_getConfig()->isInvoicesActive()) {
            parent::_prepareCollection();
            return;
        }
        
        $invoicesIds = $this->getRequest()->getParam('internal_invoice_ids');
        $collection = Mage::getResourceModel($this->_getCollectionClass())
                ->addAttributeToSelect('*');
        
        if (!empty($invoicesIds)) {
            $invoicesIds = explode(',', $invoicesIds);
            $collection->addAttributeToFilter('entity_id', array('in' => $invoicesIds));
        }
        
        $this->setCollection($collection);
    }

    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', array(
            'header'    => Mage::helper('salesexport')->__('Invoice #'),
            'index'     => 'increment_id',
            'type'      => 'text',
        ));
        
        if ($this->_getConfig()->isProductRowExport()) {
            $this->_productColumns();
        }
        else {
            $this->_invoiceColumns();
        }
    }

    protected function _invoiceColumns()
    {
        if ($columns = $this->_getConfig()->getInvoiceGridColumns()) {
            foreach ($columns as $key => $val) {
                $array = array();
                foreach ($val as $k => $v) {
                    if (is_array($v)) {
                        $model  = Mage::getModel($v['model']);
                        $method = $v['method'];
                        $array[$k] = $model->$method();
                    }
                    else {
                        $array[$k] = $v;
                    }
                }
                $mergedArray = array_merge($array, array(
                    'header'    => Mage::helper('salesexport')->__($val['header']),
                    'index'     => $key
                ));
                $this->addColumn($key, $mergedArray);
            }
            return;
        }
        
        $this->addColumn('created_at', array(
            'header'    => Mage::helper('salesexport')->__('Invoice Date'),
            'index'     => 'created_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('order_increment_id', array(
            'header'    => Mage::helper('salesexport')->__('Order #'),
            'index'     => 'order_increment_id',
            'type'      => 'text',
        ));

        $this->addColumn('order_created_at', array(
            'header'    => Mage::helper('salesexport')->__('Order Date'),
            'index'     => 'order_created_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('billing_name', array(
            'header' => Mage::helper('salesexport')->__('Bill to Name'),
            'index' => 'billing_name',
        ));

        $this->addColumn('state', array(
            'header'    => Mage::helper('salesexport')->__('Status'),
            'index'     => 'state',
            'type'      => 'options',
            'options'   => Mage::getModel('sales/order_invoice')->getStates(),
        ));

        $this->addColumn('grand_total', array(
            'header'    => Mage::helper('customer')->__('Amount'),
            'index'     => 'grand_total',
            'type'      => 'currency',
            'align'     => 'right',
            'currency'  => 'order_currency_code',
        ));
    }

    protected function _productColumns()
    {
        if ($columns = $this->_getConfig()->getProductGridColumns()) {
            foreach ($columns as $key => $val) {
                $array = array();
                foreach ($val as $k => $v) {
                    if (is_array($v)) {
                        $model  = Mage::getModel($v['model']);
                        $method = $v['method'];
                        $array[$k] = $model->$method();
                    }
                    else {
                        $array[$k] = $v;
                    }
                }
                $mergedArray = array_merge($array, array(
                    'header'    => Mage::helper('salesexport')->__($val['header']),
                    'index'     => $key
                ));
                $this->addColumn($key, $mergedArray);
            }
            return;
        }
        
        $this->addColumn('sku', array(
            'header'    => Mage::helper('salesexport')->__('SKU'),
            'index'     => 'sku',
            'type'      => 'text',
        ));
        
        $this->addColumn('name', array(
            'header'    => Mage::helper('salesexport')->__('Name'),
            'index'     => 'name',
            'type'      => 'text',
        ));
        
        $this->addColumn('qty', array(
            'header'    => Mage::helper('salesexport')->__('Qty'),
            'index'     => 'qty',
            'type'      => 'text',
        ));
        
        $this->addColumn('created_at', array(
            'header'    => Mage::helper('salesexport')->__('Invoice Date'),
            'index'     => 'created_at',
            'type'      => 'datetime',
        ));
    }
}
