<?php

namespace src;

class Parser
{
    private $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public function parse()
    {
        $xml = simplexml_load_file($this->filename);

        $data = [
            'date' => (string)$xml['date'],
            'name' => (string)$xml->shop->name,
            'url' => (string)$xml->shop->url,
            'currencies' => $this->currencies($xml->shop->currencies->currency),
            'categories' => $this->categories($xml->shop->categories->category),
            'offers' => $this->offers($xml->shop->offers->offer)
        ];

        return $data;
    }

    private function currencies($data)
    {
        $currencies = [];
        foreach ($data as $row) {
            $id = (string)$row['id'];
            $rate = (string)$row['rate'];
            $currencies[$id] = $rate;
        }

        return $currencies;
    }

    private function categories($data)
    {
        $categories = [];
        foreach ($data as $row) {
            $id = (string)$row['id'];
            $name = (string)$row;
            $parentId = isset($row['parentId']) ? (string)$row['parentId'] : '';
            $categories[] = compact('id', 'name', 'parentId');
        }

        return $categories;
    }

    private function offers($data)
    {
        $offers = [];
        foreach ($data as $row) {
            $offer['id'] = (string)$row['id'];
            $offer['available'] = (string)$row['available'];

            foreach ($row as $subRow) {
                $field = $subRow->getName();

                switch ($field) {
                    case 'barcode':
                        if (isset($offer[$field]) || empty($data->{$field})) {
                            continue 2;
                        }

                        $value = $this->getMultiple($field, $data);
                        break;
                    case 'param':
                        if (isset($offer[$field]) || empty($data->{$field})) {
                            continue 2;
                        }

                        $value = $this->getParams($data);
                        break;
                    case 'delivery-options':
                    case 'pickup-options':

                        break;
                    default:
                        $value = (string)$subRow;
                        break;
                }

                $offer[$field] = $value;
            }

            $offers[] = $offer;
        }

        return $offers;
    }

    private function getMultiple(string $field, $data)
    {
        $array = [];
        foreach ($data->{$field} as $row) {
            $array[] = (string)$row;
        }

        return $array;
    }

    private function getParams($data)
    {
        $subData = [];
        foreach ($data->param as $row) {
            $name = (string)$row['name'];
            $value = (string)$row;
            $unit = isset($row['unit']) ? (string)$row['unit'] : '';;
            $subData[] = compact('name', 'value', 'unit');
        }

        return $subData;
    }
}