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
 * Convert excel xml parser
 *
 * @category   Codnitive
 * @package    Codnitive_SalesExport
 * @author     Hassan Barza <support@codnitive.com>
 */
class Codnitive_SalesExport_Model_Varien_Convert_Parser_Xml_Excel extends Varien_Convert_Parser_Xml_Excel
{
    protected function _getConfig()
    {
        return Mage::getModel('salesexport/config');
    }

    public function getHeaderXml()
    {
        $xml = '<'.'?xml version="1.0"?'.'><'.'?mso-application progid="Excel.Sheet"?'
            . '><Workbook'
            . ' xmlns="urn:schemas-microsoft-com:office:spreadsheet"'
            . ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'
            . ' xmlns:x="urn:schemas-microsoft-com:office:excel"'
            . ' xmlns:x2="http://schemas.microsoft.com/office/excel/2003/xml"'
            . ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"'
            . ' xmlns:o="urn:schemas-microsoft-com:office:office"'
            . ' xmlns:html="http://www.w3.org/TR/REC-html40"'
            . ' xmlns:c="urn:schemas-microsoft-com:office:component:spreadsheet">'
            . '<OfficeDocumentSettings xmlns="urn:schemas-microsoft-com:office:office">'
            . '</OfficeDocumentSettings>'
            . '<ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">'
            . '</ExcelWorkbook>';
        return $xml;
    }
    
    public function getWorkSheetStart($sheetName = '')
    {
        if (empty($sheetName)) {
            $sheetName = 'Sheet 1';
        }
        $sheetName = htmlspecialchars($sheetName);
        $xml = '<Worksheet ss:Name="' . $sheetName . '">'
            . '<Table ss:StyleID="sb1">';
        
        return $xml;
    }

    public function getRowXml(array $row, $styleId = '')
    {
        $xmlHeader = '<'.'?xml version="1.0"?'.'>' . "\n";
        $xmlRegexp = '/^<cell><row>(.*)?<\/row><\/cell>\s?$/ms';

        if (is_null($this->_xmlElement)) {
            $xmlString = $xmlHeader . '<cell><row></row></cell>';
            $this->_xmlElement = new SimpleXMLElement($xmlString, LIBXML_NOBLANKS);
        }

        $xmlData = array();
        if (!empty($styleId)) {
            $styleId = ' ss:StyleID="s'.$styleId.'"';
        }
        $xmlData[] = '<Row'.$styleId.'>';
        foreach ($row as $value) {
            $this->_xmlElement->row = htmlspecialchars($value);
            $value = str_replace($xmlHeader, '', $this->_xmlElement->asXML());
            $value = preg_replace($xmlRegexp, '\\1', $value);
            $dataType = "String";
            if (is_numeric($value)) {
                $dataType = "Number";
                $value = trim($value);
            }
            $value = str_replace("\r\n", '&#10;', $value);
            $value = str_replace("\r", '&#10;', $value);
            $value = str_replace("\n", '&#10;', $value);

            $xmlData[] = '<Cell><Data ss:Type="'.$dataType.'">'.$value.'</Data></Cell>';
        }
        $xmlData[] = '</Row>';

        return join('', $xmlData);
    }
    
    public function getStyleStartXml()
    {
        $config = $this->_getConfig();
        $xml = '<Styles>'
               .'<Style ss:ID="Default" ss:Name="Normal">
                    <Alignment ss:Vertical="'.$config->getDefaultVerticalAlignment().'"/>
                    <Borders/>
                    <Font ss:FontName="'.$config->getDefaultFontName()
                        .'" ss:Size="'.$config->getDefaultFontSize()
                        .'" ss:Color="'.$config->getDefaultTextColor().'"/>
                    <Interior ss:Color="'.$config->getDefaultBackgroundColor().'" ss:Pattern="Solid"/>
                    <NumberFormat/>
                    <Protection/>
                </Style>
                <Style ss:ID="sb1">
                    <Borders>
                        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="'.$config->getDefaultBorderColor().'"/>
                        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="'.$config->getDefaultBorderColor().'"/>
                        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="'.$config->getDefaultBorderColor().'"/>
                        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="'.$config->getDefaultBorderColor().'"/>
                    </Borders>
                </Style>';
        
        return $xml;
    }
    
    public function getStyleEndXml()
    {
        return '</Styles>';
    }
    
    public function getRowStyle($styleId)
    {
        $config = $this->_getConfig();
        $xml = '<Style ss:ID="s'.$styleId.'">
            <Font ss:FontName="'.$config->getRowFontName().'" ss:Size="'.$config->getRowFontSize().'" ss:Color="'.$config->getRowTextColor().'"/>
            <Interior ss:Color="'.$config->getRowBackgroundColor().'" ss:Pattern="Solid"/>
        </Style>';
        
        return $xml;
    }
}
