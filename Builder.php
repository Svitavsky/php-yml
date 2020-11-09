<?php

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
    public function build(): string
    {
        $availableCountries = $this->getAvailableCountries();
        extract($this->data);

        ob_start();
        include 'template.php';
        $template = ob_get_clean();
        return $template;
    }

    /**
     * Список доступных стран для Яндекс.Маркет
     * @return array
     */
    private function getAvailableCountries()
    {
        $countriesList = file_get_contents('countries.csv');
        return explode(',', $countriesList);
    }
}