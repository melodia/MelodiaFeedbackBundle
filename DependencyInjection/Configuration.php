<?php

/*
 * This file is part of the Melodia Feedback Bundle
 *
 * (c) Aliocha Ryzhkov <alioch@yandex.ru>
 */

namespace Melodia\FeedbackBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('melodia_feedback');

        $rootNode
            ->children()

            ->scalarNode('entity_class')
                ->info('A fully qualified name of the Feedback entity, which should be created by user.')
            ->end()

            ->scalarNode('form_class')
                ->defaultValue('Melodia\FeedbackBundle\Form\Type\FeedbackFormType')
            ->end()

            ->scalarNode('to_email')
                ->info('An address to which emails will be sent.')
            ->end()

            ->scalarNode('subject')
                ->defaultValue('Feedback')
            ->end()
        ;

        return $treeBuilder;
    }
}
