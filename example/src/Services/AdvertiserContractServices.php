<?php

namespace Drupal\example\Services;

use Drupal\node\Entity\Node;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * AdvertiserContractServices service.
 */
class AdvertiserContractServices {
  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * AccountProxyInterface.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * Constructs a new AdvertiserCampaignListForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The account interface user service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountProxyInterface $account) {
    $this->entityTypeManager = $entity_type_manager;
    $this->account = $account;
  }

  /**
   * Returns the advertiser_id based on the current logged in user.
   *
   * @return interger
   *   value of the campagin_id which has the contract_id.
   */
  public function getAdvertiserId() {
    try {
      $advertiser_id = [];
      $advertiserUserProfile = $this->entityTypeManager->getStorage('profile')
        ->loadByProperties([
          'uid' => $this->account->id(),
          'type' => 'advertiser_profile',
        ]);

      // Check profile and pass the advertiser content type content id referenced from advertiser_profile.
      if ($advertiserUserProfile) {
        $advertiserUserProfile = reset($advertiserUserProfile);
        if ($advertiserUserProfile->field_advertiser) {
          $referenceAdvertisers = $advertiserUserProfile->field_advertiser->referencedEntities();
          if ($referenceAdvertisers) {
            foreach ($referenceAdvertisers as $referenceAdvertiser) {
              $advertiserNid[] = $referenceAdvertiser->id();
            }
            $advertiser_id = reset($advertiserNid);
          }
        }
      }
      return ($advertiser_id);
    }
    catch (RequestException $e) {
      \Drupal::logger('example')->notice($e);
    }
  }

  /**
   * Returns the advertiser_ids based on the current logged in user.
   *
   * @return interger
   *   value of the campagin_id which has the contract_id.
   */
  public function getMultipleAdvertiserId() {
    try {
      $advertiser_id = [];
      $advertiserUserProfile = $this->entityTypeManager->getStorage('profile')
        ->loadByProperties([
          'uid' => $this->account->id(),
          'type' => 'advertiser_profile',
        ]);

      // Check profile and pass the advertiser content type content id referenced from advertiser_profile.
      if ($advertiserUserProfile) {
        $advertiserUserProfile = reset($advertiserUserProfile);
        if ($advertiserUserProfile->field_advertiser) {
          $referenceAdvertisers = $advertiserUserProfile->field_advertiser->referencedEntities();
          if ($referenceAdvertisers) {
            foreach ($referenceAdvertisers as $referenceAdvertiser) {
              $advertiserNid[] = $referenceAdvertiser->id();
            }
            $advertiser_id = $advertiserNid;
          }
        }
      }
      return ($advertiser_id);
    }
    catch (RequestException $e) {
      \Drupal::logger('example')->notice($e);
    }
  }

  /**
   * Returns the contract_id based on the current loggedin user_id.
   *
   * @return integrer
   *   value of the contract_id which has the current loggedin user_id.
   */
  public function getContractId() {
    try {
      $contract_id = NULL;
      $advertiser_id = $this->getAdvertiserId();
      // Get the contract_id based on the advertiser_id.
      if ($advertiser_id) {
        $node_storage = $this->entityTypeManager->getStorage('node');
        $query = $node_storage->getQuery()
          ->condition('type', 'ad_contract')
          ->condition('status', 1)
          ->condition('field_advertiser', $advertiser_id, '=');
        $results = $query->execute();
        if ($results) {
          $contract_id = reset($results);
        }
      }
      return $contract_id;
    }
    catch (RequestException $e) {
      \Drupal::logger('example')->notice($e);
    }
  }

  /**
   * Returns the ad_slots based on the contract_id.
   *
   * @return float
   *   value of the ad_slots which has the provided contract_id.
   */
  public function getContactedAdSlots($contract_id) {
    try {
      $ad_slots = 0.00;
      // Get the ad_slots based on the contract_id.
      if ($contract_id) {
        $ad_node = \Drupal::entityTypeManager()
          ->getStorage('node')
          ->load($contract_id);
        $ad_slots = $ad_node->get('field_ad_spots')->value;
      }
      return $ad_slots;
    }
    catch (RequestException $e) {
      \Drupal::logger('example')->notice($e);
    }
  }

  /**
   * Returns the campagin_id based on the contract_id.
   *
   * @return array
   *   value of the campagin_id which has the contract_id.
   */
  public function getCampaignIds($contract_id = NULL) {
    try {
      if (empty($contract_id)) {
        $contract_id = $this->getContractId();
      }
      $campaign_ids = [];
      $node_storage = $this->entityTypeManager->getStorage('node');
      $query = $node_storage->getQuery()
        ->condition('type', 'campaign')
        ->condition('status', 1)
        ->condition('field_contract', $contract_id, '=')
        ->sort('title', 'ASC');
      $campaign_ids = $query->execute();
      return ($campaign_ids);
    }
    catch (RequestException $e) {
      \Drupal::logger('example')->notice($e);
    }
  }

  /**
   * Returns the campagin_id based on the ad_id.
   *
   * @return array
   *   value of the campagin_id which has the ad_id.
   */
  public function getCampaignIdsWithAds($ad_id) {
    try {
      $campaign_ids = [];
      $ad_node = $this->entityTypeManager->getStorage('node')->load($ad_id);
      $campaign_nids = $ad_node->get('field_campaign')->referencedEntities();
      $campaign_titles = [];
      if (!empty($campaign_nids)) {
        $campaign_nodes = Node::loadMultiple($campaign_nids);
        $campaign_count = count($campaign_nids);
        $campaign_titles['counter'] = $campaign_count;
        foreach ($campaign_nodes as $campaign_key => $campaign_value) {
          $campaign_titles['titles'][] = $campaign_value->field_campaign_title->value;
        }
      }
      return $campaign_titles;
    }
    catch (RequestException $e) {
      \Drupal::logger('example')->notice($e);
    }
  }

  /**
   * Returns the total Ads which has the associated campaignID.
   *
   * @return int
   *   value of the total Ads which has the associated campaignID
   */
  public function getAdCount($campaign_id) {
    try {
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
    catch (RequestException $e) {
      \Drupal::logger('example')->notice($e);
    }
  }

  /**
   * Returns the total SKUs which has the associated AdID.
   *
   * @return int
   *   value of the total SKUs which has the associated AdID
   */
  public function getskucountwithadid($ad_id) {
    try {
      $node_storage = $this->entityTypeManager->getStorage('node');
      $query = $node_storage->getQuery()
        ->condition('type', 'ads')
        ->condition('status', 1)
        ->condition('nid', $ad_id, '=');
      $results = $query->execute();
      $sku_count = 0;
      if ($results) {
        foreach ($results as $key => $value) {
          $ad_node = \Drupal::entityTypeManager()
            ->getStorage('node')
            ->load($value);
          $sku_count = $sku_count + count($ad_node->get('field_related_products')->referencedEntities());
        }
      }
      return $sku_count;
    }
    catch (RequestException $e) {
      \Drupal::logger('example')->notice($e);
    }
  }

  /**
   * Returns the AdIDs which has the associated campaignIDs.
   *
   * @return int
   *   ID of the Ads which has the associated campaignIDs
   */
  public function getadids($campaign_ids = NULL) {
    try {
      // If no campaignID is present then return all the ads for the user.
      $ad_ids = [];
      if (empty($campaign_ids)) {
        $campaign_ids = $this->getCampaignIds();
        $node_storage = $this->entityTypeManager->getStorage('node');
        $query = $node_storage->getQuery()
          ->condition('type', 'ads')
          ->condition('status', 1)
          ->condition('field_campaign', $campaign_ids, 'IN');
        $ad_ids = $query->execute();
      }
      else {
        $node_storage = $this->entityTypeManager->getStorage('node');
        $query = $node_storage->getQuery()
          ->condition('type', 'ads')
          ->condition('status', 1)
          ->condition('field_campaign', $campaign_ids, '=');
        $ad_ids = $query->execute();
      }

      return array_unique($ad_ids);
    }
    catch (RequestException $e) {
      \Drupal::logger('example')->notice($e);
    }
  }

  /**
   * Returns the total SKUs which has the associated campaignID.
   *
   * @return int
   *   value of the total SKUs which has the associated campaignID
   */
  public function getskuount($campaign_id) {
    try {
      $node_storage = $this->entityTypeManager->getStorage('node');
      $query = $node_storage->getQuery()
        ->condition('type', 'ads')
        ->condition('status', 1)
        ->condition('field_campaign', $campaign_id, '=');
      $results = $query->execute();
      $skus = 0;
      if ($results) {
        foreach ($results as $key => $value) {
          $ad_node = \Drupal::entityTypeManager()
            ->getStorage('node')
            ->load($value);
          $skus = $skus + count($ad_node->get('field_related_products')->referencedEntities());
        }
      }
      return $skus;
    }
    catch (RequestException $e) {
      \Drupal::logger('example')->notice($e);
    }
  }

  /**
   * Returns the SKUIDs which has the associated campaignIDs.
   *
   * @return int
   *   ID of the SKUs which has the associated campaignIDs
   */
  public function getskuids($campaign_ids = NULL) {
    try {
      $sku_ids = [];
      $campaign_ids = $this->getCampaignIds();
      $node_storage = $this->entityTypeManager->getStorage('node');
      $query = $node_storage->getQuery()
        ->condition('type', 'ads')
        ->condition('status', 1)
        ->condition('field_campaign', $campaign_ids, 'IN');
      $results = $query->execute();
      if ($results) {
        foreach ($results as $key => $value) {
          $ad_node = \Drupal::entityTypeManager()
            ->getStorage('node')
            ->load($value);
          $skus_list = $ad_node->get('field_related_products')->referencedEntities();
          foreach ($skus_list as $sku_key => $sku_value) {
            $sku_ids[] = $sku_value->id();
          }
        }
      }
      // Also grab the SKUs which aren't assigned to no reference ad and not belongs to any campaign.
      $no_reference_query = \Drupal::entityQuery('node')
        ->condition('type', 'ads')
        ->condition('field_no_ad', 'yes')
        ->condition('status', 1)
        ->condition('uid', \Drupal::currentUser()->id());
      $no_reference_ids = $no_reference_query->execute();
      if (!empty($no_reference_ids)) {
        foreach ($no_reference_ids as $no_reference_value) {
          $no_reference_ad_node = \Drupal::entityTypeManager()
            ->getStorage('node')
            ->load($no_reference_value);
          $no_reference_skus_list = $no_reference_ad_node->get('field_related_products')->referencedEntities();
          foreach ($no_reference_skus_list as $no_reference_sku_key => $no_reference_sku_value) {
            $sku_ids[] = $no_reference_sku_value->id();
          }
          $sku_ids[] = $no_reference_value;
        }
      }

      return $sku_ids;
    }
    catch (RequestException $e) {
      \Drupal::logger('example')->notice($e);
    }
  }

  /**
   * Returns the SKUIDs which has the associated AdId.
   *
   * @return int
   *   ID of the SKUs which has the associated campaignIDs
   */
  public function getskubasedonadids($ad_id) {
    try {
      $sku_ids = [];
      dump("service");
      $node_storage = $this->entityTypeManager->getStorage('node');
      $no_reference_ad_node = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->load($ad_id);
      $no_reference_skus_list = $no_reference_ad_node->get('field_related_products')->referencedEntities();
      foreach ($no_reference_skus_list as $no_reference_sku_key => $no_reference_sku_value) {
        $sku_ids[] = $no_reference_sku_value->id();
      }
      return $sku_ids;
    }
    catch (RequestException $e) {
      \Drupal::logger('example')->notice($e);
    }
  }

}
