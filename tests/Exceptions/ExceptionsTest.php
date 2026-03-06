<?php

declare(strict_types=1);

namespace Adeliom\EasyMenuBundle\Tests\Exceptions;

use Adeliom\EasyMenuBundle\Exceptions\MenuNotFoundException;
use Adeliom\EasyMenuBundle\Exceptions\TemplateNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyMenuBundle\Exceptions\MenuNotFoundException::class)]
#[CoversClass(\Adeliom\EasyMenuBundle\Exceptions\TemplateNotFoundException::class)]
final class ExceptionsTest extends TestCase
{
    public function testMenuNotFoundExceptionFormatsMessage(): void
    {
        $exception = new MenuNotFoundException('footer');

        self::assertSame('Could not find menu with code "footer".', $exception->getMessage());
    }

    public function testTemplateNotFoundExceptionFormatsMessage(): void
    {
        $exception = new TemplateNotFoundException('@EasyMenu/front/menus/footer.html.twig');

        self::assertSame('Could not find template "@EasyMenu/front/menus/footer.html.twig".', $exception->getMessage());
    }
}
