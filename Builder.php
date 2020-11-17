<?php

namespace src;

use DOMDocument;

class Builder
{
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

    /**
     * Создание контента из шаблона для YML
     * @return string
     */
    public function buildOld(): string
    {
        extract($this->data);

        ob_start();
        include 'template.php';
        return ob_get_clean();
    }

    public function build()
    {
        $config = $this->data['config'];
        $categories = $this->data['categories'];
        $offers = $this->data['offers'];

        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        $catalog = $xml->createElement('yml_catalog');
        $catalog->setAttribute('date', $config['date']);

        $shop = $xml->createElement('shop');

        $name = $xml->createElement('name', $config['companyName']);
        $company = $xml->createElement('company', $config['companyDescription']);
        $url = $xml->createElement('url', $config['companyWebsite']);
        $shop->appendChild($name);
        $shop->appendChild($company);
        $shop->appendChild($url);

        $currencies = $xml->createElement('currencies');
        foreach ($config['currencies'] as $id => $rate) {
            $currency = $xml->createElement('currency');
            $currency->setAttribute('id', $id);
            $currency->setAttribute('rate', $rate);
            $currencies->appendChild($currency);
        }
        $shop->appendChild($currencies);

        $categoriesEl = $xml->createElement('categories');
        foreach ($categories as $category) {
            $categoryEl = $xml->createElement('category', $category['name']);
            $categoryEl->setAttribute('id', $category['id']);

            if(isset($category['parentId'])) {
                $categoryEl->setAttribute('parentId', $category['parentId']);
            }

            $categoriesEl->appendChild($categoryEl);
        }
        $shop->appendChild($categoriesEl);

        $offersEl = $xml->createElement('offers');
        foreach($offers as $offer) {
            $offerEl = $xml->createElement('offer');
            $offerEl->setAttribute('id', $offer['id']);
            $offerEl->setIdAttribute('id', true);

            if(isset($offer['bid'])) {
                $offerEl->setAttribute('bid', $offer['bid']);
            }

            if($config['simplifiedOffers']) {
                $offerEl->setAttribute('type', 'vendor.model');

                $name = $xml->createElement('name', $offer['name']);
                $offerEl->appendChild($name);

                if(isset($offer['vendor'])) {
                    $vendor = $xml->createElement('vendor', $offer['vendor']);
                    $offerEl->appendChild($vendor);
                }
            } else {
                $name = $xml->createElement('name', $offer['name']);
                $offerEl->appendChild($name);

                $vendor = $xml->createElement('vendor', $offer['vendor']);
                $offerEl->appendChild($vendor);

                if(isset($offer['typePrefix'])) {
                    $typePrefix = $xml->createElement('typePrefix', $offer['typePrefix']);
                    $offerEl->appendChild($typePrefix);
                }
            }

            if(isset($offer['available'])) {
                $offerEl->setAttribute('available', $offer['available']);
            }

            if(isset($offer['simple'])) {
                foreach($offer['simple'] as $field => $value) {
                    $element = $xml->createElement($field, $value);
                    $offerEl->appendChild($element);
                }
            }

            $offersEl->appendChild($offerEl);
        }
        $shop->appendChild($offersEl);


        $catalog->appendChild($shop);
        $xml->appendChild($catalog);
            $done= $xml->saveXML();
            return $done;
    }
}