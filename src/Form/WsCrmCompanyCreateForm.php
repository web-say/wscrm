<?php

namespace Drupal\wscrm\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;

class WsCrmCompanyCreateForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wscrm_companycreate';
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Form constructor.
    $form = parent::buildForm($form, $form_state);

    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Name company'),
      '#default_value' => $form_state->getValue('name'),
      '#description' => $this->t('Name company'),
    );
    $form['description'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Description company'),
      '#default_value' => $form_state->getValue('description'),
      '#description' => $this->t('Description company'),
    );
    $form['contact'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Contact company'),
      '#default_value' => $form_state->getValue('contact'),
      '#description' => $this->t('Contact company'),
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
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    $uid= $user->get('uid')->value;

    $connection = \Drupal::database();
    $result = $connection->insert('wscrm_company')
              ->fields(['name', 'description', 'contact','create','create_uid'])
              ->values([
                'name' => $form_state->getValue('name'),
                'description' => $form_state->getValue('description'),
                'contact' => $form_state->getValue('contact'),

                'create' => REQUEST_TIME,
                'create_uid' => $uid,
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
