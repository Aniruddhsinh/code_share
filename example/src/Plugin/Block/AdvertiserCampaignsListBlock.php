<?php

namespace Drupal\example\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Advertiser Campaign List block' block.
 *
 * @Block(
 *   id = "advertiser_campaign_list_block",
 *   admin_label = @Translation("Advertiser Campaign List block"),
 *   category = @Translation("Custom"),
 * )
 */
class AdvertiserCampaignsListBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Build the form.
    $form = $this->formBuilder->getForm('Drupal\example\Form\AdvertiserCampaignListForm');

    // Specify caching settings.
    $build = [
      '#cache' => [
    // Cache for 1 hour. Adjust this value as needed.
        'max-age' => 3600,
      ],
    ];

    // Merge the form into the render array.
    $build += $form;
    return $build;
  }

}
