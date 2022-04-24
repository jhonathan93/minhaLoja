<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Jhonathan\Customer\Block\Widget;

use Magento\Framework\Data\Form\Filter\FilterInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Block\Widget\AbstractWidget;
use Magento\Framework\Locale\Bundle\DataBundle;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Data\Form\FilterFactory;
use Magento\Framework\View\Element\Html\Date;
use Magento\Framework\Api\ArrayObjectSearch;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Customer\Helper\Address;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Phrase;

/**
 * Customer date of birth attribute block
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Dob extends AbstractWidget {

    /**
     * Constants for borders of date-type customer attributes
     */
    const MIN_DATE_RANGE_KEY = 'date_range_min';

    const MAX_DATE_RANGE_KEY = 'date_range_max';

    /**
     * Date inputs
     * @var array
     */
    protected $_dateInputs = [];

    /**
     * @var Date
     */
    protected $dateElement;

    /**
     * @var FilterFactory
     */
    protected $filterFactory;

    /**
     * JSON Encoder
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @param Context $context
     * @param Address $addressHelper
     * @param CustomerMetadataInterface $customerMetadata
     * @param Date $dateElement
     * @param FilterFactory $filterFactory
     * @param array $data
     * @param EncoderInterface|null $encoder
     * @param ResolverInterface|null $localeResolver
     */
    public function __construct(
        Context $context,
        Address $addressHelper,
        CustomerMetadataInterface $customerMetadata,
        Date $dateElement,
        FilterFactory $filterFactory,
        array $data = [],
        ?EncoderInterface $encoder = null,
        ?ResolverInterface $localeResolver = null
    ) {
        $this->dateElement = $dateElement;
        $this->filterFactory = $filterFactory;
        $this->encoder = $encoder ?? ObjectManager::getInstance()->get(EncoderInterface::class);
        $this->localeResolver = $localeResolver ?? ObjectManager::getInstance()->get(ResolverInterface::class);
        parent::__construct($context, $addressHelper, $customerMetadata, $data);
    }

    /**
     * @inheritdoc
     */
    public function _construct() {
        parent::_construct();
        $this->setTemplate('Magento_Customer::widget/dob.phtml');
    }

    /**
     * Check if dob attribute enabled in system
     * @return bool
     */
    public function isEnabled(): bool {
        $attributeMetadata = $this->_getAttribute('dob');
        return $attributeMetadata ? (bool)$attributeMetadata->isVisible() : false;
    }

    /**
     * Check if dob attribute marked as required
     * @return bool
     */
    public function isRequired(): bool {
        $attributeMetadata = $this->_getAttribute('dob');
        return $attributeMetadata && $attributeMetadata->isRequired();
    }

    /**
     * Set date
     *
     * @param string $date
     * @return $this
     */
    public function setDate($date): self {
        $this->setTime($this->filterTime($date));
        $this->setValue($this->applyOutputFilter($date));
        return $this;
    }

    /**
     * Sanitizes time
     * @param mixed $value
     * @return bool|int
     */
    private function filterTime($value) {
        $time = false;
        if ($value) {
            if ($value instanceof \DateTimeInterface) {
                $time =  $value->getTimestamp();
            } elseif (is_numeric($value)) {
                $time = $value;
            } elseif (is_string($value)) {
                $time = strtotime($value);
                $time = $time === false ? $this->_localeDate->date($value, null, false, false)->getTimestamp() : $time;
            }
        }

        return $time;
    }

    /**
     * Return Data Form Filter or false
     * @return FilterInterface|false
     */
    protected function getFormFilter() {
        $attributeMetadata = $this->_getAttribute('dob');
        $filterCode = $attributeMetadata->getInputFilter();
        if ($filterCode) {
            $data = [];
            if ($filterCode == 'date') {
                $data['format'] = $this->getDateFormat();
            }
            $filter = $this->filterFactory->create($filterCode, $data);
            return $filter;
        }
        return false;
    }

    /**
     * Apply output filter to value
     * @param string $value
     * @return string|null
     */
    protected function applyOutputFilter($value): ?string {
        $filter = $this->getFormFilter();
        if ($filter && $value) {
            $value = date('Y-m-d', $this->getTime());
            $value = $filter->outputFilter($value);
        }
        return $value;
    }

    /**
     * Get day
     * @return string|bool
     */
    public function getDay() {
        return $this->getTime() ? date('d', $this->getTime()) : '';
    }

    /**
     * Get month
     * @return string|bool
     */
    public function getMonth() {
        return $this->getTime() ? date('m', $this->getTime()) : '';
    }

    /**
     * Get year
     * @return string|bool
     */
    public function getYear() {
        return $this->getTime() ? date('Y', $this->getTime()) : '';
    }

    /**
     * Return label
     * @return Phrase
     */
    public function getLabel(): Phrase {
        return __('Date of Birth');
    }

    /**
     * Retrieve store attribute label
     * @param string $attributeCode
     * @return string
     */
    public function getStoreLabel(string $attributeCode): string {
        $attribute = $this->_getAttribute($attributeCode);
        return $attribute ? __($attribute->getStoreLabel()) : '';
    }

    /**
     * Create correct date field
     * @return string
     */
    public function getFieldHtml(): string {
        $this->dateElement->setData(
            [
                'extra_params' => $this->getHtmlExtraParams(),
                'name' => $this->getHtmlId(),
                'id' => $this->getHtmlId(),
                'class' => $this->getHtmlClass(),
                'value' => $this->getValue(),
                'date_format' => $this->getDateFormat(),
                'image' => $this->getViewFileUrl('Magento_Theme::calendar.png'),
                'years_range' => '-120y:c+nn',
                'max_date' => '-1d',
                'change_month' => 'true',
                'change_year' => 'true',
                'show_on' => 'both',
                'first_day' => $this->getFirstDay()
            ]
        );

        return $this->dateElement->getHtml();
    }

    /**
     * Return id
     * @return string
     */
    public function getHtmlId(): string {
        return 'dob';
    }

    /**
     * Return data-validate rules
     * @return string
     */
    public function getHtmlExtraParams(): string {
        $validators = [];
        if ($this->isRequired()) {
            $validators['required'] = true;
        }
        $validators['validate-date'] = [
            'dateFormat' => $this->getDateFormat()
        ];
        $validators['validate-dob'] = [
            'dateFormat' => $this->getDateFormat()
        ];

        return 'data-validate="' . $this->_escaper->escapeHtml(json_encode($validators)) . '"';
    }

    /**
     * Returns format which will be applied for DOB in javascript
     * @return string
     */
    public function getDateFormat(): string {
        $dateFormat = $this->setTwoDayPlaces($this->_localeDate->getDateFormatWithLongYear());
        /** Escape RTL characters which are present in some locales and corrupt formatting */
        $escapedDateFormat = preg_replace('/[^MmDdYy\/\.\-]/', '', $dateFormat);

        return $escapedDateFormat;
    }

    /**
     * Add date input html
     * @param string $code
     * @param string $html
     * @return void
     */
    public function setDateInput(string $code, string $html) {
        $this->_dateInputs[$code] = $html;
    }

    /**
     * Sort date inputs by dateformat order of current locale
     * @param bool $stripNonInputChars
     * @return string
     */
    public function getSortedDateInputs(bool $stripNonInputChars = true): string {
        $mapping = [];
        if ($stripNonInputChars) {
            $mapping['/[^medy]/i'] = '\\1';
        }
        $mapping['/m{1,5}/i'] = '%1$s';
        $mapping['/e{1,5}/i'] = '%2$s';
        $mapping['/d{1,5}/i'] = '%2$s';
        $mapping['/y{1,5}/i'] = '%3$s';

        $dateFormat = preg_replace(array_keys($mapping), array_values($mapping), $this->getDateFormat());

        return sprintf($dateFormat, $this->_dateInputs['m'], $this->_dateInputs['d'], $this->_dateInputs['y']);
    }

    /**
     * Return minimal date range value
     * @return string|null
     */
    public function getMinDateRange(): ?string {
        $dob = $this->_getAttribute('dob');
        if ($dob !== null) {
            $rules = $this->_getAttribute('dob')->getValidationRules();
            $minDateValue = ArrayObjectSearch::getArrayElementByName(
                $rules,
                self::MIN_DATE_RANGE_KEY
            );
            if ($minDateValue !== null) {
                return date("Y/m/d", $minDateValue);
            }
        }
        return null;
    }

    /**
     * Return maximal date range value
     * @return string|null
     */
    public function getMaxDateRange(): ?string {
        $dob = $this->_getAttribute('dob');
        if ($dob !== null) {
            $rules = $this->_getAttribute('dob')->getValidationRules();
            $maxDateValue = ArrayObjectSearch::getArrayElementByName(
                $rules,
                self::MAX_DATE_RANGE_KEY
            );
            if ($maxDateValue !== null) {
                return date("Y/m/d", $maxDateValue);
            }
        }
        return null;
    }

    /**
     * Return first day of the week
     * @return int
     */
    public function getFirstDay(): int {
        return (int)$this->_scopeConfig->getValue('general/locale/firstday', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get translated calendar config json formatted
     * @return string
     */
    public function getTranslatedCalendarConfigJson(): string {
        $localeData = (new DataBundle())->get($this->localeResolver->getLocale());
        $monthsData = $localeData['calendar']['gregorian']['monthNames'];
        $daysData = $localeData['calendar']['gregorian']['dayNames'];

        return $this->encoder->encode(
            [
                'closeText' => __('Done'),
                'prevText' => __('Prev'),
                'nextText' => __('Next'),
                'currentText' => __('Today'),
                'monthNames' => array_values(iterator_to_array($monthsData['format']['wide'])),
                'monthNamesShort' => array_values(iterator_to_array($monthsData['format']['abbreviated'])),
                'dayNames' => array_values(iterator_to_array($daysData['format']['wide'])),
                'dayNamesShort' => array_values(iterator_to_array($daysData['format']['abbreviated'])),
                'dayNamesMin' => array_values(iterator_to_array($daysData['format']['short'])),
            ]
        );
    }

    /**
     * Set 2 places for day value in format string
     * @param string $format
     * @return string
     */
    private function setTwoDayPlaces(string $format): string {
        return preg_replace(
            '/(?<!d)d(?!d)/',
            'dd',
            $format
        );
    }

    public function getMask(): string {
        return '00/00/0000';
    }
}
