<?php

namespace src;

class YMLGenerator
{
    /**
     * Название итогового файла
     * @var string
     */
    public $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * Запуск генератора
     * @param array $data
     */
    public function run(array $data)
    {
        $data['availableCountries'] = $this->getAvailableCountries();
        $validator = new Validator($data);
        $result = $validator->validateAll();

        $builder = new Builder($result);
        $content = $builder->build();
        file_put_contents($this->filename, $content);

        echo "Файл {$this->filename} успешно сгенерирован!";
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