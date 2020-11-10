<?php

namespace src;

class Validator
{
    const URL_MAX_LENGTH = 2048;
    const BOOLEAN_AVAILABLE_WORDS = ['yes', 'true', '1', 'no', 'false', '0'];

    const SIMPLIFIED_TYPE_FIELDS = [
        'name' => 'required|string',
        'vendor' => 'string',
    ];

    const CUSTOM_TYPE_FIELDS = [
        'model' => 'required|string',
        'vendor' => 'required|string',
        'typePrefix' => 'string',
    ];

    const FIELDS_RULES = [
        'id' => 'required|int|max[20]',
        'available' => 'boolean',
        'type' => 'select[medicine,books,audiobooks,artist.title,event-ticket,tour,alco]',
        'bid' => 'int',
        'vendorCode' => 'string',
        'url' => 'required|string|url',
        'price' => 'required|float',
        'price_from' => 'boolean',
        'oldprice' => 'float|compare[oldprice,>,price]',
        'enable_auto_discounts' => 'boolean',
        'currencyId' => 'required|boolean',
        'categoryId' => 'required|string|max[18]',
        'picture' => 'required|string|url|image|max[512]',
        'supplier' => 'string|max[15]',
        'delivery' => 'boolean',
        'delivery-options' => 'array',
        'pickup' => 'boolean',
        'pickup-options' => 'array',
        'store' => 'boolean',
        'description' => 'required|string|max[3000]',
        'sales_notes' => 'string|max[50]',
        'min-quantity' => 'int',
        'manufacturer_warranty' => 'boolean',
        'country_of_origin' => 'string',
        'adult' => 'boolean',
        'barcode' => 'array',
        'param' => 'array',
        'condition' => 'array',
        'credit-template' => 'string',
        'expiry' => 'string',
        'weight' => 'float',
        'dimensions' => 'regex[[0-9]*\.?[0-9]*/[0-9]*\.?[0-9]*/[0-9]*\.?[0-9]*]',
        'downloadable' => 'float',
        'age_month' => 'select[0,1,2,3,4,5,6,7,8,9,10,11,12]',
        'age_year' => 'select[0,6,12,16,18]',
    ];

    const ARRAY_FIELDS_RULES = [
        'delivery-options' => [
            'cost' => 'required|int',
            'days' => 'required|string',
            'order-before' => 'int|range[0-24]'
        ],
        'pickup-options' => [
            'cost' => 'required|int',
            'days' => 'required|string',
            'order-before' => 'int|range[0-24]'
        ],
        'barcode' => 'string',
        'param' => [
            'name' => 'required|string',
            'unit' => 'string',
            'value' => 'required|string'
        ],
        'condition' => [
            'type' => 'select[likenew,used]',
            'reason' => 'string|max[3000]'
        ]
    ];

    /**
     * @var array
     */
    private $typeFields;

    public function __construct(bool $simplified)
    {
        $this->typeFields = $simplified ? self::SIMPLIFIED_TYPE_FIELDS : self::CUSTOM_TYPE_FIELDS;
    }

    /**
     * @param array $offers
     * @return array
     */
    public function validate(array $offers)
    {
        $allRules = self::FIELDS_RULES + $this->typeFields;

        foreach ($offers as $offer) {
            foreach ($allRules as $field => $rulesList) {
                $rules = explode('|', $rulesList);
                foreach ($rules as $rule) {
                    list($ruleType, $ruleOptions) = $this->getRuleOptions($rule);

                    if (!isset($offer[$field])) {
                        if ($ruleType === 'required') {
                            continue 3;
                        } else {
                            continue 2;
                        }
                    }

                    $value = &$offer[$field];

                    switch ($ruleType) {
                        case 'int':
                            $this->validateInt($value);
                            break;
                        case 'float':
                            $this->validateFloat($value);
                            break;
                        case 'string':
                            $this->validateString($field, $value);
                            break;
                        case 'boolean':
                            if(!$this->validateBoolean($value)) {
                                continue 2;
                            }
                            break;
                        case 'url':
                            $this->validateUrl($value);
                            break;
                        case 'array':
                            $this->validateArray($value);
                            break;
                        default:
                            continue 2;
                            break;
                    }
                }
            }
        }

        return $offers;
    }

    private function validateInt(&$value)
    {
        if (!is_int($value)) {
            $value = intval($value);
        }
    }

    private function validateFloat(&$value)
    {
        if (!is_float($value)) {
            $value = floatval($value);
        }
    }

    private function validateString(string $fieldType, &$value)
    {
        if ($fieldType === 'description') {
            return strpos($value, '<![CDATA[') !== false ? $value : "<![CDATA[{$value}]]";
        }

        return htmlspecialchars($value);
    }

    private function validateBoolean($value)
    {
        return in_array(strval($value), self::BOOLEAN_AVAILABLE_WORDS);
    }

    private function validateArray($value)
    {

    }

    private function validateUrl($value)
    {

    }

    private function getRuleOptions(string $rule)
    {
        $optionStart = strpos($rule, '[');
        if ($optionStart) {
            $ruleType = substr($rule, 0, $optionStart);
            $option = substr($rule, $optionStart, strlen($rule) - 1);
            return [$ruleType, $option];
        }

        return [$rule, null];
    }
}