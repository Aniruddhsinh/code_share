<?php

namespace Drupal\example\TwigExtension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension providing Count of Ads based on the NID of a campaignID.
 *
 * @package Drupal\example\TwigExtension
 */
class ReferenceAdCountExtension extends AbstractExtension {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'example_ad_count';
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction('example_ad_count', [
        $this,
        'getadcount',
      ]),
    ];
  }

  /**
   * Returns the total Ads which has the associated campaignID.
   *
   * @return int
   *   value of the total Ads which has the associated campaignID
   */
  public function getadcount($campaign_id) {
    $node_storage = $this->entityTypeManager->getStorage('node');
    $query = $node_storage->getQuery()
      ->condition('type', 'ads')
      ->condition('status', 1)
      ->condition('field_campaign', $campaign_id, '=');
    $results = $query->execute();
    $ad_count = 0;
    if ($results) {
      $ad_count = count($results);
    }
    return $ad_count;
  }

}
