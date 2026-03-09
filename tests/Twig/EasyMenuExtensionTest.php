<?php

declare(strict_types=1);

namespace Adeliom\EasyMenuBundle\Tests\Twig;

use Adeliom\EasyMenuBundle\Exceptions\MenuNotFoundException;
use Adeliom\EasyMenuBundle\Exceptions\TemplateNotFoundException;
use Adeliom\EasyMenuBundle\Tests\Fixtures\Entity\TestMenu;
use Adeliom\EasyMenuBundle\Tests\Fixtures\Entity\TestMenuItem;
use Adeliom\EasyMenuBundle\Tests\Fixtures\Repository\TestMenuItemRepository;
use Adeliom\EasyMenuBundle\Tests\Fixtures\Repository\TestTwigMenuRepository;
use Adeliom\EasyMenuBundle\Twig\EasyMenuExtension;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\LoaderInterface;
use Twig\Markup;
use Twig\TwigFunction;

#[CoversClass(\Adeliom\EasyMenuBundle\Twig\EasyMenuExtension::class)]
final class EasyMenuExtensionTest extends TestCase
{
    public function testExtensionRegistersExpectedTwigFunction(): void
    {
        $extension = new EasyMenuExtension(
            $this->createMock(Environment::class),
            $this->createMock(EntityManagerInterface::class),
            'menu.class',
            'menu_item.class'
        );

        $functions = $extension->getFunctions();

        self::assertContainsOnlyInstancesOf(TwigFunction::class, $functions);
        self::assertSame(['easy_menu'], array_map(static fn (TwigFunction $function): string => $function->getName(), $functions));
    }

    public function testRenderEasyMenuReturnsRenderedMarkupAndSetsRootItem(): void
    {
        $menu = new TestMenu();
        $menu->setName('Header');
        $rootItem = new TestMenuItem();
        $rootItem->setName('Root');
        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects(self::once())
            ->method('exists')
            ->with('@EasyMenu/front/menus/custom.html.twig')
            ->willReturn(true);

        $twig = $this->createMock(Environment::class);
        $twig->expects(self::once())->method('getLoader')->willReturn($loader);
        $twig->expects(self::once())
            ->method('render')
            ->with('@EasyMenu/front/menus/custom.html.twig', self::callback(static function (array $parameters) use ($menu): bool {
                return 'bar' === $parameters['foo']
                    && 'baz' === $parameters['slot']
                    && $menu === $parameters['menu'];
            }))
            ->willReturn('<nav>header</nav>');

        $menuRepository = $this->getMockBuilder(TestTwigMenuRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findOneByCode'])
            ->getMock();
        $menuRepository->expects(self::once())
            ->method('findOneByCode')
            ->with('header')
            ->willReturn($menu);

        $menuItemRepository = $this->getMockBuilder(TestMenuItemRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findOneBy'])
            ->getMock();
        $menuItemRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['menu' => $menu, 'parent' => null])
            ->willReturn($rootItem);

        $em = $this->createMock(EntityManagerInterface::class);
        $requestedRepositories = [];
        $em->expects(self::exactly(2))
            ->method('getRepository')
            ->willReturnCallback(static function (string $className) use (&$requestedRepositories, $menuRepository, $menuItemRepository) {
                $requestedRepositories[] = $className;

                return match ($className) {
                    TestMenu::class => $menuRepository,
                    TestMenuItem::class => $menuItemRepository,
                    default => throw new \LogicException(sprintf('Unexpected repository "%s".', $className)),
                };
            });

        $extension = new EasyMenuExtension($twig, $em, TestMenu::class, TestMenuItem::class);

        $markup = $extension->renderEasyMenu($twig, ['foo' => 'bar'], 'header', [
            'template' => '@EasyMenu/front/menus/custom.html.twig',
            'slot' => 'baz',
        ]);

        self::assertInstanceOf(Markup::class, $markup);
        self::assertSame('<nav>header</nav>', (string) $markup);
        self::assertSame($rootItem, $menu->getRootItem());
        self::assertSame([TestMenu::class, TestMenuItem::class], $requestedRepositories);
    }

    public function testRenderEasyMenuThrowsWhenMenuCannotBeFound(): void
    {
        $twig = $this->createMock(Environment::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $menuRepository = $this->getMockBuilder(TestTwigMenuRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findOneByCode'])
            ->getMock();
        $menuRepository->expects(self::once())
            ->method('findOneByCode')
            ->with('missing')
            ->willReturn(null);

        $em->expects(self::once())
            ->method('getRepository')
            ->with(TestMenu::class)
            ->willReturnCallback(static fn () => $menuRepository);

        $extension = new EasyMenuExtension($twig, $em, TestMenu::class, TestMenuItem::class);

        $this->expectException(MenuNotFoundException::class);
        $this->expectExceptionMessage('Could not find menu with code "missing".');

        $extension->renderEasyMenu($twig, [], 'missing');
    }

    public function testRenderEasyMenuThrowsWhenTemplateCannotBeFound(): void
    {
        $menu = new TestMenu();
        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects(self::once())
            ->method('exists')
            ->with('@EasyMenu/front/menus/header.html.twig')
            ->willReturn(false);

        $twig = $this->createMock(Environment::class);
        $twig->expects(self::once())->method('getLoader')->willReturn($loader);
        $twig->expects(self::never())->method('render');

        $em = $this->createMock(EntityManagerInterface::class);
        $menuRepository = $this->getMockBuilder(TestTwigMenuRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findOneByCode'])
            ->getMock();
        $menuRepository->expects(self::once())
            ->method('findOneByCode')
            ->with('header')
            ->willReturn($menu);

        $em->expects(self::once())
            ->method('getRepository')
            ->with(TestMenu::class)
            ->willReturnCallback(static fn () => $menuRepository);

        $extension = new EasyMenuExtension($twig, $em, TestMenu::class, TestMenuItem::class);

        $this->expectException(TemplateNotFoundException::class);
        $this->expectExceptionMessage('Could not find template "@EasyMenu/front/menus/header.html.twig".');

        $extension->renderEasyMenu($twig, [], 'header');
    }
}
