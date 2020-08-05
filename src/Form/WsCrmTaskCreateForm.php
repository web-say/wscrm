<?php

namespace Drupal\wscrm\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Datetime\DrupalDateTime;

class WsCrmTaskCreateForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wscrm_taskcreate';
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state,$tid='') {
    // Form constructor.
    $form = parent::buildForm($form, $form_state);

    $conn = Database::getConnection();

    $form['task_parent'] = array(
      '#type' => 'hidden',
      '#title' => $this->t('Task parent'),
      '#default_value' => $tid ? $tid : $form_state->getValue('task_parent'),
      '#description' => $this->t('Task parent'),
    );

    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Name task'),
      '#default_value' => $form_state->getValue('name'),
      '#description' => $this->t('Name task'),
      '#required' => TRUE,
    );
    
    $form['wuid'] = array(
       '#type' => 'hidden',
       '#title' => $this->t('Task performer'),
       '#default_value' =>'1',
      // '#autocomplete_route_name' => 'wscrm.taskuidautocomplete',
    );

    $form['estimated_time'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Estimated time'),
      '#default_value' => $form_state->getValue('estimated_time')?$form_state->getValue('estimated_time'):'0',
      '#description' => $this->t('Estimated time'),
    );

    $form['description'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Task description'),
      '#default_value' => $form_state->getValue('description'),
      '#description' => $this->t('Task description'),
    );


      //$projects = array( 0 =>t('Make a selection'));
      
      $projects = [];
      
      if(wsrcm_project_count()>0){
       
        $projects_list = wsrcm_project_list('show');
        
        if(!empty($projects_list)){
          
          foreach ($projects_list as $k=>$v){
            
            $projects[$k] = $v;
            
          }
          
        }
        
      }

      if(!empty($tid)) {
        
        $arr = wsrcm_task_id($tid);
        
        $form['pid'] = array(
           '#type' => 'select',
           '#title' => $this->t('Project'),
           '#options' => $projects,
           '#description' => $this->t('Project'),
           '#default_value'=> $arr['pid'],
           '#disabled' =>'disabled',
           '#required' => TRUE,
        );
      }else{
      $form['pid'] = array(
         '#type' => 'select',
         '#title' => $this->t('Project'),
         '#options' => $projects,
         '#description' => $this->t('Project'),
         '#required' => TRUE,
      );
    }
    $form['price'] = array(
       '#type' => 'textfield',
       '#title' => $this->t('Price'),
       '#default_value' => $form_state->getValue('price'),
       '#description' => $this->t('Price information'),
    );
     
    
    $form['currency'] = array(
         '#type' => 'select',
         '#title' => $this->t('Currency'),
         '#options' => ['usd'=> 'usd','eur'=> 'eur', 'uah' => 'uah','rub' => 'rub'],
         '#description' => $this->t('Currency'),
         '#required' => TRUE,
    );

     $form['deadline'] = array(
       '#type' => 'datetime',
       '#title' => $this->t('Deadline'),
       '#size' => 20,
       '#date_date_format' => 'd/m/Y',
       '#date_time_format' => 'H:m',
       '#default_value' => '0',//DrupalDateTime::createFromTimestamp(time()),
       '#element_validate' => array(),
      );

    $form['full'] = [
            '#type' => 'item',
            '#title' => '',
            '#markup' => '',
          ];

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
    
    $result = $connection->insert('wscrm_tasks')
              ->fields(['name', 'wuid', 'description', 'estimated_time','task_parent',
                        'pid','price','deadline','create','create_uid', 'manager','currency',
                        'worker_notes','manager_notes','debug_notes','report_notes','payment'])
              ->values([
                'name' => $form_state->getValue('name'),
                'wuid' => $form_state->getValue('wuid'),
                'description' => htmlspecialchars($form_state->getValue('description')),
                'estimated_time' => $form_state->getValue('estimated_time')?$form_state->getValue('estimated_time'):'0',
                'pid' => $form_state->getValue('pid')?$form_state->getValue('pid'):'0',
                'price' => $form_state->getValue('price')?$form_state->getValue('price'):'0',
                'currency' => $form_state->getValue('currency'),
                'deadline' => $form_state->getValue('deadline')['date'] ? strtotime($form_state->getValue('deadline')['date'].' '.$form_state->getValue('deadline')['time']) : '0',
                'task_parent'=>$form_state->getValue('task_parent')?$form_state->getValue('task_parent'):'0',
                'create' => time(),
                'create_uid' => $uid,
                'manager'=> $uid,
                'worker_notes'=>'',
                'manager_notes'=>'',
                'debug_notes'=>'',
                'report_notes'=>'',
                'payment'=>'0'
              ])
              ->execute();
    
    $result = $connection->insert('wscrm_task_status')
            ->fields(['tid', 'status', 'description','create','create_uid'])
            ->values([
              'tid' => $result,
              'status' => 'new',
              'description' => '',
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
      'wscrm.tasklist',
    ];
  }

}
