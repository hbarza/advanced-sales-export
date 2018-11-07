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

class Codnitive_SalesExport_Model_Config
{
    
    const PATH_NAMESPACE      = 'codnitivesalesexport';
    const EXTENSION_NAMESPACE = 'salesexport';
    const ORDERS_NAMESPACE    = 'ordersexport';
    const INVOICES_NAMESPACE  = 'invoicesexport';
    
    const EXTENSION_NAME    = 'Advanced Sales Export';
    const EXTENSION_VERSION = '1.0.00';
    const EXTENSION_EDITION = 'Basic';

    public static function getNamespace()
    {
        return self::PATH_NAMESPACE . '/' . self::EXTENSION_NAMESPACE . '/';
    }
    
    public function getExtensionName()
    {
        return self::EXTENSION_NAME;
    }
    
    public static function getOrdersNamespace()
    {
        return self::PATH_NAMESPACE . '/' . self::ORDERS_NAMESPACE . '/';
    }
    
    public static function getInvoicesNamespace()
    {
        return self::PATH_NAMESPACE . '/' . self::INVOICES_NAMESPACE . '/';
    }
    
    public function getExtensionVersion()
    {
        return self::EXTENSION_VERSION;
    }
    
    public function getExtensionEdition()
    {
        return self::EXTENSION_EDITION;
    }

    public function isActive()
    {
        return Mage::getStoreConfigFlag(self::getNamespace() . 'active');
    }
    
    public function isOrdersActive()
    {
        if (!$this->isActive()) {
            return false;
        }
        return Mage::getStoreConfigFlag($this->getCurrentNamespace() . 'active');
    }
    
    public function isInvoicesActive()
    {
        if (!$this->isActive()) {
            return false;
        }
        return Mage::getStoreConfigFlag($this->getCurrentNamespace() . 'active');
    }
    
    public function getExportPageSize()
    {
        if (!$this->isInvoicesActive()) {
            return 0;
        }
        return (int) Mage::getStoreConfig($this->getCurrentNamespace() . 'export_page_size');
    }
    
    public function isProductRowExport()
    {
        if (!$this->isInvoicesActive()) {
            return false;
        }
        return Mage::getStoreConfigFlag($this->getCurrentNamespace() . 'export_product_row');
    }
    
    public function getInvoiceGridColumns($assoc = true)
    {
        if (!$this->isInvoicesActive()) {
            return false;
        }
        $value = Mage::getStoreConfig($this->getCurrentNamespace() . 'invoice_grid_columns');
        return json_decode($value, $assoc);
    }
    
    public function getProductGridColumns($assoc = true)
    {
        if (!$this->isInvoicesActive()) {
            return false;
        }
        $value = Mage::getStoreConfig($this->getCurrentNamespace() . 'product_grid_columns');
        return json_decode($value, $assoc);
    }
    
    public function addOrderData()
    {
        if (!$this->isInvoicesActive()) {
            return false;
        }
        return Mage::getStoreConfigFlag($this->getCurrentNamespace() . 'add_order_data');
    }
    
    public function addInvoiceData()
    {
        if (!$this->isInvoicesActive()) {
            return false;
        }
        return Mage::getStoreConfigFlag($this->getCurrentNamespace() . 'add_invoice_data');
    }
    
    public function getDefaultVerticalAlignment()
    {
        return Mage::getStoreConfig($this->getCurrentNamespace() . 'default_vertical_alignment');
    }
    
    public function getDefaultFontName()
    {
        return Mage::getStoreConfig($this->getCurrentNamespace() . 'default_font_name');
    }
    
    public function getDefaultFontSize()
    {
        return Mage::getStoreConfig($this->getCurrentNamespace() . 'default_font_size');
    }
    
    public function getDefaultTextColor()
    {
        return Mage::getStoreConfig($this->getCurrentNamespace() . 'default_text_color');
    }
    
    public function getDefaultBackgroundColor()
    {
        return Mage::getStoreConfig($this->getCurrentNamespace() . 'default_background_color');
    }
    
    public function getDefaultBorderColor()
    {
        return Mage::getStoreConfig($this->getCurrentNamespace() . 'default_border_color');
    }
    
    public function getShippingMethods($asArray = true)
    {
        $shippingMethods = Mage::getStoreConfig($this->getCurrentNamespace() . 'shipping_methods');
        if ($asArray) {
            $shippingMethods = preg_split('/\n\r|\n|\r/', $shippingMethods);
            $keysToRemove = array_keys($shippingMethods, '');
            foreach ($keysToRemove as $k) {
                unset($shippingMethods[$k]);
            }
        }
        return $shippingMethods;
    }
    
    public function getRowFontName()
    {
        return Mage::getStoreConfig($this->getCurrentNamespace() . 'row_font_name');
    }
    
    public function getRowFontSize()
    {
        return Mage::getStoreConfig($this->getCurrentNamespace() . 'row_font_size');
    }
    
    public function getRowTextColor()
    {
        return Mage::getStoreConfig($this->getCurrentNamespace() . 'row_text_color');
    }
    
    public function getRowBackgroundColor()
    {
        return Mage::getStoreConfig($this->getCurrentNamespace() . 'row_background_color');
    }

    public function getCurrentNamespace()
    {
        $section = Mage::helper('salesexport')->getSalesSection();
        switch ($section) {
            case 'sale':
                $nameSpace = self::getOrdersNamespace();
                break;
            
            case 'invoice':
                $nameSpace = self::getInvoicesNamespace();
                break;
            
            default:
                $nameSpace = self::getOrdersNamespace();
                break;
        }
        
        return $nameSpace;
    }
    
}
