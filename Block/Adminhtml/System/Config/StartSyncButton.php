<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;

class StartSyncButton extends Field
{
    protected $_template = 'PrimeData_PrimeDataConnect::system/config/startSyncButton.phtml';
    /**
    * @param Context $context
    * @param array $data
    * @codeCoverageIgnore
    */
    public function __construct(Context $context , array $data = [])
    {
        parent::__construct($context , $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @codeCoverageIgnore
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getCustomUrl()
    {
        return $this->getUrl('router/controller/action');
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData([  'id' => 'start_sync_button' , 'label' => __('Start Synchronization'), ] );
        return $button->toHtml();
    }
}
