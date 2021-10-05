<?php

namespace Adeliom\EasyMenuBundle\Controller;

use App\Entity\EasyMenu\Menu;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

abstract class MenuCrudController extends AbstractCrudController
{
    private AdminUrlGenerator $adminUrlGenerator;

    const TRANSLATE_TITLE_PREFIX =  "easy.menu.admin.crud.title.menu.";

    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->addFormTheme('@EasyCommon/crud/custom_panel.html.twig')
            ->setPageTitle(Crud::PAGE_INDEX, self::TRANSLATE_TITLE_PREFIX . Crud::PAGE_INDEX)
            ->setPageTitle(Crud::PAGE_EDIT, self::TRANSLATE_TITLE_PREFIX . Crud::PAGE_EDIT)
            ->setPageTitle(Crud::PAGE_NEW, self::TRANSLATE_TITLE_PREFIX . Crud::PAGE_NEW)
            ->setPageTitle(Crud::PAGE_DETAIL, self::TRANSLATE_TITLE_PREFIX . Crud::PAGE_DETAIL)
            ->setEntityLabelInSingular("easy.menu.admin.crud.label.menu.singular")
            ->setEntityLabelInPlural("easy.menu.admin.crud.label.menu.plural")
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        $pages = [Crud::PAGE_INDEX, Crud::PAGE_EDIT, Crud::PAGE_NEW, Crud::PAGE_DETAIL];
        foreach ($pages as $page) {
            $pageActions = $actions->getAsDto($page)->getActions();
            foreach ($pageActions as $action) {
                $action->setLabel("easy.menu.admin.crud.label.menu." . $action->getName());
                $actions->remove($page, $action->getAsConfigObject());
                $actions->add($page, $action->getAsConfigObject());
            }
        }

        $actions->disable(Action::DETAIL);

        // Add a link to the Item Crud Controller to manage selected menu items
        $viewItems = Action::new('goToItems', 'easy.menu.admin.crud.label.menu.manage_items', 'fas fa-list')
            ->displayIf(static function (Menu $entity) {
                return $entity->getId();
            })
            ->linkToCrudAction("goToItems");

        $actions
            ->add(Crud::PAGE_INDEX, $viewItems);

        return $actions;
    }

    // Redirect to Item Crud Controller to manage selected menu items
    public function goToItems(AdminContext $context): Response
    {
        $url = $this->adminUrlGenerator
            ->unsetAll()
            ->setController($this->container->get("parameter_bag")->get("easy_menu.menu_item.crud") )
            ->setAction(Action::INDEX)
            ->set('fromMenuId', $context->getEntity()->getInstance()->getId())
            ->generateUrl();
        return $this->redirect($url);
    }

    public function configureFields(string $pageName): iterable
    {
        $context = $this->get(AdminContextProvider::class)->getContext();
        $subject = $context->getEntity();

        yield IdField::new('id')->hideOnForm();
        yield from $this->informationsFields($pageName, $subject);
        yield from $this->publishFields($pageName, $subject);
    }

    public function informationsFields(string $pageName, $subject): iterable
    {
        yield FormField::addPanel("easy.menu.admin.panel.information")->addCssClass("col-8");
        yield TextField::new('name', "easy.menu.admin.field.name")
            ->setRequired(false)
            ->setColumns(12);
        yield SlugField::new('code', "easy.menu.admin.field.code")
            ->setRequired(true)
            ->hideOnIndex()
            ->setTargetFieldName('name')
            ->setUnlockConfirmationMessage("easy.page.admin.field.slug_edit")
            ->setColumns(12);
    }

    public function publishFields(string $pageName, $subject): iterable
    {
        yield FormField::addPanel("easy.menu.admin.panel.publication")->collapsible()->addCssClass("col-4");
        yield BooleanField::new("status", "easy.menu.admin.field.state")
            ->setRequired(true)
            ->renderAsSwitch(true);
    }
}
