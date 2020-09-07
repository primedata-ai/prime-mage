<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;

class DatePicker extends Field
{
    protected $_coreRegistry;

    /**
     * Checkbox constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(Context $context , Registry $coreRegistry , array $data = [])
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context , $data);
    }

    /**
     * Retrieve element HTML markup.
     * @param AbstractElement $element
     * @return string
     * @codeCoverageIgnore
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = $element->getElementHtml();
        if (!$this->_coreRegistry->registry('datepicker_loaded')) {
            $this->_coreRegistry->registry('datepicker_loaded' , 1);
        }
        $html .= '<button type="button" style="display:none;" class="ui-datepicker-trigger '
            .'v-middle"><span>Select Date</span></button>';
        $html .= '<script type="text/javascript">
            require(["jquery", "jquery/ui"], function (jq) {
                jq(document).ready(function () {
                    jq("#' . $element->getHtmlId() . '").datepicker( { dateFormat: "yy-mm-dd" } );
                    jq(".ui-datepicker-trigger").removeAttr("style");
                    jq(".ui-datepicker-trigger").click(function(){
                        jq("#' . $element->getHtmlId() . '").focus();
                    });
                });
            });
            </script>';
        return $html;
    }
}
