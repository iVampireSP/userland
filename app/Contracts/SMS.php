<?php

namespace App\Contracts;

interface SMS
{
    public function setPhone(string $phone);

    public function setTemplateId(string $templateId);

    public function setContent(string $content);

    public function setVariableContent(array $variables = []);

    public function send();

    public function sendVariable();
}
