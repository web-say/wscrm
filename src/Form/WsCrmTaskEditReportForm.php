<?php

namespace Drupal\wscrm\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Datetime\DrupalDateTime;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;

use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\OpenModalDialogCommand;
Use Drupal\Core\Ajax\CloseModalDialogCommand;
Use Drupal\Core\Ajax\CommandInterface;
//use Drupal\views\Ajax\ScrollTopCommand;

class WsCrmTaskEditReportForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wscrm_taskeditreport';
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state,$tid=NULL) {
    // Form constructor.
    $form = parent::buildForm($form, $form_state);

    if(!empty($tid))
    {

      $conn = Database::getConnection();
      $record = array();
      $query = $conn->select('wscrm_tasks', 'ts')
               ->condition('tid', $tid)
               ->fields('ts');
      $task = $query->execute()->fetchAssoc();

      $form['#prefix'] = '<div role="tabpanel" class="tab-pane" id="edit_report_notes"><div class="panel panel-default taskeditreport"> <div class="panel-footer">';

      $form['#suffix'] = '</div></div></div>';

      $form['tid'] = array(
        '#type' => 'hidden',
        '#default_value' => $task['tid'],
      );

      $form['elapsed_time'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Elapsed time'),
        '#default_value' => $task['elapsed_time'],
        '#description' => $this->t('Elapsed time'),
      );

      $form['report_notes'] = array(
            '#type' => 'textarea',
            '#title' => $this->t('Report notes'),
            '#default_value' => $task['report_notes'],
            '#description' => $this->t('Report notes'),
      );

      $form['full'] = [
          '#type' => 'item',
          '#title' => '',
          '#markup' => '',
      ];

      $form['actions'] = array('#type' => 'actions');

      $form['actions']['send'] = [
         '#type' => 'submit',
         '#value' => $this->t('Save'),
         '#attributes' => [
           'class' => [
             'use-ajax',
           ],
         ],
         '#ajax' => [
           'callback' => [$this, 'submitModalFormAjax'],
           'event' => 'click',
         ],
       ];

      $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    }
    return $form;
  }
  /**
 * AJAX callback handler that displays any errors or a success message.
 */
public function submitModalFormAjax(array $form, FormStateInterface $form_state) {
  $response = new AjaxResponse();
  // If there are any form errors, AJAX replace the form.
  if (!$form_state->hasAnyErrors()) {
    // Get the modal form using the form builder.
    $response->addCommand(new OpenModalDialogCommand(t('Success'), t('Update information'), ['width' => '100%']));
    // upload block
    $description = ['#markup' => nl2br($form_state->getValue('report_notes'))];  
    $response->addCommand(new HtmlCommand('#report_notes', $description));
    // close 
    $command = new CloseModalDialogCommand();
    $response->addCommand($command);
    
  }
  
  return $response;
}
/**
 * {@inheritdoc}
 */
public function validateForm(array &$form, FormStateInterface $form_state) {}
/**
 * {@inheritdoc}
 */
public function submitForm(array &$form, FormStateInterface $form_state) {

  $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
  $uid = $user->get('uid')->value;

  $connection = \Drupal::database();
  $result = $connection->update('wscrm_tasks')
              ->fields(['report_notes' => htmlspecialchars($form_state->getValue('report_notes')),'elapsed_time'=>$form_state->getValue('elapsed_time'),'update' => REQUEST_TIME])
              ->condition('tid', $form_state->getValue('tid'), '=')
              ->execute();

  return parent::submitForm($form, $form_state);

}
/**
 * Gets the configuration names that will be editable.
 *
 * @return array
 *   An array of configuration object names that are editable if called in
 *   conjunction with the trait's config() method.
 */
protected function getEditableConfigNames() {
  return ['config.wscrm_taskeditreport'];
}



}
