<?php
namespace Drupal\WsCrm\Controller;

use Drupal\Core\Url;
use Drupal\Core\Link;

use Drupal\Component\Utility\Html;
use Drupal\Core\Database\Database;
use Drupal\user\Entity\User;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Controller\ControllerBase;

use Drupal\Core\Form\ConfigFormBase;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;

use Drupal\Component\Render\FormattableMarkup;

// use Html instead SAfeMarkup

/**
 * Controller routines for Lorem ipsum pages.
 */
class TaskController  {

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

    public function tasklist($pid='0') {

      $element = [];

      $element['#title'] = Html::escape(t('Tasks'));

      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
      $_url = Url::fromRoute('wscrm.taskcreate', [], ['language' => $language]);
      $userCurrent = \Drupal::currentUser();

      if ($_url->access($userCurrent)) {

        $url_create = Link::createFromRoute(t('New task'), 'wscrm.taskcreate')
                      ->toString()
                      ->getGeneratedLink();

        $element[] = array(
                '#cache' => ['max-age' => 0,],
                '#markup' => $url_create
              );

      }

      if(!empty($pid)){
        if(wsrcm_project_access($pid)){
          $projects_list = [$pid];
        }else{
          throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
        }

      } else {

        $projects_list = wsrcm_project_list();

      }
        $connection = Database::getConnection();

        $projects = $connection->select('wscrm_projects', 'pr')
              ->condition('pid',$projects_list, 'IN')
              ->fields('pr', array('pid', 'name','site_url'));

        // Execute the statement
        $executed_projects = $projects->execute();

        // Get all the results
        $results_projects = $executed_projects->fetchAll(\PDO::FETCH_OBJ);

      if(!empty($results_projects)) {

        foreach ($results_projects as $row_pro)
        {

          $link_to_site = Link::fromTextAndUrl($row_pro->site_url, Url::fromUri($row_pro->site_url, ['attributes' => ['class' => ['link'],'target' => '_blank'] ] ))->toString();

          $link_to_project_task_link = Url::fromRoute('wscrm.projecttasklist',['pid'=>$row_pro->pid]);
          $link_to_project_task = Link::fromTextAndUrl(t('Tasks'), $link_to_project_task_link)->toString();


          $connection = Database::getConnection();

          $projects = $connection->select('wscrm_tasks', 'ts')
                      ->fields('ts', array('tid', 'name', 'deadline', 'create','price','payment','currency','estimated_time','pid'))
                      ->condition('delete', '0')
                      ->condition('task_parent', '0')
                      ->condition('pid', $row_pro->pid);

          // Execute the statement
          $executed = $projects->execute();

          // Get all the results
          $results = $executed->fetchAll(\PDO::FETCH_OBJ);

          $data = [];

          $i = 1;

          $all_price = 0;

          $all_time = 0;

          if(!empty($results)){
            // Iterate results
            foreach ($results as $row) {

              if(empty($row->price)) { $row->price = 0; }

              $url = Url::fromRoute('wscrm.taskshow',['tid'=>$row->tid]);
              $link_to_task = Link::fromTextAndUrl($row->name, $url);

              if(!empty($row->create)) {
                $create = date('d.m.Y', $row->create);
              }else{
                $create = '';
              }

             // $url_edit = Url::fromRoute('wscrm.taskedit',['tid'=>$row->tid]);
             // $link_to_task_edit = Link::fromTextAndUrl(t('Edit'), $url_edit);

              $status = wsrcm_task_status($row->tid);

              if(empty($status['status'])) { $status['status'] = '' ; }

              if(!empty($row->deadline)){
                $deadline = wscre_deadline($row->deadline,$status['status']);
              }else{
                $deadline = '';
              }

              $config = \Drupal::config('wscrm.settings');
              $result = $config->get('wscrm.status');

              $status_name = !empty($status['status'])?t($result[$status['status']]):'';

              if(!empty($row->payment)){
                $payment = new FormattableMarkup('@pay <span class="glyphicon glyphicon-ok wscrm_pay_on"></span> ',['@pay' => ''.$row->price .' '.$row->currency]);
              } else{
                $payment = $row->price .' '.$row->currency;
              }

              $data[] = [$i,$link_to_task,$create,$deadline,$status_name,$payment,$row->estimated_time.' '.t('hour'),
              //$link_to_task_edit
              ];

              $all_price += $row->price;

              $all_time += $row->estimated_time;

              $i++;

              $count_parent_task = $this->tasklistparentcount($row->pid, $row->tid);

              if($count_parent_task > 0){

                $data_parent = $this->tasklistparent($row->pid, $row->tid);

                foreach ($data_parent as $keyp => $valp) {

                  if(!empty($valp['payment'])){
                    $payment_1 = new FormattableMarkup('@pay <span class="glyphicon glyphicon-ok wscrm_pay_on"></span> ',['@pay' => ''.$valp['price'] .' '.$valp['currency']]);
                  } else{
                    $payment_1 = $valp['price'] .' '.$valp['currency'];
                  }

                  $data[] = [ '∟',$valp['task_name'],$valp['create'],$valp['deadline'],$valp['status'],$payment_1 ,$valp['estimated_time'].' '.t('hour'),
                              //$valp['link_to_edit']
                              ];
                  $all_price  += $valp['price'];
                  $all_time   += $valp['estimated_time'];
                }

              }

            }
          }

            if(count($results) > 0){

              $element[] = array(
                      '#cache' => ['max-age' => 0,],
                      '#markup' => '<h5 class="project-name">'.$row_pro->name.' ('.$link_to_site.') '.$link_to_project_task.'</h5>',
                    );

              $header = ['#', t('Name task'), t('Date create'), t('Deadline'), t('Status'), t('Price'), t('Time')];

              $datas = [];

              $datas[] = [t('Total').':','','','','',$all_price.' ',$all_time.' '.t('hour')];

              $element[] = array(
                '#theme' => 'table',
                '#cache' => ['max-age' => 0,],
                '#header' => $header,
                '#rows' => $data,
                 '#attributes' => array('class'=>array('wscrm-table-task')),
                '#footer'=>$datas,
              );
            } else {
              $element[] = array(
                      '#cache' => ['max-age' => 0,],
                      '#markup' => '<h5 class="project-name">'.$row_pro->name.' ('.$link_to_site.')</h5>',
                    );
              $element[] = array(
                        '#cache' => ['max-age' => 0,],
                        '#markup' => '<p class="small">Задачи не найдены. '.$url_create.'</p>',
                      );
            }
        }

      } else {

        $element[] = array(
                    '#cache' => ['max-age' => 0,],
                    '#markup' => '<p>Задачи не найдены. '.$url_create.'</p>',
                  );
      }

      return $element;

    }

    public function tasklistparent($pid='0',$parent='0'){

      $data = [];

      if(!empty($pid) && !empty($parent)){

        $connection = Database::getConnection();

        $projects = $connection->select('wscrm_tasks', 'ts')
                    ->fields('ts', array('tid', 'name', 'deadline', 'create','price','payment','currency','estimated_time'))
                    ->condition('delete', '0')
                    ->condition('task_parent', $parent)
                    ->condition('pid', $pid);

        // Execute the statement
        $executed = $projects->execute();

        // Get all the results
        $results = $executed->fetchAll(\PDO::FETCH_OBJ);

        $data = [];

        // Iterate results
        foreach ($results as $row) {

          $url = Url::fromRoute('wscrm.taskshow',['tid'=>$row->tid]);
          $link_to_task = Link::fromTextAndUrl($row->name, $url)->toString();

          if(!empty($row->create)) {
            $create = date('d.m.Y', $row->create);
          }else{
            $create = '';
          }

          //$url_edit = Url::fromRoute('wscrm.taskedit',['tid'=>$row->tid]);
          //$link_to_task_edit = Link::fromTextAndUrl(t('Edit'), $url_edit);

          $status = wsrcm_task_status($row->tid);

          if(empty($status['status'])) { $status['status'] = '' ; }

          if(!empty($row->deadline)){
              $deadline = wscre_deadline($row->deadline,$status['status']);
          }else{
            $deadline = '';
          }

          $config = \Drupal::config('wscrm.settings');
          $result = $config->get('wscrm.status');

          $status_name = !empty($status['status'])?t($result[$status['status']]):'';

          $data[] = ['parent'         =>  $parent,
                     'task_name'      =>  $link_to_task,
                     'create'         =>  $create,
                     'deadline'       =>  $deadline,
                     'status'         =>  $status_name,
                     'price'          =>  $row->price,
                     'payment'        =>  $row->payment,
                     'currency'       =>  $row->currency,
                     'estimated_time' =>  $row->estimated_time,
                    // 'link_to_edit'   =>  $link_to_task_edit
          ];

        }
        return $data;
      }
    }


    public function tasklistparentcount($pid='0',$parent='0') {

      $task = 0;

      if(!empty($pid)){

        $query = \Drupal::database()->select('wscrm_tasks', 'ts')
                        ->condition('task_parent', $parent)
                        ->condition('pid', $pid)
                        ->fields('ts', ['tid']);

        $task = $query->countQuery()->execute()->fetchField();

      }

      return $task;
    }


    public function taskshow($tid='0') {

      $tasks = wsrcm_task_list();
      $yes = 0;
      foreach($tasks as $kp=>$vp){
        if($vp == $tid){ $yes = 1;}
      }

      if(!empty($tid) && $yes == 1) {

      $conn = Database::getConnection();

      $query = $conn->select('wscrm_tasks', 'ts')
          ->condition('delete', '0')
          ->condition('tid', $tid)
          ->fields('ts');

      $task = $query->execute()->fetchAssoc();

      $content = '';

      //echo "<pre>"; print_r($task); echo "</pre>";

      if(!empty($task))
      {
        $data = [];

        if(!empty($task['tid']))
        {
          $perms_pay = \Drupal::currentUser()->hasPermission('generate wscrm_tasks_pay');

          $tid = $task['tid'];

          $element['#title'] = Html::escape($task['name']);

          $project_info = wsrcm_project_id($task['pid']);

          if(!empty($project_info['pmanager'])){
            $user_manager_load = \Drupal\user\Entity\User::load($project_info['pmanager']);
            $project_manager = $user_manager_load->getAccountName();
          }else{
            $project_manager = '';
          }

          if(!empty($task['wuid'])){
            $user_wuid_load = \Drupal\user\Entity\User::load($task['wuid']);
            $wuid = $user_wuid_load->getAccountName();
          }else{
            $wuid = '-';
          }

          $link_to_site = Link::fromTextAndUrl($project_info['site_url'], Url::fromUri($project_info['site_url'], ['attributes' => ['class' => ['link'],'target' => '_blank'] ] ))->toString();

          $project_link = Link::fromTextAndUrl($project_info['name'], Url::fromRoute('wscrm.projectshow',['pid'=>$task['pid']]))->toString();

          $url_edit = Url::fromRoute('wscrm.taskedit',['tid'=>$task['tid']],['absolute' => TRUE])->toString();

          $url_edit_new = Url::fromUri($url_edit, ['attributes' => ['class' => ['btn','btn-warning'],'target' => '_blank'] ]);

          $link_to_task_edit = Link::fromTextAndUrl(' '.t('Edit'), $url_edit_new )->toString();

          $link_to_task_create = Url::fromRoute('wscrm.taskcreateparent',['tid'=>$task['tid']],['absolute' => TRUE])->toString();
          $link_to_task_create_link = Url::fromUri($link_to_task_create, ['attributes' => ['class' => ['btn','btn-success'],'target' => '_blank'] ]);
          $link_to_task_create_parent = Link::fromTextAndUrl(' '.t('New task'), $link_to_task_create_link )->toString();

          $element[]['#markup'] = '<div class="container-fluid"><div class="row"><div class="col-12"><h4>'.$project_link.' : '.$link_to_site.'</h4><h5>'.$link_to_task_edit.' '.$link_to_task_create_parent.'</h5></div></div></div>';

          if(!empty($task['manager'])){
            $user_manager = \Drupal\user\Entity\User::load($task['manager']);
            $manager = $user_manager->getAccountName();
          }else{
            $manager = '';
          }

          $status = wsrcm_task_status($task['tid']);
          $config = \Drupal::config('wscrm.settings');
          $result = $config->get('wscrm.status');
          $status_name = '';
          if(!empty($status['status']) && !empty($result[$status['status']])) { $status_name = $result[$status['status']]; }

          if(!empty($task['payment'])){
            $payment = new FormattableMarkup('@pay <span class="glyphicon glyphicon-ok wscrm_pay_on"></span> <strong>Оплачено</strong>',['@pay' => ''.$task['price'] .' '.$task['currency']]);
          } else{
            $payment = $task['price'] .' '.$task['currency'];
          }

          $data[] = [t('Name task'),$task['name']];
          $data[] = [t('Project manager'),$project_manager];
          $data[] = [t('Manager task'),   $manager];
          $data[] = [t('Task performer'), $wuid];
          $data[] = [t('Date create'),    $task['create'] ? date('d.m.Y H:i', $task['create']) : ''];
          $data[] = [t('Deadline'),       $task['deadline'] ? wscre_deadline($task['deadline']) : ''];
          $data[] = [t('Estimated time'), $task['estimated_time'] ? $task['estimated_time'] .' '.t('hour'): '-'];
          $data[] = [t('Planned time'),   $task['planned_time'] ? $task['planned_time'].' '.t('hour') : '-'];
          $data[] = [t('Elapsed time'),   $task['elapsed_time'] ? $task['elapsed_time'].' '.t('hour') : '-'];
          $data[] = [t('Price'),          $payment ];
          $data[] = [t('Status'),         t($status_name)];

          $element[] = array(
              '#theme' => 'table',
              '#cache' => ['max-age' => 0,],
              '#header' => [],
              '#rows' => $data,
            );

          $t_yes  = '&nbsp;<span class="icon glyphicon glyphicon-eye-open"></span>&nbsp;';

          if(!empty($task['description']))  { $t_desc = $t_yes; } else { $t_desc = ''; }
          if(!empty($task['worker_notes'])) { $t_wono = $t_yes; } else { $t_wono = ''; }
          if(!empty($task['manager_notes'])){ $t_mano = $t_yes; } else { $t_mano = ''; }
          if(!empty($task['debug_notes']))  { $t_deno = $t_yes; } else { $t_deno = ''; }
          if(!empty($task['report_notes'])) { $t_reno = $t_yes; } else { $t_reno = ''; }


          $tab_content = '<div id="tasktabs">
                          <ul class="nav nav-tabs" role="tablist">
                            <li class="active" role="presentation" ><a href="#description" aria-controls="description" role="tab" data-toggle="tab" aria-expanded="true">'.t('Task description').$t_desc.'</a></li>
                            <li role="presentation"><a href="#edit_description" aria-controls="description" role="tab" data-toggle="tab"><span class="icon glyphicon glyphicon-pencil"></span></a></li>
                            <li role="presentation"><a href="#worker_notes" aria-controls="profile" role="tab" data-toggle="tab">'.t('Worker notes').''.$t_wono.'</a></li>
                            <li role="presentation"><a href="#edit_worker_notes" aria-controls="profile" role="tab" data-toggle="tab"><span class="icon glyphicon glyphicon-pencil"></span></a></li>
                            <li role="presentation"><a href="#manager_notes" aria-controls="messages" role="tab" data-toggle="tab">'.t('Manager notes').''.$t_mano.'</a></li>
                            <li role="presentation"><a href="#edit_manager_notes" aria-controls="messages" role="tab" data-toggle="tab"><span class="icon glyphicon glyphicon-pencil"></span></a></li>
                            <li role="presentation"><a href="#debug_notes" aria-controls="settings" role="tab" data-toggle="tab">'.t('Debug notes').''.$t_deno.'</a></li>
                            <li role="presentation"><a href="#edit_debug_notes" aria-controls="settings" role="tab" data-toggle="tab"><span class="icon glyphicon glyphicon-pencil"></span></a></li>
                            <li role="presentation"><a href="#report_notes" aria-controls="settings" role="tab" data-toggle="tab">'.t('Report notes').''.$t_reno.'</a></li>
                            <li role="presentation"><a href="#edit_report_notes" aria-controls="settings" role="tab" data-toggle="tab"><span class="icon glyphicon glyphicon-pencil"></span></a></li>
                            <li role="presentation"><a href="#edit_status" aria-controls="settings" role="tab" data-toggle="tab">'.t('Status').'</a></li>
                          ';
          if(!empty($perms_pay)){
            $tab_content.='<li role="presentation"><a href="#edit_pay" aria-controls="settings" role="tab" data-toggle="tab">'.t('Payment').'</a></li>';
          }

          $tab_content.=' </ul>
                        <div class="tab-content">';

          $element[]=['#markup' =>  $tab_content,'#cache' => ['max-age' => 0,]];;

            $element[] = [ '#cache' => ['max-age' => 0,], '#markup' => ('<div role="tabpanel" class="tab-pane active" id="description">'.nl2br($task['description']).'</div>')];
            $link_desc =\Drupal::formBuilder()->getForm('Drupal\wscrm\Form\WsCrmTaskEditDescriptionForm',$tid);
            $element[] = [ '#cache' => ['max-age' => 0,], '#markup' => \Drupal::service('renderer')->render($link_desc)];

            $element[] = [ '#cache' => ['max-age' => 0,], '#markup' => ('<div role="tabpanel" class="tab-pane" id="worker_notes">'.nl2br($task['worker_notes']).'</div>')];
            $link_work = \Drupal::formBuilder()->getForm('Drupal\wscrm\Form\WsCrmTaskEditWorkerForm',     $tid);
            $element[] = [ '#cache' => ['max-age' => 0,], '#markup' => \Drupal::service('renderer')->render($link_work)];

            $element[] = [ '#cache' => ['max-age' => 0,], '#markup' => ('<div role="tabpanel" class="tab-pane" id="manager_notes">'.nl2br($task['manager_notes']).'</div>')];
            $link_manager  = \Drupal::formBuilder()->getForm('Drupal\wscrm\Form\WsCrmTaskEditManagerForm',    $tid);
            $element[] = [ '#cache' => ['max-age' => 0,], '#markup' => \Drupal::service('renderer')->render($link_manager)];

            $element[] = [ '#cache' => ['max-age' => 0,], '#markup' => ('<div role="tabpanel" class="tab-pane" id="debug_notes">'.nl2br($task['debug_notes']).'</div>')];
            $link_debug = \Drupal::formBuilder()->getForm('Drupal\wscrm\Form\WsCrmTaskEditDebugForm',      $tid);
            $element[] = [ '#cache' => ['max-age' => 0,], '#markup' => \Drupal::service('renderer')->render($link_debug)];

            $element[] = [ '#cache' => ['max-age' => 0,], '#markup' => ('<div role="tabpanel" class="tab-pane" id="report_notes">'.nl2br($task['report_notes']).'</div>')];
            $link_report = \Drupal::formBuilder()->getForm('Drupal\wscrm\Form\WsCrmTaskEditReportForm',     $tid);
            $element[] = [ '#cache' => ['max-age' => 0,], '#markup' => \Drupal::service('renderer')->render($link_report)];

            $link_status = \Drupal::formBuilder()->getForm('Drupal\wscrm\Form\WsCrmTaskEditStatusForm',     $tid);
            $element[] = [ '#cache' => ['max-age' => 0,], '#markup' => \Drupal::service('renderer')->render($link_status)];

            if(!empty($perms_pay)){
              $link_pay = \Drupal::formBuilder()->getForm('Drupal\wscrm\Form\WsCrmTaskPayForm',     $tid);
              $element[] = [ '#cache' => ['max-age' => 0,], '#markup' => \Drupal::service('renderer')->render($link_pay)];
            }

            $element[] = ['#markup' => '</div></div>','#cache' => ['max-age' => 0,]];

            /*
            $perms = array_keys(\Drupal::service('user.permissions')->getPermissions());
            echo "<pre>";
            print_r($perms);
            echo "</pre>";
            */


           /*
            $element[]=['#markup' => '<div id="tasktabs">
                            <ul class="nav nav-tabs" role="tablist">
                              <li role="presentation"><a href="#edit_description" aria-controls="description" role="tab" data-toggle="tab">'.t('Task description').'</a></li>
                              <li role="presentation"><a href="#edit_worker_notes" aria-controls="profile" role="tab" data-toggle="tab">'.t('Worker notes').'</a></li>
                              <li role="presentation"><a href="#edit_manager_notes" aria-controls="messages" role="tab" data-toggle="tab">'.t('Manager notes').'</a></li>
                              <li role="presentation"><a href="#edit_debug_notes" aria-controls="settings" role="tab" data-toggle="tab">'.t('Debug notes').'</a></li>
                              <li role="presentation"><a href="#edit_report_notes" aria-controls="settings" role="tab" data-toggle="tab">'.t('Report notes').'</a></li>
                              <li role="presentation"><a href="#edit_status" aria-controls="settings" role="tab" data-toggle="tab">'.t('Status').'</a></li>
                            </ul>
                            <div class="tab-content">','#cache' => ['max-age' => 0,]];

            $link_desc =\Drupal::formBuilder()->getForm('Drupal\wscrm\Form\WsCrmTaskEditDescriptionForm',$tid);
            $element[] = [ '#cache' => ['max-age' => 0,], '#markup' => \Drupal::service('renderer')->render($link_desc)];

            $link_work = \Drupal::formBuilder()->getForm('Drupal\wscrm\Form\WsCrmTaskEditWorkerForm',     $tid);
            $element[] = [ '#cache' => ['max-age' => 0,], '#markup' => \Drupal::service('renderer')->render($link_work)];

            $link_manager  = \Drupal::formBuilder()->getForm('Drupal\wscrm\Form\WsCrmTaskEditManagerForm',    $tid);
            $element[] = [ '#cache' => ['max-age' => 0,], '#markup' => \Drupal::service('renderer')->render($link_manager)];

            $link_debug = \Drupal::formBuilder()->getForm('Drupal\wscrm\Form\WsCrmTaskEditDebugForm',      $tid);
            $element[] = [ '#cache' => ['max-age' => 0,], '#markup' => \Drupal::service('renderer')->render($link_debug)];

            $link_report = \Drupal::formBuilder()->getForm('Drupal\wscrm\Form\WsCrmTaskEditReportForm',     $tid);
            $element[] = [ '#cache' => ['max-age' => 0,], '#markup' => \Drupal::service('renderer')->render($link_report)];

            $link_status = \Drupal::formBuilder()->getForm('Drupal\wscrm\Form\WsCrmTaskEditStatusForm',     $tid);
            $element[] = [ '#cache' => ['max-age' => 0,], '#markup' => \Drupal::service('renderer')->render($link_status)];

            $element[] = ['#markup' => '</div></div>','#cache' => ['max-age' => 0,]];
          */
          }

          return $element;

        }
      }
      else{
        throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
      }
    }


    function getNumEnding($number, $endingArray=['час', 'часа', 'часов'])
    {
        $number = $number % 100;
        if ($number>=11 && $number<=19) {
            $ending=$endingArray[2];
        }
        else {
            $i = $number % 10;
            switch ($i)
            {
                case (1): $ending = $endingArray[0]; break;
                case (2):
                case (3):
                case (4): $ending = $endingArray[1]; break;
                default: $ending=$endingArray[2];
            }
        }
        return $ending;
    }


}
