<?php

namespace Drupal\wscrm\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\Element\EntityAutocomplete;

class WsCrmTaskEditForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wscrm_taskedit';
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state,$tid=NULL) {
    // Form constructor.
    $form = parent::buildForm($form, $form_state);

    $conn = Database::getConnection();
    
    $task = wsrcm_task_id($tid);
    
    if(empty($task)){
      
      throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
      
    }else{
      
    $form['tid'] = array(
      '#type' => 'hidden',
      '#default_value' => $task['tid'],
    );

    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Name task'),
      '#default_value' => $task['name'],
      '#description' => $this->t('Name task'),
      '#required' => TRUE,
    );
    
    $form['wuid'] = array(
       '#type' => 'hidden',
       '#title' => $this->t('Task performer'),
       '#default_value' => $task['wuid'],
      // '#autocomplete_route_name' => 'wscrm.taskuidautocomplete',
    );

    $form['estimated_time'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Estimated time'),
      '#default_value' => $task['estimated_time'],
      '#description' => $this->t('Estimated time'),
    );

    $form['description'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Task description'),
      '#default_value' => $task['description'],
      '#description' => $this->t('Task description'),
    /*
      '#type' => 'text_format',
      '#title' => $this->t('Task description'),
      '#default_value' => $task['description'],
      '#description' => $this->t('Task description'),
    //'#format'=> 'crm_editor',*/
    );
/**
    $sth = $conn->select('wscrm_projects', 'pr')
               ->fields('pr', array('pid', 'name'));
    // Execute the statement
    $data = $sth->execute();
    // Get all the results
    $result = $data->fetchAll(\PDO::FETCH_OBJ);
    $projects = array( 0 =>'Without choice');
    foreach ($result as $project){
      $projects[$project->pid] = $project->name;
    }
   */
    $projects_list = wsrcm_project_list('select');
    //$projects = array( 0 =>'Without choice');
    $projects  = [];
    foreach ($projects_list as $kp=>$project){
      $projects[$kp] = $project;
    }
    $form['pid'] = array(
       '#type' => 'select',
       '#title' => $this->t('Project'),
       '#options' => $projects,
       '#default_value' => $task['pid'],
       '#description' => $this->t('Project'),
       '#required' => TRUE,
     );

    $form['price'] = array(
       '#type' => 'textfield',
       '#title' => $this->t('Price'),
       '#default_value' => $task['price'],
       '#description' => $this->t('Price information'),
     );
     
     $form['currency'] = array(
         '#type' => 'select',
         '#title' => $this->t('Currency'),
         '#options' => ['usd'=> 'usd','eur'=> 'eur', 'uah' => 'uah','rub' => 'rub'],
         '#description' => $this->t('Currency'),
         '#default_value' => $task['currency'],
         '#required' => TRUE,
    );

     $form['deadline'] = array(
       '#type' => 'datetime',
       '#title' => $this->t('Deadline'),
       '#size' => 20,
       '#date_date_format' => 'd/m/Y',
       '#date_time_format' => 'H:m',
       '#element_validate' => array(),
       '#default_value' => DrupalDateTime::createFromTimestamp($task['deadline'])
      );

  $form['full'] = [
        '#type' => 'item',
        '#title' => '',
        '#markup' => '',
      ];
    }
    
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
    $result = $connection->update('wscrm_tasks')
              ->fields([
                'name' => $form_state->getValue('name'),
                'description' => \Drupal\Component\Utility\Html::escape($form_state->getValue('description')),
                'estimated_time' => $form_state->getValue('estimated_time'),
                'pid' => $form_state->getValue('pid'),
                'wuid' => $form_state->getValue('wuid'),
                'price' => $form_state->getValue('price'),
                'currency' => $form_state->getValue('currency'),
                'deadline' => strtotime($form_state->getValue('deadline')['date'].' '.$form_state->getValue('deadline')['time']),
                'update' => REQUEST_TIME,
              ])
              ->condition('tid', $form_state->getValue('tid'), '=')
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
