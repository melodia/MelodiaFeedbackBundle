<?php

/*
 * This file is part of the Melodia Feedback Bundle
 *
 * (c) Aliocha Ryzhkov <alioch@yandex.ru>
 */

namespace Melodia\FeedbackBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class MelodiaFeedbackExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        if (!isset($config['entity_class'])) {
            throw new \InvalidArgumentException('The "entity_class" option must be set in melodia_feedback configuration');
        }
        $container->setParameter('melodia_feedback.entity.class', $config['entity_class']);

        $container->setParameter('melodia_feedback.form.class', $config['form_class']);

        if (!isset($config['to_email'])) {
            throw new \InvalidArgumentException('The "to_email" option must be set in melodia_feedback configuration');
        }
        $container->setParameter('melodia_feedback.to_email', $config['to_email']);

        $container->setParameter('melodia_feedback.subject', $config['subject']);
    }
}
