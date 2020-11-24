<?php

namespace src;

use DOMDocument;

class Builder
{
    const URL_FIELDS = [
        'url',
        'picture'
    ];

    // Возможные состояния уцененного товара
    const CONDITION_TYPES = [
        'likenew',    // Как новый, уценен из-за недостатков
        'used'        // Подержанный
    ];

    // Ограничения для товара по годам
    const AGE_YEAR = [0, 6, 12, 16, 18];

    // Ограничения для товара по месяцам
    const AGE_MONTH = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

    // Максимальная длина для описания причины уценки товара
    const CONDITION_DESCRIPTION_LENGTH = 3000;

    /**
     * Данные для шаблона
     * @var array
     */
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $shop = $this->data['shop'];
        $currencies = $this->data['currencies'];
        $categories = $this->data['categories'];
        $offers = $this->data['offers'];
        $date = date("Y-m-d H:i", time());

        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        $catalog = $xml->createElement('yml_catalog');
        $catalog->setAttribute('date', $date);

        $shopEl = $xml->createElement('shop');

        $name = $xml->createElement('name', $shop['name']);
        $company = $xml->createElement('company', $shop['description']);
        $url = $xml->createElement('url', rawurlencode(utf8_encode($shop['website'])));
        $shopEl->appendChild($name);
        $shopEl->appendChild($company);
        $shopEl->appendChild($url);

        $currenciesEl = $xml->createElement('currencies');
        foreach ($currencies as $id => $rate) {
            $currency = $xml->createElement('currency');
            $currency->setAttribute('id', $id);
            $currency->setAttribute('rate', $rate);
            $currenciesEl->appendChild($currency);
        }
        $shopEl->appendChild($currenciesEl);

        $categoriesEl = $xml->createElement('categories');
        foreach ($categories as $category) {
            $categoryEl = $xml->createElement('category', $category['name']);
            $categoryEl->setAttribute('id', $category['id']);

            if (isset($category['parentId'])) {
                $categoryEl->setAttribute('parentId', $category['parentId']);
            }

            $categoriesEl->appendChild($categoryEl);
        }
        $shopEl->appendChild($categoriesEl);

        $offersEl = $xml->createElement('offers');
        foreach ($offers as $offer) {
            $offerEl = $xml->createElement('offer');
            $offerEl->setAttribute('id', $offer['id']);
            $offerEl->setIdAttribute('id', true);

            if (isset($offer['bid'])) {
                $offerEl->setAttribute('bid', $offer['bid']);
            }

            if (isset($offer['type'])) {
                $offerEl->setAttribute('type', $offer['type']);

                $name = $xml->createElement('name', $offer['name']);
                $offerEl->appendChild($name);

                if (isset($offer['vendor'])) {
                    $vendor = $xml->createElement('vendor', $offer['vendor']);
                    $offerEl->appendChild($vendor);
                }
            } else {
                $name = $xml->createElement('name', $offer['name']);
                $offerEl->appendChild($name);

                $vendor = $xml->createElement('vendor', $offer['vendor']);
                $offerEl->appendChild($vendor);

                if (isset($offer['typePrefix'])) {
                    $typePrefix = $xml->createElement('typePrefix', $offer['typePrefix']);
                    $offerEl->appendChild($typePrefix);
                }
            }

            if (isset($offer['description'])) {
                $cdata = $xml->createCDATASection($offer['description']);
                $descriptionEl = $xml->createElement('description');
                $descriptionEl->appendChild($cdata);

                $offerEl->appendChild($descriptionEl);
            }

            if (isset($offer['barcode'])) {
                foreach ($offer['barcode'] as $barcode) {
                    $barcodeEl = $xml->createElement('barcode', $barcode);
                    $offerEl->appendChild($barcodeEl);
                }
            }

            if (isset($offer['group_id'])) {
                $offerEl->setAttribute('group_id', $offer['group_id']);
            }

            if (isset($offer['available'])) {
                $offerEl->setAttribute('available', $offer['available']);
            }

            $priceEl = $xml->createElement('price', $offer['price']);

            if (isset($offer['price_from'])) {
                $priceEl->setAttribute('from', 'true');
            }

            $offerEl->appendChild($priceEl);

            if (isset($offer['supplier'])) {
                $supplierEl = $xml->createElement('supplier');
                $supplierEl->setAttribute('ogrn', $offer['supplier']);
                $offerEl->appendChild($supplierEl);
            }

            if (isset($offer['delivery-options'])) {
                $deliveryEl = $xml->createElement('delivery-options');

                foreach ($offer['delivery-options'] as $option) {
                    $optionEl = $xml->createElement('option');
                    $optionEl->setAttribute('cost', $option['cost']);
                    $optionEl->setAttribute('days', $option['days']);

                    if (isset($option['order-before'])) {
                        $optionEl->setAttribute('order-before', $option['order-before']);
                    }

                    $deliveryEl->appendChild($optionEl);
                }

                $offerEl->appendChild($deliveryEl);
            }

            if (isset($offer['pickup-options'])) {
                $pickupEl = $xml->createElement('pickup-options');

                foreach ($offer['pickup-options'] as $option) {
                    $optionEl = $xml->createElement('option');
                    $optionEl->setAttribute('cost', $option['cost']);
                    $optionEl->setAttribute('days', $option['days']);

                    if (isset($option['order-before'])) {
                        $optionEl->setAttribute('order-before', $option['order-before']);
                    }

                    $pickupEl->appendChild($optionEl);
                }

                $offerEl->appendChild($pickupEl);
            }

            if (isset($offer['simple'])) {
                foreach ($offer['simple'] as $field => $value) {
                    if (in_array($field, self::URL_FIELDS)) {
                        $value = rawurlencode(utf8_encode($value));
                    }

                    $element = $xml->createElement($field, $value);
                    $offerEl->appendChild($element);
                }
            }

            if (isset($offer['condition'])) {
                $conditionEl = $xml->createElement('condition');
                $conditionEl->setAttribute('type', $offer['condition']['type']);

                $reasonEl = $xml->createElement('reason', $offer['condition']['reason']);
                $conditionEl->appendChild($reasonEl);

                $offerEl->appendChild($conditionEl);
            }

            if (isset($offer['credit-template'])) {
                $creditEl = $xml->createElement('credit-template');
                $creditEl->setAttribute('id', $offer['credit-template']);

                $offerEl->appendChild($creditEl);
            }

            if (isset($offer['param'])) {
                foreach ($offer['param'] as $param) {
                    $paramEl = $xml->createElement('param', $param['value']);
                    $paramEl->setAttribute('name', $param['name']);

                    if (isset($param['unit'])) {
                        $paramEl->setAttribute('unit', $param['unit']);
                    }

                    $offerEl->appendChild($paramEl);
                }
            }

            $offersEl->appendChild($offerEl);
        }
        $shopEl->appendChild($offersEl);

        $catalog->appendChild($shopEl);
        $xml->appendChild($catalog);
        $done = $xml->saveXML();
        return $done;
    }
}