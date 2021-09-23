<?php

namespace Adeliom\EasyMenuBundle\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Twig\Environment;
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

    public function __construct(Environment $twig, EntityManagerInterface $em, string $menuClass)
    {
        $this->twig = $twig;
        $this->em = $em;
        $this->menuClass = $menuClass;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('render_easy_menu', [$this, 'renderEasyMenu'], ['is_safe' => ['js', 'html'], 'needs_context' => true, 'needs_environment' => true]),
        ];
    }

    /**
     * @param array $datas
     */
    public function renderEasyMenu(Environment $env, array $context, $code, $extra = [])
    {
        $menu = $this->em->getRepository($this->menuClass)->findOneByCode($code);

        if (empty($menu)) {
            throw new \Exception('Menu ' . $code . ' not found. Please add it !');
        }

        if (!$this->twig->getLoader()->exists("@EasyMenu/front/menus/" . $code . ".html.twig")) {
            throw new \Exception('Template not found ' . "@EasyMenu/front/menus/" . $code . ".html.twig");
        }

        return new Markup($this->twig->render("@EasyMenu/front/menus/" . $code . ".html.twig", array_merge($context, [
            "menu" => $menu
        ], $extra)), 'UTF-8');
    }

}
