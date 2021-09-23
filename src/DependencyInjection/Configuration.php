<?php

namespace Adeliom\EasyMenuBundle\DependencyInjection;

use Adeliom\EasyMenuBundle\Controller\MenuCrudController;
use Adeliom\EasyMenuBundle\Controller\MenuItemCrudController;
use Adeliom\EasyMenuBundle\Entity\MenuEntity;
use Adeliom\EasyMenuBundle\Entity\MenuItemEntity;
use Adeliom\EasyMenuBundle\Repository\MenuItemRepository;
use Adeliom\EasyMenuBundle\Repository\MenuRepository;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;


/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('easy_menu');
        $rootNode    = $treeBuilder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('menu')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')
                            ->isRequired()
                            ->validate()
                                ->ifString()
                                ->then(function($value) {
                                    if (!class_exists($value) || !is_a($value, MenuEntity::class, true)) {
                                        throw new InvalidConfigurationException(sprintf(
                                            'Entry class must be a valid class extending %s. "%s" given.',
                                            MenuEntity::class, $value
                                        ));
                                    }
                                    return $value;
                                })
                            ->end()
                        ->end()
                        ->scalarNode('repository')
                            ->defaultValue(MenuRepository::class)
                            ->validate()
                                ->ifString()
                                ->then(function($value) {
                                    if (!class_exists($value) || !is_a($value, MenuRepository::class, true)) {
                                        throw new InvalidConfigurationException(sprintf(
                                            'Entry repository must be a valid class extending %s. "%s" given.',
                                            MenuRepository::class, $value
                                        ));
                                    }
                                    return $value;
                                })
                            ->end()
                        ->end()
                        ->scalarNode('crud')
                            ->defaultValue(MenuCrudController::class)
                            ->validate()
                                ->ifString()
                                ->then(function($value) {
                                    if (!class_exists($value) || !is_a($value, MenuCrudController::class, true)) {
                                        throw new InvalidConfigurationException(sprintf(
                                            'Entry crud controller must be a valid class extending %s. "%s" given.',
                                            MenuCrudController::class, $value
                                        ));
                                    }
                                    return $value;
                                })
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('menu_item')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')
                            ->isRequired()
                            ->validate()
                                ->ifString()
                                ->then(function($value) {
                                    if (!class_exists($value) || !is_a($value, MenuItemEntity::class, true)) {
                                        throw new InvalidConfigurationException(sprintf(
                                            'Category class must be a valid class extending %s. "%s" given.',
                                            MenuItemEntity::class, $value
                                        ));
                                    }
                                    return $value;
                                })
                            ->end()
                        ->end()
                        ->scalarNode('repository')
                            ->defaultValue(MenuItemRepository::class)
                            ->validate()
                                ->ifString()
                                ->then(function($value) {
                                    if (!class_exists($value) || !is_a($value, MenuItemRepository::class, true)) {
                                        throw new InvalidConfigurationException(sprintf(
                                            'Category repository must be a valid class extending %s. "%s" given.',
                                            MenuItemRepository::class, $value
                                        ));
                                    }
                                    return $value;
                                })
                            ->end()
                        ->end()
                        ->scalarNode('crud')
                            ->defaultValue(MenuItemCrudController::class)
                            ->validate()
                                ->ifString()
                                ->then(function($value) {
                                    if (!class_exists($value) || !is_a($value, MenuItemCrudController::class, true)) {
                                        throw new InvalidConfigurationException(sprintf(
                                            'Category crud controller must be a valid class extending %s. "%s" given.',
                                            MenuItemCrudController::class, $value
                                        ));
                                    }
                                    return $value;
                                })
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->integerNode('ttl')->defaultValue(300)->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
