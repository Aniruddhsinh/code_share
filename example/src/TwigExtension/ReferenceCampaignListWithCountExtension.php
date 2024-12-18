<?php

namespace Drupal\example\TwigExtension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension providing Count of Ads based on the NID of a campaignID.
 *
 * @package Drupal\example\TwigExtension
 */
class ReferenceCampaignListWithCountExtension extends AbstractExtension {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'example_campaign_name_count';
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction('example_campaign_name_count', [
        $this,
        'getcamapignameandcount',
      ]),
    ];
  }

  /**
   * Returns the total Campaigns with their count which has the associated AdID.
   *
   * @return string
   *   value of the total Campaigns with their count which has the associated AdID
   */
  public function getcamapignameandcount($ad_id) {
    dump($ad_id);
    exit;
    $advertiser_service = \Drupal::service('example.advertiser_contract');
    $campaign_data = $advertiser_service->getcampaignidswithads($ad_id);
    return $campaign_data;
  }

}
