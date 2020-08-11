<?php
namespace Drupal\WsCrm\Controller;

use Drupal\Core\Url;
use Drupal\Core\Link;

use Drupal\Component\Utility\Html;
use Drupal\Core\Database\Database;

// use Html instead SAfeMarkup

/**
 * Controller routines for Lorem ipsum pages.
 */
class WsCrmController {

  /**
   * Constructs Lorem ipsum text with arguments.
   * This callback is mapped to the path
   * 'loremipsum/generate/{paragraphs}/{phrases}'.
   *
   * @param string $paragraphs
   *   The amount of paragraphs that need to be generated.
   * @param string $phrases
   *   The maximum amount of phrases that can be generated inside a paragraph.
   */

  public function panelshome() {

    $element = [];

    $config = \Drupal::config('wscrm.settings');

    $element['#title'] = $config->get('wscrm.page_title') ? $config->get('wscrm.page_title') : Html::escape(t('WS CRM Panels'));

    $element[]= array(
              '#cache' => ['max-age' => 0,],
              '#markup' => $config->get('wscrm.source_text')
            );

    /** PROJECT **/
    $count_project = wsrcm_project_count();

    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $_url = Url::fromRoute('wscrm.projectcreate', [], ['language' => $language]);
    $userCurrent = \Drupal::currentUser();

    if ($_url->access($userCurrent)) {
      $url_create_project= Url::fromRoute('wscrm.projectcreate');
      $url_create_project_options = array( 'attributes' => array( 'class' => array('btn','btn-success' ), ),);
      $url_create_project->setOptions($url_create_project_options);
      //$link_create_project_link = Link::fromTextAndUrl(t('Create'), $url_create_project)->toString();
      $link_create_project_link = Link::fromTextAndUrl('+', $url_create_project)->toString();
    } else{
      $link_create_project_link = '';
    }

    $url_list_project = Url::fromRoute('wscrm.projectlist');
    $url_list_project_options = array( 'attributes' => array( 'class' => array('btn','btn-default' ), ),);
    $url_list_project->setOptions($url_list_project_options);
    $link_list_project_link = Link::fromTextAndUrl(t('Projects'), $url_list_project)->toString();

    /** TASK **/
    $count_task = wsrcm_task_count();

    $_url = Url::fromRoute('wscrm.taskcreate', [], ['language' => $language]);
    $userCurrent = \Drupal::currentUser();
    if ($_url->access($userCurrent)) {
      $url_create_task= Url::fromRoute('wscrm.taskcreate');
      $url_create_task_options = array( 'attributes' => array( 'class' => array('btn','btn-success' ), ),);
      $url_create_task->setOptions($url_create_task_options);
      //$link_create_task_link = Link::fromTextAndUrl(t('Create'), $url_create_task)->toString();
      $link_create_task_link = Link::fromTextAndUrl('+', $url_create_task)->toString();
    }else{
      $link_create_task_link = '';
    }
    $url_list_task = Url::fromRoute('wscrm.tasklist');
    $url_list_task_options = array( 'attributes' => array( 'class' => array('btn','btn-default' ), ),);
    $url_list_task->setOptions($url_list_task_options);
    $link_list_task_link = Link::fromTextAndUrl(t('Tasks'), $url_list_task)->toString();

    /** Company **/
    $count_сompany = wsrcm_company_count();

    $_url = Url::fromRoute('wscrm.companycreate', [], ['language' => $language]);
    $userCurrent = \Drupal::currentUser();
    if ($_url->access($userCurrent)) {

      $url_create_сompany = Url::fromRoute('wscrm.companycreate');
      $url_create_сompany_options = array( 'attributes' => array( 'class' => array('btn','btn-success' ), ),);
      $url_create_сompany->setOptions($url_create_task_options);
      //$link_create_сompany_link = Link::fromTextAndUrl(t('Create'), $url_create_сompany)->toString();
      $link_create_сompany_link = Link::fromTextAndUrl('+', $url_create_сompany)->toString();
    }else{
      $link_create_сompany_link = '';
    }
    $url_list_сompany = Url::fromRoute('wscrm.companylist');
    $url_list_сompany_options = array( 'attributes' => array( 'class' => array('btn','btn-default' ), ),);
    $url_list_сompany->setOptions($url_list_сompany_options);
    $link_list_сompany_link = Link::fromTextAndUrl(t('Companyes'), $url_list_сompany)->toString();

    $content = '<div class="container-fluid">
                <div class="row">
                <div class="col-sm-6 col-md-4 col-xs-12 col-lg-4">
                  <div class="thumbnail">
                    <div class="caption">
                      <h3>'.t('Companyes').' ('.$count_сompany.')</h3>
                      <p>'.$link_list_сompany_link.' '.$link_create_сompany_link.'</p>
                    </div>
                    <ul>
                      <li> -</li>
                    </ul>
                  </div>
                </div>
                  <div class="col-sm-6 col-md-4 col-xs-12 col-lg-4">
                    <div class="thumbnail">
                      <div class="caption">
                        <h3>'.t('Projects').' ('.$count_project.')</h3>
                        <p>'.$link_list_project_link.' '.$link_create_project_link.'</p>
                      </div>
                    </div>
                  </div>
                  <div class="col-sm-6 col-md-4 col-xs-12 col-lg-4">
                    <div class="thumbnail">
                      <div class="caption">
                        <h3>'.t('Tasks').' ('.$count_task.')</h3>
                        <p>'.$link_list_task_link.' '.$link_create_task_link.'</p>
                      </div>
                    </div>
                  </div>
                </div>
                </div>';

    $element[]= array(
              '#cache' => ['max-age' => 0,],
              '#markup' => $content
            );

    return $element;

  }

}
