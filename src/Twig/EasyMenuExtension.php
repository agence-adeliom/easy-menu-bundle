<?php

namespace Adeliom\EasyMenuBundle\Twig;

use Adeliom\EasyMenuBundle\Exceptions\MenuNotFoundException;
use Adeliom\EasyMenuBundle\Exceptions\TemplateNotFoundException;
use App\Entity\EasyMenu\MenuItem;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;

class EasyMenuExtension extends AbstractExtension
{
    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var ManagerRegistry
     */
    private $em;

    /**
     * @var string
     */
    private $menuClass;

    /**
     * @var string
     */
    private $menuItemClass;

    public function __construct(Environment $twig, EntityManagerInterface $em, string $menuClass, string $menuItemClass)
    {
        $this->twig = $twig;
        $this->em = $em;
        $this->menuClass = $menuClass;
        $this->menuItemClass = $menuItemClass;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('easy_menu', [$this, 'renderEasyMenu'], ['is_safe' => ['js', 'html'], 'needs_context' => true, 'needs_environment' => true]),
        ];
    }

    /**
     * @param Environment $env
     * @param array $context
     * @param $code
     * @param array $extra
     * @return Markup
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function renderEasyMenu(Environment $env, array $context, $code, $extra = []): Markup
    {
        $menu = $this->em->getRepository($this->menuClass)->findOneByCode($code);

        if (empty($menu)) {
            throw new MenuNotFoundException($code);
        }

        $template = "@EasyMenu/front/menus/" . $code . ".html.twig";

        if ( !empty($extra['template']) ) {
            $template = $extra['template'];
        }

        if (!$this->twig->getLoader()->exists($template)) {
            throw new TemplateNotFoundException($template);
        }

        $rootItem = $this->em->getRepository($this->menuItemClass)->findOneBy([
            'menu' => $menu,
            'parent' => null
        ]);

        $menu->setRootItem($rootItem);

        return new Markup($this->twig->render($template, array_merge($context, [
            "menu" => $menu
        ], $extra)), 'UTF-8');
    }

}
