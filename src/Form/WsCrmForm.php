<?php

namespace Drupal\wscrm\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class WsCrmForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wscrm_form';
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Form constructor.
    $form = parent::buildForm($form, $form_state);
    // Default settings.
    $config = $this->config('wscrm.settings');
    // Page title field.
    $form['page_title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Page title'),
      '#default_value' => $config->get('wscrm.page_title'),
      '#description' => $this->t('Page title home page.'),
    );
    // Source text field.
    $form['source_text'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $config->get('wscrm.source_text'),
      '#description' => $this->t('Description home page.'),
    );
    
    $roles = [];
    
    if(user_role_names()) {
      
      foreach(user_role_names() as $k=>$v) {
        
        if($k != 'anonymous' && $k != 'administrator'){
          $roles[$k] = $v;
        }
      }
    }
    
    $form['role_company'] = array(
      '#type'          => 'checkboxes',
      '#title'         => $this->t('Company roles'),
      '#default_value' => $config->get('wscrm.role_company') ? $config->get('wscrm.role_company') : '',
      '#options'       => $roles,
      '#description'   => $this->t('Company roles premission.'),
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
    
    $role_company = [];
    
    if($form_state->getValue('role_company')){
    
      foreach( $form_state->getValue('role_company')  as $k=>$v){
    
        if(!empty($v)){    
    
          $role_company[$k] = $v;
   
        }
   
      }
   
    }
    
    $config = $this->config('wscrm.settings');
    $config->set('wscrm.source_text', $form_state->getValue('source_text'));
    $config->set('wscrm.page_title', $form_state->getValue('page_title'));
    $config->set('wscrm.role_company', $role_company);
    $config->save();
    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'wscrm.settings',
    ];
  }

}
