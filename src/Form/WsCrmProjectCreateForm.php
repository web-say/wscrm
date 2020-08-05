<?php

namespace Drupal\wscrm\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;

class WsCrmProjectCreateForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wscrm_projectcreate';
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Form constructor.
    $form = parent::buildForm($form, $form_state);

    $conn = Database::getConnection();

    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Name project'),
      '#default_value' => $form_state->getValue('name'),
      '#description' => $this->t('Name project'),
      '#required' => TRUE,
    );
    $form['description'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Description project'),
      '#default_value' => $form_state->getValue('description'),
      '#description' => $this->t('Description project'),
    );
    $form['contact'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Contact project'),
      '#default_value' => $form_state->getValue('contact'),
      '#description' => $this->t('Contact project'),
    );
    $form['type'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Type project'),
      '#default_value' => $form_state->getValue('type'),
      '#description' => $this->t('Type project'),
    );

    //$company =  array('0' => t('Make a selection'));
    $company = [];
    
    if(wsrcm_company_count()>0)
    {
      $company_list = wsrcm_company_list(1);
      foreach ($company_list as $cm){
        $company[$cm->cid] = $cm->name;
      }
    }
    

    $form['company'] = array(
      '#type' => 'select',
      '#title' => $this->t('Company project'),
      '#default_value' => $form_state->getValue('company'),
      '#options' => $company,
      '#description' => $this->t('Company project'),
      '#required' => TRUE,
    );
    
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    $uid = $user->get('uid')->value;
    if( $uid == '1'){
      $sth = $conn->select('users_field_data', 'u')
                 ->fields('u', array('uid', 'name'))
                 ->condition('u.uid', '0', '>');
      // Execute the statement
      $data = $sth->execute();
      // Get all the results
      $result = $data->fetchAll(\PDO::FETCH_OBJ);
      $users =  array('0' => t('Make a selection'));
      foreach ($result as $user){
        $users[$user->uid] = $user->name;
      }
      $default_value_pmanager = $form_state->getValue('pmanager');
      
    }else{
      $users[$uid] = $user->get('name')->value;
      $default_value_pmanager = $uid;
    }
    $form['pmanager'] = array(
      '#type' => 'select',
      '#title' => $this->t('Project manager'),
      '#options' => $users,
      '#default_value' => $default_value_pmanager,
      '#description' => $this->t('Project manager'),
      '#required' => TRUE,
    );

    $form['site'] = [
      '#type' => 'item',
      '#title' => '',
      '#markup' => '<h3>'.t('Site').'</h3>',
    ];

    $form['site_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Project site'),
      '#default_value' => $form_state->getValue('site_url'),
      '#description' => $this->t('Project site'),
    );

    $form['site_admin'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Admin url'),
      '#default_value' => $form_state->getValue('site_admin'),
      '#description' => $this->t('Site url admin project'),
    );
    $form['site_login'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Login'),
      '#default_value' => $form_state->getValue('site_login'),
      '#description' => $this->t('Site login project'),
    );
    $form['site_pass'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Password'),
      '#default_value' => $form_state->getValue('site_pass'),
      '#description' => $this->t('Site password project'),
    );

    $form['host'] = [
      '#type' => 'item',
      '#title' => '',
      '#markup' => '<h3>'.t('Hosting').'</h3>',
    ];

    $form['host_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Url host'),
      '#default_value' => $form_state->getValue('host_url'),
      '#description' => $this->t('Site url host'),
    );
    $form['host_login'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Login'),
      '#default_value' => $form_state->getValue('host_login'),
      '#description' => $this->t('Site login host'),
    );
    $form['host_pass'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Password'),
      '#default_value' => $form_state->getValue('host_pass'),
      '#description' => $this->t('Site password host'),
    );

    $form['domain'] = [
      '#type' => 'item',
      '#title' => '',
      '#markup' => '<h3>'.t('Domain').'</h3>',
    ];
    $form['domain_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Domain host'),
      '#default_value' => $form_state->getValue('host_url'),
      '#description' => $this->t('Domain url'),
    );
    $form['domain_login'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Domain Login'),
      '#default_value' => $form_state->getValue('host_login'),
      '#description' => $this->t('Domain login'),
    );
    $form['domain_pass'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Domain Password'),
      '#default_value' => $form_state->getValue('domain_pass'),
      '#description' => $this->t('Domain password '),
    );

    $form['mail'] = [
      '#type' => 'item',
      '#title' => '',
      '#markup' => '<h3>'.t('Mail').'</h3>',
    ];
    $form['mail_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Mail host'),
      '#default_value' => $form_state->getValue('mail_url'),
      '#description' => $this->t('Mail url'),
    );
    $form['mail_login'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Mail Login'),
      '#default_value' => $form_state->getValue('mail_login'),
      '#description' => $this->t('Mail login'),
    );
    $form['mail_pass'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Mail Password'),
      '#default_value' => $form_state->getValue('mail_pass'),
      '#description' => $this->t('Mail password'),
    );

    $form['ftp'] = [
      '#type' => 'item',
      '#title' => '',
      '#markup' => '<h3>'.t('FTP').'</h3>',
    ];

    $form['ftp_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Server'),
      '#default_value' => $form_state->getValue('ftp_url'),
      '#description' => $this->t('Server FTP'),
    );
    $form['ftp_login'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Login'),
      '#default_value' => $form_state->getValue('ftp_login'),
      '#description' => $this->t('FTP login project'),
    );
    $form['ftp_pass'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Password'),
      '#default_value' => $form_state->getValue('ftp_pass'),
      '#description' => $this->t('FTP password project'),
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
    $result = $connection->insert('wscrm_projects')
              ->fields(['name', 'description', 'contact','type','company','pmanager',
                        'ftp_url','ftp_login','ftp_pass',
                        'site_url','site_login','site_admin','site_pass',
                        'host_url','host_login','host_pass',
                        'domain_url','domain_login','domain_pass',
                        'mail_url','mail_login','mail_pass',
                        'create','create_uid'])
              ->values([
                'name' => $form_state->getValue('name'),
                'description' => $form_state->getValue('description'),
                'contact' => $form_state->getValue('contact'),

                'type' => $form_state->getValue('type'),
                'company' => $form_state->getValue('company'),
                'pmanager' => $form_state->getValue('pmanager'),

                'ftp_url' => $form_state->getValue('ftp_url'),
                'ftp_login' => $form_state->getValue('ftp_login'),
                'ftp_pass' => $form_state->getValue('ftp_pass'),

                'host_url' => $form_state->getValue('host_url'),
                'host_login' => $form_state->getValue('host_login'),
                'host_pass' => $form_state->getValue('host_pass'),

                'domain_url' => $form_state->getValue('domain_url'),
                'domain_login' => $form_state->getValue('domain_login'),
                'domain_pass' => $form_state->getValue('domain_pass'),

                'mail_url' => $form_state->getValue('mail_url'),
                'mail_login' => $form_state->getValue('mail_login'),
                'mail_pass' => $form_state->getValue('mail_pass'),

                'site_url' => $form_state->getValue('site_url'),
                'site_admin' => $form_state->getValue('site_admin'),
                'site_login' => $form_state->getValue('site_login'),
                'site_pass' => $form_state->getValue('site_pass'),

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
      'wscrm.projectlist',
    ];
  }

}
