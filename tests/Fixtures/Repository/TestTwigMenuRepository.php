<?php

declare(strict_types=1);

namespace Adeliom\EasyMenuBundle\Tests\Fixtures\Repository;

use Adeliom\EasyMenuBundle\Repository\MenuRepository;

class TestTwigMenuRepository extends MenuRepository
{
    public function findOneByCode(string $code): ?object
    {
        return null;
    }
}
