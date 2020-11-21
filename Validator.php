<?php

namespace src;

class Validator
{
    // Правила для URL
    const URL_REGEX = "/(https?:\/\/(\S*?\.\S*?))([\s)\[\]{},;\"\':<]|\.\s|$)/i";
    const URL_MAX_LENGTH = 2048;

    // Правила для изображений
    const IMAGE_URL_MAX_LENGTH = 512;
    const IMAGE_VALID_EXTENSIONS = ['png', 'jpg'];

    // Возможные значения для полей boolean
    const BOOLEAN_TRUE = ['yes', 'true', '1'];
    const BOOLEAN_FALSE = ['no', 'false', '0'];

    // Формат записи для поля dimensions
    const DIMENSIONS_REGEX = '/[0-9]*\.?[0-9]*\/[0-9]*\.?[0-9]*\/[0-9]*\.?[0-9]*/';

    // Замена кода валюты, чтобы во всей выгрузке был 1 код
    const CURRENCY_REPLACEMENTS = [
        'RUR' => 'RUB',
        'RUB' => 'RUR'
    ];

    // Поля для упрощенного типа
    const SIMPLIFIED_TYPE_FIELDS = [
        'name' => 'required|string',
        'vendor' => 'string',
    ];

    // Поля для произвольного типа
    const CUSTOM_TYPE_FIELDS = [
        'model' => 'required|string',
        'vendor' => 'required|string',
        'typePrefix' => 'string',
    ];

    // Правила для атрибутов
    // (!) Правило required обязательно идет первым
    const FIELDS_RULES = [
        'id' => 'required|int|length[20]',
        'available' => 'boolean',
        'type' => 'select[medicine,books,audiobooks,artist.title,event-ticket,tour,alco]',
        'bid' => 'int',
        'vendorCode' => 'string',
        'url' => 'required|string|url',
        'price' => 'required|float',
        'price_from' => 'boolean',
        'oldprice' => 'float|greater[price]',
        'enable_auto_discounts' => 'boolean',
        'currencyId' => 'required|string|currency',
        'categoryId' => 'required|string|length[18]',
        'picture' => 'required|string|url|image',
        'supplier' => 'string|length[15]',
        'delivery' => 'boolean',
        'delivery-options' => 'array',
        'pickup' => 'boolean',
        'pickup-options' => 'array',
        'store' => 'boolean',
        'description' => 'string|length[3000]',
        'sales_notes' => 'string|length[50]',
        'min-quantity' => 'int',
        'manufacturer_warranty' => 'boolean',
        'country_of_origin' => 'string|country',
        'adult' => 'boolean',
        'barcode' => 'simplearray',
        'param' => 'array',
        'condition' => 'array',
        'credit-template' => 'string',
        'expiry' => 'string',
        'weight' => 'float',
        'dimensions' => 'dimensions',
        'downloadable' => 'boolean',
        'age_month' => 'range[0-12]',
        'age_year' => 'select[0,6,12,16,18]',
    ];

    // Правила для пложенных атрибутов
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
            'name' => 'string',
            'unit' => 'string',
            'value' => 'required|string'
        ],
        'condition' => [
            'type' => 'select[likenew,used]',
            'reason' => 'string|length[3000]'
        ]
    ];

    /**
     * Конфигурация модуля
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function validateAll(array $data)
    {
        return [
            'offers' => $this->offers($data['offers']),
            'categories' => $this->categories($data['categories'])
        ];
    }

    private function offers(array $offers)
    {
        $validated = [];
        foreach ($offers as $offer) {
            $allRules = self::FIELDS_RULES + (isset($offer['type']) ? self::CUSTOM_TYPE_FIELDS : self::SIMPLIFIED_TYPE_FIELDS);
            foreach ($allRules as $field => $rulesList) {
                $rules = explode('|', $rulesList);
                foreach ($rules as $rule) {

                    // Проверяем наличие поля
                    // Если его нет и оно обязательное - пропускаем товар
                    // Если необязательное - пропускаем правило
                    if (!isset($offer[$field])) {
                        if ($rule === 'required') {
                            continue 3;
                        }
                        continue 2;
                    }

                    $valid = $this->validate($offer, $rule, $field);

                    if ($valid === false) {
                        continue 3;
                    }
                }
            }
            $validated[] = $offer;
        }

        return $validated;
    }

    private function categories(array $categories)
    {
        return $categories;
    }

    private function validate($data, $rule, $field)
    {
        list($ruleType, $ruleOptions) = $this->getRuleOptions($rule);
        $value = is_array($data) ? $data[$field] : $data;

        switch ($ruleType) {
            case 'int':
                return $this->int($value);
            case 'float':
                return $this->float($value);
            case 'string':
                return $this->string($value);
            case 'boolean':
                return $this->boolean($value);
            case 'array':
                return $this->array($data, $field);
            case 'simplearray':
                return $this->simpleArray($data, $field);
            case 'url':
                return $this->url($value);
            case 'image':
                return $this->image($value);
            case 'greater':
                return $this->greater($data, $value, $ruleOptions);
            case 'select':
                return $this->select($value, $ruleOptions);
            case 'range':
                return $this->range($value, $ruleOptions);
            case 'dimensions':
                return $this->dimensions($value);
            case 'length':
                return $this->length($value, $ruleOptions);
            case 'currency':
                return $this->currency($value);
            case 'country':
                return $this->country($value);
            default:
                return true;
        }
    }

    private function int($value)
    {
        return filter_var($value, FILTER_VALIDATE_INT);
    }

    private function float($value)
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT);
    }

    private function string($value)
    {
        return is_string($value);
    }

    private function boolean($value)
    {
        $isTrue = $value === true || in_array($value, self::BOOLEAN_TRUE);
        $isFalse = $value === false || in_array($value, self::BOOLEAN_FALSE);

        return $isTrue || $isFalse;
    }

    private function array($data, $field)
    {
        if (!isset($data[$field])) {
            return true;
        }

        $rows = $data[$field];
        $subFields = self::ARRAY_FIELDS_RULES[$field];

        foreach ($rows as $row) {
            foreach ($subFields as $subField => $subRules) {
                $rules = explode('|', $subRules);
                foreach ($rules as $rule) {

                    // Проверяем наличие поля
                    if (!isset($row[$subField])) {
                        if ($rule === 'required') {
                            return false;
                        } else {
                            continue;
                        }
                    }

                    $result = $this->validate($row, $rule, $subField);

                    if ($result === false) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    private function simpleArray($data, $field)
    {
        if (!isset($data[$field])) {
            return true;
        }

        $rules = explode('|', self::ARRAY_FIELDS_RULES[$field]);
        foreach ($data[$field] as $value) {
            foreach ($rules as $rule) {
                $result = $this->validate($value, $rule, $field);

                if ($result === false) {
                    return false;
                }
            }
        }

        return true;
    }

    private function url($value)
    {
        $maxLength = strlen($value) <= self::URL_MAX_LENGTH;
        $match = preg_match(self::URL_REGEX, $value);
        return $maxLength && $match;
    }

    private function image($value)
    {
        $maxLength = strlen($value) <= self::IMAGE_URL_MAX_LENGTH;
        $validExtension = in_array(substr($value, -3), self::IMAGE_VALID_EXTENSIONS);
        return $maxLength && $validExtension;
    }

    private function greater($offer, $value, $greaterThan)
    {
        return $value > $offer[$greaterThan];
    }

    private function select($value, $optionsList)
    {
        $options = explode(',', $optionsList);
        return in_array($value, $options);
    }

    private function range($value, $optionsList)
    {
        $options = explode('-', $optionsList);
        return $value >= $options[0] && $value <= $options[1];
    }

    private function dimensions($value)
    {
        return preg_match(self::DIMENSIONS_REGEX, $value);
    }

    private function length($value, $length)
    {
        return strlen($value) < $length;
    }

    private function currency($value)
    {
        $currencies = array_keys($this->config['currencies']);

        // В выгрузке можно применять оба кода рубля, используем тот, который указан в конфиге
        if (in_array('RUR', $currencies) && $value === 'RUB') {
            $value = 'RUR';
        }

        if (in_array('RUB', $currencies) && $value === 'RUR') {
            $value = 'RUB';
        }

        return in_array($value, $currencies);
    }

    private function country($value)
    {
        $countries = $this->config['availableCountries'];
        return in_array($value, $countries);
    }

    private function getRuleOptions(string $rule)
    {
        preg_match('/([a-z]+)\[?([\w,.-]+)?]?/', $rule, $matches);

        $rule = $matches[1];
        $option = $matches[2] ?? null;

        return [$rule, $option];
    }
}