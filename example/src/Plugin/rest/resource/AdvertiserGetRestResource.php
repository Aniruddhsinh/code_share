<?php

namespace Drupal\example\Plugin\rest\resource;

use Drupal\node\Entity\Node;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a resource to get advertiser nodes by entity and bundle.
 *
 * @RestResource(
 *   id = "example_get_rest_resource",
 *   label = @Translation("Example Get Rest Resource"),
 *   uri_paths = {
 *     "canonical" = "/example-export"
 *   }
 * )
 */
class AdvertiserGetRestResource extends ResourceBase {
  /**
   * A current user instance which is logged in the session.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $loggedUser;

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $config
   *   A configuration array which contains the information about the plugin instance.
   * @param string $module_id
   *   The module_id for the plugin instance.
   * @param mixed $module_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A currently logged user instance.
   */
  public function __construct(
    array $config,
    $module_id,
    $module_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user,
  ) {
    parent::__construct($config, $module_id, $module_definition, $serializer_formats, $logger);

    $this->loggedUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $config, $module_id, $module_definition) {
    return new static(
      $config,
      $module_id,
      $module_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('sample_rest_resource'),
      $container->get('current_user')
    );
  }

  /**
   * Returns a list of nodes.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get() {
    // Implementing our custom REST Resource here.
    // Use currently logged user after passing authentication and validating the access of term list.
    if (!$this->loggedUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    $content_type = 'campaign';
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $query = $node_storage->getQuery()
      ->condition('type', 'campaign')
      ->condition('status', 1);
    $results = $query->execute();
    if ($results) {
      $campaign_nodes = Node::loadMultiple($results);
      foreach ($campaign_nodes as $campaign_key => $campaign_value) {
        $ad_nodes = $campaign_value->get('field_campaign_ads')->referencedEntities();
        $advertiser_name = "";
        if ($campaign_advertiser_field = $campaign_value->get('field_campaign_advertiser')) {
          if (!$campaign_advertiser_field->isEmpty()) {
            $advertiser_name = $campaign_advertiser_field->referencedEntities()[0]->label();
            $advertiser_id = $campaign_advertiser_field->referencedEntities()[0]->id();
          }
        }
        $contract_name = "";
        $contract_id = "";
        if ($campaign_contract_field = $campaign_value->get('field_contract')) {
          if (!$campaign_contract_field->isEmpty()) {
            $contract_name = $campaign_contract_field->referencedEntities()[0]->label();
            $contract_id = $campaign_contract_field->referencedEntities()[0]->id();
          }
        }
        $campaign_status = "";
        if ($campaign_status_field = $campaign_value->get('field_campaign_status')) {
          if (!$campaign_status_field->isEmpty()) {
            $campaign_status = $campaign_status_field->referencedEntities()[0]->label();
          }
        }
        $node_result[$campaign_key] = [
          'Campaign Id' => $campaign_value->id(),
          'Campaign Name' => $campaign_value->field_campaign_title->value,
              // 'ad_play' => $campaign_value->field_ad_play->value,
          'Ad Slots (by Campaign)' => $campaign_value->field_ad_slots->value,
          'Company' => $contract_name,
          'Company Id' => $contract_id,
          'Advertiser Name' => $advertiser_name,
          'Advertiser Id' => $advertiser_id,
          'Start date' => $campaign_value->field_go_live_date->value,
          'End date' => $campaign_value->field_target_end_date->value,
          'Campaign Status' => $campaign_status,
        ];
        if ($ad_nodes) {
          foreach ($ad_nodes as $ad_key => $ad_value) {
            $ad_status = "";
            if ($ad_status_field = $ad_value->get('field_ad_status')) {
              if (!$ad_status_field->isEmpty()) {
                $ad_status = $ad_status_field->referencedEntities()[0]->label();
              }
            }
            $node_result[$campaign_key]['ad'][$ad_key] = [
              'Ad Id' => $ad_value->id(),
              'Ad Name' => $ad_value->field_ad_title->value,
              'Ad Status' => $ad_status,
              'Horizontal Video URI' => $ad_value->field_ad_title->value,
              'Vertical Video URI' => $ad_value->field_ad_title->value,
              'Video Duration' => "",
              'Hero Image URI' => "",
              'Description Override' => "",
              'Region' => "ALL",
            ];
            $sku_nodes = $ad_value->get('field_related_products')->referencedEntities();
            if ($sku_nodes) {
              foreach ($sku_nodes as $sku_key => $sku_value) {
                $node_result[$campaign_key]['ad'][$ad_key]['sku'][$sku_key] = [
                  'Product Id' => $sku_value->id(),
                  'Product Description' => $sku_value->field_product_name->value,
                  'UPC' => $sku_value->field_product_name->value,
                  'AGLC SKU' => $sku_value->field_sku_ab->value,
                  'BC SKU' => $sku_value->field_sku_bc->value,
                  'ON SKU' => $sku_value->field_sku_on->value,
                  'Alt SKU' => $sku_value->field_sku_alt->value,
                  'Notes' => "",
                  'Format' => $sku_value->field_format->value,
                  'Brand' => $sku_value->field_brand->value,
                  'Live Date' => $sku_value->field_sku_start_date->value,
                  'End Date' => $sku_value->field_sku_end_date->value,
                ];
              }
            }
          }
        }
      }
    }
    $response = new ResourceResponse($node_result);
    $response->addCacheableDependency($node_result);
    return $response;
  }

}
