<?php

namespace Drupal\wscrm\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;

class WsCrmCompanyEditForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wscrm_companyedit';
  }

   public $pid;
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state,$cid=NULL) {
    // Form constructor.
    $form = parent::buildForm($form, $form_state);

    $conn = Database::getConnection();
    $record = array();
    $query = $conn->select('wscrm_company', 'cm')
             ->condition('cid', $cid)
             ->fields('cm');
    $company = $query->execute()->fetchAssoc();

    $form['cid'] = array(
      '#type' => 'hidden',
      '#default_value' => $company['cid'],
    );

    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Name company'),
      '#default_value' => $company['name'],
      '#description' => $this->t('Name company'),
    );
    $form['description'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Description company'),
      '#default_value' => $company['description'],
      '#description' => $this->t('Description company'),
    );
    $form['contact'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Contact company'),
      '#default_value' => $company['contact'],
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
    $result = $connection->update('wscrm_company')
                ->fields([
                'name' => $form_state->getValue('name'),
                'description' => $form_state->getValue('description'),
                'contact' => $form_state->getValue('contact'),
                'update' => REQUEST_TIME,

              ])
              ->condition('cid', $form_state->getValue('cid'), '=')
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
