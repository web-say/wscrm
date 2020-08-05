<?php

namespace Drupal\wscrm\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Datetime\DrupalDateTime;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;

class WsCrmTaskPayForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wscrm_taskpay';
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $tid=NULL) {
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

      $form['tid'] = array(
        '#type' => 'hidden',
        '#default_value' => $task['tid'],
      );
        
      $form['#prefix'] = '<div role="tabpanel" class="tab-pane" id="edit_pay"><div class="panel panel-default taskpay"> <div class="panel-footer">';

      $form['#suffix'] = '</div></div></div>';
 

      $form['payment'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Payment'),
        '#default_value' => !empty($task['payment']) ? TRUE : '',
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
/*
  // If there are any form errors, AJAX replace the form.
  if ($form_state->hasAnyErrors()) {
    $response->addCommand(new ReplaceCommand('#modal_example_form', $form));
  }
  else {
    $response->addCommand(new OpenModalDialogCommand("Success!", 'The modal form has been submitted.', ['width' => 700]));
  }
*/
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


  $connection = \Drupal::database();
  $result = $connection->update('wscrm_tasks')
            ->fields([
              'payment' => $form_state->getValue('payment') ? '1' : '0'
            ])
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
  return ['config.wscrm_taskpay'];
}



}
