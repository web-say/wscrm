<?php

namespace Drupal\wscrm\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;

class WsCrmCompanyUserCreateForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wscrm_companyysercreate';
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state,$cid=NULL) {
    // Form constructor.
    $form = parent::buildForm($form, $form_state);
    
    $form['cid'] = array(
      '#type' => 'hidden',
      '#default_value' => $cid,
    );
    
    $form['description'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Description user'),
      '#default_value' => $form_state->getValue('description'),
      '#description' => $this->t('Description user'),
    );
    
    $form['uid'] = array(
       '#type' => 'textfield',
       '#title' => $this->t('Users'),
       '#default_value' =>'',
       '#autocomplete_route_name' => 'wscrm.companyuidautocomplete',
    );
    
    return $form;
  }
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    
    $connection = \Drupal::database();
    $result = $connection->insert('wscrm_company_users')
              ->fields(['cid', 'description', 'create','uid'])
              ->values([
                'cid' => $form_state->getValue('cid'),
                'description' => $form_state->getValue('description'),
                'create' => REQUEST_TIME,
                'uid' => $form_state->getValue('uid'),
              ])
              ->execute();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'wscrm.companylist',
    ];
  }

}
