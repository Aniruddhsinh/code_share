<?php

namespace Drupal\example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\example\Services\AdvertiserContractServices;

/**
 * Implements a listing form with editable field form.
 */
class AdvertiserCampaignListForm extends FormBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\example\Services\AdvertiserContractServices
   */
  protected $advertisercontractservices;

  /**
   * Constructs a new AdvertiserCampaignListForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\example\AdvertiserContractServices $advertiser_contract
   *   The advertiser contract service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AdvertiserContractServices $advertiser_contract) {
    $this->entityTypeManager = $entity_type_manager;
    $this->advertiser_contract = $advertiser_contract;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      // Load the service required to construct this class.
      $container->get('entity_type.manager'),
      $container->get('example.advertiser_contract')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'advertiser_campaign_list_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $contract_id = $this->advertiser_contract->getContractId();
    $contracted_ad_slots = $this->advertiser_contract->getContactedAdSlots($contract_id);
    $campaign_ids = $this->advertiser_contract->getCampaignIds($contract_id);
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'campaign')
      ->condition('field_no_campaign', 'yes')
      ->condition('status', 1)
      ->condition('uid', \Drupal::currentUser()->id());
    $no_reference_ids = $query->execute();
    $no_reference_id = '';
    if (!empty($no_reference_ids)) {
      foreach ($no_reference_ids as $no_reference_value) {
        $no_reference_id = $no_reference_value;
      }
    }
    if (!empty($no_reference_id)) {
      unset($campaign_ids[array_search($no_reference_id, $campaign_ids)]);
    }
    if ($campaign_ids) {
      $form['campaign_ids'] = [
        '#type' => 'hidden',
        '#value' => $campaign_ids,
        '#attributes' => [
          'class' => [
            'hidden-campaign-ids',
          ],
        ],
      ];

      $campaigns = $this->entityTypeManager->getStorage('node')->loadMultiple($campaign_ids);
      $form['campaign_data'] = [
        '#type' => 'details',
        '#tree' => TRUE,
        '#attributes' => [
          'class' => [
            '',
          ],
        ],
      ];
      $total_ad_play = 0;
      $total_ads = 0;
      $total_skus = 0;
      $existing_allotment = [];
      foreach ($campaigns as $campaign_key => $campaign_value) {
        // Get inidividual campaign ad play %.
        $campaign_ad_play = $campaign_value->field_ad_play->value;
        // Assign add play to the existing_values array.
        $existing_allotment[$campaign_value->id()] = $campaign_ad_play;
        // SUM it all the ad plays abd get the total number of ad plays for all campaigns.
        $total_ad_play += $campaign_ad_play;
        // Get campaign status based on the reference from taxonomy term.
        $campaign_status = "";
        if ($campaign_status_val = $campaign_value->get('field_campaign_status')) {
          if (!$campaign_status_val->isEmpty()) {
            $campaign_status = $campaign_status_val->referencedEntities()[0]->label();
          }
        }
        // Get individual ad count for the campaign.
        $ad_count = $this->advertiser_contract->getAdCount($campaign_value->id());
        // Get total ad count for the campagin.
        $total_ads += $ad_count;

        // Get individual sku count for the campaign.
        $sku_count = $this->advertiser_contract->getSKUCount($campaign_value->id());
        // Get total sku count for the campagin.
        $total_skus += $sku_count;

        $form['campaign_data'][$campaign_key] = [
          '#type' => 'details',
        ];
        $form['campaign_data'][$campaign_key]['campaign_id'] = [
          '#markup' => $campaign_value->id(),
        ];
        $form['campaign_data'][$campaign_key]['campaign_title'] = [
          '#markup' => $campaign_value->getTitle(),
        ];
        $form['campaign_data'][$campaign_key]['campaign_status'] = [
          '#markup' => $campaign_status,
        ];
        $form['campaign_data'][$campaign_key]['allocation'] = [
          '#type' => 'number',
          '#default_value' => $campaign_ad_play,
          '#attributes' => [
            'class' => [
              'allocation-input',
            ],
          ],
        ];
        // Pass the campaing_id from here and using twig call the function to fetch number of ads.
        $form['campaign_data'][$campaign_key]['campaign_ads'] = [
          '#markup' => $campaign_value->id(),
        ];
        // Pass the campaing_id from here and using twig call the function to fetch number of skus.
        $form['campaign_data'][$campaign_key]['campaign_skus'] = [
          '#markup' => $campaign_value->id(),
        ];
      }
      $form['total_ad_play'] = [
        '#type' => 'hidden',
        '#value' => $total_ad_play,
      ];
      $form['total_ads'] = [
        '#type' => 'hidden',
        '#value' => $total_ads,
      ];
      $form['total_skus'] = [
        '#type' => 'hidden',
        '#value' => $total_skus,
      ];
      $form['existing_allotment'] = [
        '#type' => 'hidden',
        '#value' => $existing_allotment,
      ];
      $form['contracted_ad_slots'] = [
        '#type' => 'hidden',
        '#value' => $contracted_ad_slots,
      ];
      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Update'),
      ];
    }
    else {
      $form['no_campaign'] = [
        '#markup' => 'No campaign found for current contract.',
      ];
    }
    $form['#attached']['library'][] = 'example/example.ad_play_count';
    $form['#theme'] = 'advertiser_campaigns_list_form';
    return $form;
  }

  /**
   * Form submit method.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $campaign_ids = $form_state->getValue('campaign_ids');
    $contracted_ad_slots = $form_state->getValue('contracted_ad_slots');
    $existing_allotment = $form_state->getValue('existing_allotment');
    foreach ($campaign_ids as $campaign_key => $campaign_value) {
      $updated_allotment = $form_state->getValue(['campaign_data', $campaign_value, 'allocation']);
      if ($existing_allotment[$campaign_value] != $updated_allotment) {
        $campaign_node = $this->entityTypeManager->getStorage('node')->load($campaign_value);
        $campaign_node->set('field_ad_play', $updated_allotment);
        $campaign_node->set('field_ad_slots', ($updated_allotment / 100) * $contracted_ad_slots);
        $campaign_node->save();
      }
    }
  }

}
