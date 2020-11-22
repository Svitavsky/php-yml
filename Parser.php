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

        return [
            'date' => (string)$xml['date'],
            'shop' => $this->shop($xml->shop),
            'currencies' => $this->currencies($xml->shop->currencies->currency),
            'categories' => $this->categories($xml->shop->categories->category),
            'offers' => $this->offers($xml->shop->offers->offer)
        ];
    }

    private function shop($data)
    {
        return [
            'name' => (string)$data->name,
            'description' => (string)$data->company,
            'website' => rawurldecode(utf8_decode((string)$data->url)),
        ];
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
            $offer = [
                'id' => (string)$row['id']
            ];

            if (isset($row['available'])) {
                $offer['available'] = (string)$row['available'];
            }

            if (isset($row['bid'])) {
                $offer['bid'] = (string)$row['bid'];
            }

            if (isset($row['group_id'])) {
                $offer['group_id'] = (string)$row['group_id'];
            }

            if (isset($row['type'])) {
                $offer['type'] = (string)$row['type'];
            }

            foreach ($row as $subRow) {
                $field = $subRow->getName();

                switch ($field) {
                    case 'price':
                        if (isset($row->price['from'])) {
                            $offer['price_from'] = true;
                        }

                        $value = (string)$subRow;
                        break;
                    case 'barcode':
                        if (isset($offer[$field]) || empty($row->{$field})) {
                            continue 2;
                        }

                        $value = $this->getMultiple($field, $data);
                        break;
                    case 'url':
                    case 'picture':
                        $value = $this->getUrl((string)$subRow);
                        break;
                    case 'param':
                        if (isset($offer['param']) || empty($row->param)) {
                            continue 2;
                        }

                        $value = $this->getParams($row);
                        break;
                    case 'delivery-options':
                    case 'pickup-options':
                        $value = $this->getTransportOptions($row->{$field});
                        break;
                    case 'supplier':
                        $value = isset($row->supplier['ogrn']) ? (string)$row->supplier['ogrn'] : '';
                        break;
                    case 'condition':
                        $value = $this->getConditions($subRow);
                        break;
                    case 'credit-template':
                        $value = (string)$row->{"credit-template"}['id'];
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

    private function getTransportOptions($data)
    {
        $options = [];
        foreach ($data->option as $row) {
            $option = [
                'cost' => (string)$row['cost'],
                'days' => (string)$row['days']
            ];

            if (isset($row['order-before'])) {
                $option['order-before'] = (string)$row['order-before'];
            }

            $options[] = $option;
        }
        return $options;
    }

    private function getUrl(string $url)
    {
        return rawurldecode(utf8_decode($url));
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
        $params = [];
        foreach ($data->param as $row) {
            $param = [
                'value' => (string)$row
            ];

            if (isset($row['name'])) {
                $param['name'] = (string)$row['name'];
            }

            if (isset($row['unit'])) {
                $param['unit'] = (string)$row['unit'];
            }

            $params[] = $param;
        }

        return $params;
    }

    private function getConditions($row)
    {
        return [
            'type' => (string)$row['type'],
            'reason' => (string)$row->reason,
        ];
    }
}