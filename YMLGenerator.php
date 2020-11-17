<?php

namespace src;

class YMLGenerator
{
    /**
     * Список данных
     * @var array
     */
    public $data;

    /**
     * Сообщение генератора
     * @var string
     */
    public $message = '';

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Запуск генератора
     */
    public function run()
    {
        $config = $this->getConfig();
        $data = $this->getData($config);
        $data['config'] = $config;
        if ($this->message) {
            return;
        }

        $builder = new Builder($data);
        $text = $builder->build();
        $outputFile = strlen($config['ymlFilename']) ? $config['ymlFilename'] : 'shop.yml';
        file_put_contents($outputFile, $text);

        $this->message = "Файл {$config['ymlFilename']} успешно сгенерирован!";
    }

    private function getData(array $config)
    {
        $validator = new Validator($config);
        return $validator->validateAll($this->data);
    }

    private function getConfig()
    {
        if (!file_exists('config.php')) {
            $this->message = 'Файл конфигурации config.php не найден! Создайте его, затем перезапустите генератор.';
        }

        $config = include 'config.php';
        $example = include 'config.example.php';

        $missingKeys = [];
        foreach ($example as $key => $value) {
            if (!array_key_exists($key, $config)) {
                $missingKeys[] = $key;
            }
        }

        if (count($missingKeys)) {
            $keys = implode(' ', $missingKeys);
            $this->message = "Отсутствуют обязательные параметры в файле конфигурации config.php: {$keys}";
        }

        $config['availableCountries'] = $this->getAvailableCountries();
        $config['date'] = date("Y-m-d H:i", time());
        return $config;
    }

    /**
     * Получение списка доступных стран
     * @return array
     */
    private function getAvailableCountries()
    {
        $countriesList = file_get_contents('countries.csv');
        return explode(',', $countriesList);
    }
}