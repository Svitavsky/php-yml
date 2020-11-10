<?php

namespace src;

class YMLGenerator
{
    /**
     * Имя файла конфигурации
     */
    const CONFIG_FILENAME = 'config.php';

    /**
     * Список категорий
     * @var array
     */
    public $categories;

    /**
     * Список товаров
     * @var array
     */
    public $offers;

    /**
     * Статус работы
     * @var bool
     */
    public $success = true;

    /**
     * Сообщение генератора
     * @var string
     */
    public $message = '';

    public function __construct(array $categories, array $offers)
    {
        $this->categories = $categories;
        $this->offers = $offers;
    }

    /**
     * Запуск генератора
     */
    public function run()
    {
        $data = $this->getData();
        if ($this->message) {
            return;
        }

        $builder = new Builder($data);
        $text = $builder->build();
        file_put_contents($data['ymlFilename'], $text);

        $this->message = "Файл {$data['ymlFilename']} успешно сгенерирован!";
    }

    private function getData()
    {
        if (!file_exists(self::CONFIG_FILENAME)) {
            $this->message = 'Файл конфигурации config.php не найден! Создайте его, затем перезапустите генератор.';
        }

        $config = include self::CONFIG_FILENAME;
        $example = include 'config-example.php';

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

        $validator = new Validator($config['simplifiedOffers']);
        $validatedOffers = $validator->validate($this->offers);

        $data = [
            'config' => $config,
            'categories' => $this->categories,
            'offers' => $validatedOffers
        ];

        return $data;
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