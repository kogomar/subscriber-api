<?php

namespace App\Http;

class View
{
    private string $templatePath;

    public function __construct(string $templatePath = __DIR__ . '/../../templates')
    {
        $this->templatePath = $templatePath;
    }

    public function render(string $template, array $data = [], ?string $layout = 'layout'): string
    {
        extract($data);

        ob_start();
        include "{$this->templatePath}/{$template}.php";
        $content = ob_get_clean();

        if ($layout) {
            ob_start();
            include "{$this->templatePath}/{$layout}.php";
            return ob_get_clean();
        }

        return $content;
    }
}
