<?php

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
        $config = $this->getConfig();
        if ($this->message) {
            return;
        }

        $builder = new Builder($config);
        $text = $builder->build();
        file_put_contents($config['ymlFilename'], $text);

        $this->message = "Файл {$config['ymlFilename']} успешно сгенерирован!";
    }

    private function getConfig()
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
            $this->message = "Отсутствуют параметры в файле конфигурации config.php: {$keys}";
        }

        $config['date'] = date("Y-m-d H:i", time());

        $config['categories'] = $this->categories;
        $config['offers'] = $this->offers;
        return $config;
    }
}