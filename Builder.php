<?php

class Builder
{
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
        extract($this->data);

        ob_start();
        include 'template.php';
        $template = ob_get_clean();
        return $template;
    }
}