<?php

namespace Adeliom\EasyMenuBundle\Controller;

use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use Adeliom\EasyFieldsBundle\Admin\Field\EnumField;
use Adeliom\EasyFieldsBundle\Admin\Field\PositionSortableField;
use Adeliom\EasyFieldsBundle\Traits\Admin\PositionSortableActionTrait;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

abstract class MenuItemCrudController extends AbstractCrudController
{
    use PositionSortableActionTrait;

    /**
     * @var string
     */
    public const TRANSLATE_TITLE_PREFIX = 'easy.menu.admin.crud.title.menu_item.';

    public function __construct(private \Doctrine\Persistence\ManagerRegistry $managerRegistry)
    {
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->addFormTheme('@EasyFields/form/association_widget.html.twig')

            ->setPageTitle(Crud::PAGE_INDEX, self::TRANSLATE_TITLE_PREFIX.Crud::PAGE_INDEX)
            ->setPageTitle(Crud::PAGE_EDIT, self::TRANSLATE_TITLE_PREFIX.Crud::PAGE_EDIT)
            ->setPageTitle(Crud::PAGE_NEW, self::TRANSLATE_TITLE_PREFIX.Crud::PAGE_NEW)
            ->setPageTitle(Crud::PAGE_DETAIL, self::TRANSLATE_TITLE_PREFIX.Crud::PAGE_DETAIL)
            ->setEntityLabelInSingular('easy.menu.admin.crud.label.menu_item.singular')
            ->setEntityLabelInPlural('easy.menu.admin.crud.label.menu_item.plural')

//            ->overrideTemplate('crud/index', '@EasyFields/crud/tree.html.twig')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        $pages = [Crud::PAGE_INDEX, Crud::PAGE_EDIT, Crud::PAGE_NEW, Crud::PAGE_DETAIL];
        foreach ($pages as $page) {
            $pageActions = $actions->getAsDto($page)->getActions();
            foreach ($pageActions as $action) {
                $action->setLabel('easy.menu.admin.crud.label.menu_item.'.$action->getName());
                $actions->remove($page, $action->getAsConfigObject());
                $actions->add($page, $action->getAsConfigObject());
            }
        }

        $actions->disable(Action::DETAIL);

        $actions->update(Crud::PAGE_INDEX, Action::EDIT, static function (Action $action) {
            $action->displayIf(static fn ($entity) => !empty($entity->getParent()));

            return $action;
        });
        $actions->update(Crud::PAGE_INDEX, Action::DELETE, static function (Action $action) {
            $action->displayIf(static fn ($entity) => !empty($entity->getParent()));

            return $action;
        });

        $url = $this->container->get(AdminUrlGenerator::class)
            ->unsetAll()
            ->setController($this->container->get('parameter_bag')->get('easy_menu.menu.crud'))
            ->setAction(Action::INDEX)
            ->generateUrl();
        $goBack = Action::new('goBack', 'easy.menu.admin.crud.label.menu_item.go_back')
            ->linkToUrl($url)
            ->addCssClass('btn btn-secondary')
            ->createAsGlobalAction();

        $actions
            ->add(Crud::PAGE_INDEX, $goBack);

        return $actions;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        if (!empty($this->container->get('request_stack')->getCurrentRequest()->query->get('fromMenuId'))) {
            $menu = $this->managerRegistry->getRepository($this->container->get('parameter_bag')->get('easy_menu.menu.class'))->find($this->container->get('request_stack')->getCurrentRequest()->query->get('fromMenuId'));
            $queryBuilder->andWhere('entity.menu = :menu');
            $queryBuilder->setParameter('menu', $menu);
        }

        $queryBuilder
            ->orderBy('entity.menu', \Doctrine\Common\Collections\Criteria::ASC)
            ->addOrderBy('entity.lft', \Doctrine\Common\Collections\Criteria::ASC)
        ;

        return $queryBuilder;
    }

    public function createEntity(string $entityFqcn)
    {
        parse_str(parse_url((string) $this->container->get('request_stack')->getCurrentRequest()->query->get('referrer'))['query'], $params);
        $entity = new $entityFqcn();
        if (!empty($params['fromMenuId'])) {
            $menu = $this->managerRegistry->getRepository($this->container->get('parameter_bag')->get('easy_menu.menu.class'))->find($params['fromMenuId']);
            $entity->setMenu($menu);
        }

        return $entity;
    }

    public function configureFields(string $pageName): iterable
    {
        $context = $this->container->get(AdminContextProvider::class)->getContext();
        $subject = $context?->getEntity();

        yield IdField::new('id')->hideOnForm();
        yield from $this->informationsFields($pageName, $subject);
    }

    /**
     * @return FieldInterface[]
     */
    public function informationsFields(string $pageName, object $subject): iterable
    {
        yield FormField::addPanel('easy.menu.admin.panel.information')->addCssClass('col-12');

        yield TextField::new('flattenParents', 'easy.menu.admin.field.flattenParents')
            ->onlyOnIndex();

        yield TextField::new('name', 'easy.menu.admin.field.title')
            ->hideOnIndex()
            ->setRequired(true)
            ->setColumns(12);

        yield AssociationField::new('parent', 'easy.menu.admin.field.parent')
            ->setQueryBuilder(static function (QueryBuilder $queryBuilder) use ($subject) {
                $rootAllias = $queryBuilder->getAllAliases()[0];
                if ($subject->getPrimaryKeyValue()) {
                    $queryBuilder->andWhere(sprintf('%s.id != :currentID', $rootAllias))
                        ->setParameter('currentID', $subject->getPrimaryKeyValue());
                }

                $queryBuilder->andWhere(sprintf('%s.menu = :menu', $rootAllias))
                    ->setParameter('menu', $subject->getInstance()->getMenu());

                return $queryBuilder;
            })
            ->setColumns(12)
            ->setRequired(true);

        yield UrlField::new('url', 'easy.menu.admin.field.url')
            ->setRequired(true)
            ->setColumns(12);

        yield TextField::new('classAttribute', 'easy.menu.admin.field.classAttribute')
            ->hideOnIndex()
            ->setRequired(false)
            ->setColumns(12);

        yield PositionSortableField::new('position', 'easy.menu.admin.field.position')
            ->setRequired(true)
            ->setColumns(6)
            ->onlyOnIndex();

        yield EnumField::new('state', 'easy.page.admin.field.state')
            ->setEnum(ThreeStateStatusEnum::class)
            ->setRequired(true)
            ->renderExpanded(true)
            ->renderAsBadges(true)
            ->setColumns(6);
    }
}
