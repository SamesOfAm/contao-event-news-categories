<?php

declare(strict_types=1);

namespace KlapprothKoch\ContaoEventNewsCategories;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ContaoEventNewsCategoriesBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
