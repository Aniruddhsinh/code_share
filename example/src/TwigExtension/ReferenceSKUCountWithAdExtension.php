<?php

namespace Drupal\example\TwigExtension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension providing custom functionalities to get the Count of SKUs based on the NID of a AdID.
 *
 * @package Drupal\example\TwigExtension
 */
class ReferenceSKUCountWithAdExtension extends AbstractExtension {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'example_sku_count_with_ad';
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction('example_sku_count_with_ad', [
        $this,
        'getskucountwitaAd',
      ]),
    ];
  }

  /**
   * Returns the total SKUs which has the associated AdID.
   *
   * @return int
   *   value of the total SKUs which has the associated AdID
   */
  public function getskucountwitaAd($ad_id) {
    $node_storage = $this->entityTypeManager->getStorage('node');
    $query = $node_storage->getQuery()
      ->condition('type', 'ads')
      ->condition('status', 1)
      ->condition('nid', $ad_id, '=');
    $results = $query->execute();
    $skus = 0;
    if ($results) {
      foreach ($results as $key => $value) {
        $ad_node = $this->entityTypeManager->getStorage('node')->load($value);
        $skus = $skus + count($ad_node->get('field_related_products')->referencedEntities());
      }
    }
    return $skus;
  }

}
