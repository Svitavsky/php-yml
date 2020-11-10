<?php

class Validator
{
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
        'url' => 'required|string|url|max[2048]',
        'price' => 'required|float',
        'price_from' => 'boolean',
        'oldprice' => 'float|compare[oldprice,>,price]',
        'enable_auto_discounts' => 'boolean',
        'currencyId' => 'required|boolean',
        'categoryId' => 'required|string|max[18]',
        'picture' => 'required|string|url|max[512]',
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
        $rulesList = self::FIELDS_RULES + $this->typeFields;

        foreach ($offers as $offer) {
            foreach ($offer as $key => $value) {
                if (!isset($rulesList[$key])) {
                    continue;
                }

                $rules = explode('|', $rulesList[$key]);


            }
        }

        return $offers;
    }
}