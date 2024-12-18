<?php

/**
 * @file
 * Custom features based on the view updates.
 */

use Drupal\views\ViewExecutable;

/**
 * Implements hook_views_pre_build().
 */
function example_views_pre_build(ViewExecutable $view) {
  $view_id = $view->id();
  $display_id = $view->getDisplay()->display['id'];
  if ($view_id === 'advertising_contract') {
    // Get Advertiser profile details.
    $currentUser = \Drupal::currentUser();
    if (!$currentUser->hasPermission('administer site configuration')) {
      $advertiser_service = \Drupal::service('example.advertiser_contract');
      $contract_id[] = $advertiser_service->getContractId();
      if ($contract_id) {
        $view->setArguments($contract_id);
      }
    }
  }
  if ($view_id === 'advertiser_campaigns') {
    $currentUser = \Drupal::currentUser();
    if (!$currentUser->hasPermission('administer site configuration')) {
      $advertiser_service = \Drupal::service('example.advertiser_contract');
      $campaign_list[] = implode(",", $advertiser_service->getCampaignIds());
      $view->setArguments($campaign_list);
    }
  }
  if ($view_id === 'advertiser_campaign_list') {
    $currentUser = \Drupal::currentUser();
    if (!$currentUser->hasPermission('administer site configuration')) {
      $advertiser_service = \Drupal::service('example.advertiser_contract');
      $campaign_list[] = implode(",", $advertiser_service->getCampaignIds());
      $view->setArguments($campaign_list);
    }
    else {
      $campaign_list[] = "all";
      $view->setArguments($campaign_list);
    }
  }
  if ($view_id === "ad_listing") {
    $currentUser = \Drupal::currentUser();
    if (!$currentUser->hasPermission('administer site configuration')) {
      $advertiser_service = \Drupal::service('example.advertiser_contract');
      $ad_list = $advertiser_service->getAdIds();
      if (!empty($ad_list)) {
        $view->setArguments([implode("+", $ad_list)]);
      }
    }
  }
  if ($view_id === "sku_listing" && $display_id === "block_1") {
    $currentUser = \Drupal::currentUser();
    if (!$currentUser->hasPermission('administer site configuration')) {
      $advertiser_service = \Drupal::service('example.advertiser_contract');
      $skus_list = $advertiser_service->getSkuIds();
      if (!empty($skus_list)) {
        $view->setArguments([implode("+", $skus_list)]);
      }
    }
  }

}
