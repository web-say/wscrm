<?php
namespace Drupal\WsCrm\Controller;

use Drupal\Core\Url;
use Drupal\Core\Link;

use Drupal\Component\Utility\Html;
use Drupal\Core\Database\Database;

use Drupal\Core\Form\ConfigFormBase;

// use Html instead SAfeMarkup

/**
 * Controller routines for Lorem ipsum pages.
 */
class ProjectController  {

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

    public function projects() {

      $element['#title'] = Html::escape(t('CRM Projects list'));
      
      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
      $_url = Url::fromRoute('wscrm.projectcreate', [], ['language' => $language]);
      $userCurrent = \Drupal::currentUser();
      
      if ($_url->access($userCurrent)) {
        
        $url_create = Link::createFromRoute(t('New project'), 'wscrm.projectcreate')
          ->toString()
          ->getGeneratedLink();
  
        $element[] = array(
                '#markup' => $url_create
              );
      }
      
      $company_arr = wsrcm_company_list();
      
      $connection = Database::getConnection();

      $company = $connection->select('wscrm_company', 'cm')
            ->condition('cid',$company_arr, 'IN')
            ->fields('cm', array('cid', 'name'));

      // Execute the statement
      $executed_company = $company->execute();

      // Get all the results
      $results_company = $executed_company->fetchAll(\PDO::FETCH_OBJ);

      foreach ($results_company as $row_com)
      {
        $element[] = array(
                '#cache' => ['max-age' => 0,],
                '#markup' => '<h5 class="crm-project-h5">'.$row_com->name.'</h5>',
              );

        $projects = $connection->select('wscrm_projects', 'pr')
              ->fields('pr', array('pid','name', 'site_url', 'contact'))
              ->condition('company', $row_com->cid);;

        // Execute the statement
        $executed = $projects->execute();

        // Get all the results
        $results = $executed->fetchAll(\PDO::FETCH_OBJ);

        $data = [];

        $header = ['#',t('Project'), t('Site'), t('Contact')];

        $i = 1;

        // Iterate results
        foreach ($results as $row) {

          $link_to_site = Link::fromTextAndUrl($row->site_url, Url::fromUri($row->site_url, ['attributes' => ['class' => ['link'],'target' => '_blank'] ] ))->toString();

          $url = Url::fromRoute('wscrm.projectshow',['pid'=>$row->pid]);
          $link_to_project = Link::fromTextAndUrl($row->name, $url);
          $link_to_show = Link::fromTextAndUrl(t('Details'), $url);

          $url_edit = Url::fromRoute('wscrm.projectedit',['pid'=>$row->pid]);
          $link_to_project_edit = Link::fromTextAndUrl(t('Edit'), $url_edit);

          $data[] = [$i,$link_to_project,$link_to_site,$row->contact,$link_to_show,$link_to_project_edit];

          $i++;

        }

       $element[] = array(
          '#theme' => 'table',
          '#cache' => ['max-age' => 0,],
          //'#caption' => t('List projects'),
          '#header' => $header,
          '#rows' => $data,
        );
      }

      return $element;

    }



    public function projectshow($pid=0) {
      
      $projects = wsrcm_project_list();
      $yes = 0;
      if(!empty($projects)){
        foreach($projects as $kp=>$vp){
          if($vp == $pid){ $yes = 1;}
        }
      }
      
      if($yes == 1 && !empty($pid)) 
      {
        
        $connection = Database::getConnection();
  
        $query = $connection->select('wscrm_projects', 'pr')
            ->condition('pid', $pid)
            ->fields('pr');
  
        $project = $query->execute()->fetchAssoc();
  
        $content = '';

        if(!empty($project))
        {
          // Default settings.
  
        //  $config = \Drupal::config('wscrm.settings');
  
          $element['#title'] = Html::escape(t('Project').': '.$project['name']);
          
          if(!empty($project['site_url'])){
            
            $link_to_site = Link::fromTextAndUrl($project['site_url'], Url::fromUri($project['site_url'], ['attributes' => ['class' => ['link'],'target' => '_blank'] ] ))->toString();
  
            $content .= '<h4>'.t('Project site').': '.$link_to_site.'</h4>'; //'<a href="'.$project['site_url'].'" target="_blank">'.$project['site_url'].'</a>';
          }
          
          $accesses = '';
  
          $information = '--';
  
          if(!empty($project['description']))
          {
            $information .= '<p><strong>'.t('Description').'</strong>:<br/>'.nl2br($project['description']).'</p>';
          }
  
          if(!empty($project['contact']))
          {
            $information .= '<p><strong>'.t('Contact').'</strong>:<br/>'.nl2br($project['contact']).'</p>';
          }
  
          if(!empty($project['type']))
          {
            /*
            if($config->get('wscrm.type.'.$project['type'])){
              $information .= '<p><strong>'.t('Type').'</strong>: '.t($config->get('wscrm.type.'.$project['type'])).'</p>';
            }else{
  */
              $information .= '<p><strong>'.t('Type').'</strong>: '.$project['type'].'</p>';
    //        }
          }
          
          if(!empty($project['domain_url']) && (!empty($project['domain_login']) || !empty($project['domain_pass'])))
          {
            $link_to_domain = Link::fromTextAndUrl($project['domain_url'], Url::fromUri($project['domain_url'], ['attributes' => ['class' => ['link'],'target' => '_blank'] ] ))->toString();
            
            $accesses .= '<p><strong>Domain </strong>:<br/>'.t('server').': '.$link_to_domain.'<br/>';
            $accesses .= ''.t('login').': '.$project['domain_login'].'<br/>';
            $accesses .= ''.t('password').': '.$project['domain_pass'].'<br/></p>';
          }
  
          if(!empty($project['ftp_url']) || !empty($project['ftp_login']) || !empty($project['ftp_pass']))
          {
            $accesses .= '<p><strong>FTP</strong>:<br/>'.t('server').': '.$project['ftp_url'].'<br/>';
            $accesses .= ''.t('login').': '.$project['ftp_login'].'<br/>';
            $accesses .= ''.t('password').': '.$project['ftp_pass'].'<br/></p>';
          }
  
          if(!empty($project['site_admin']) && (!empty($project['site_login']) || !empty($project['site_pass'])))
          {
            $link_to_admin = Link::fromTextAndUrl($project['site_admin'], Url::fromUri($project['site_admin'], ['attributes' => ['class' => ['link'],'target' => '_blank'] ] ))->toString();
            $accesses .= '<p><strong>'.t('Admin page').'</strong>:<br/>'.t('Link').': '.$link_to_admin.'<br/>';
            $accesses .= ''.t('User').': '.$project['site_login'].'<br/>';
            $accesses .= ''.t('Password').': '.$project['site_pass'].'<br/></p>';
          }
  
          if(!empty($project['host_url']) || !empty($project['host_login']) || !empty($project['host_pass']))
          {
              $pos = strpos($project['host_url'], 'http://');
              if ($pos === false) {
                 $project['host_url'] = 'http://'.$project['host_url'];
              } 
              
            
            $link_to_host = Link::fromTextAndUrl($project['host_url'], Url::fromUri($project['host_url'], ['attributes' => ['class' => ['link'],'target' => '_blank'] ] ))->toString();
            $accesses .= '<p><strong>'.t('Hosting panel').':</strong><br/>'.t('Link').': '.$link_to_host.'<br/>';
            $accesses .= ''.t('User').': '.$project['host_login'].'<br/>';
            $accesses .= ''.t('Password').': '.$project['host_pass'].'<br/></p>';
          }
  
          $content .= '<div id="tasktabs">
                        <ul class="nav nav-tabs" role="tablist">
                          <li role="presentation" class="active"><a href="#accesses" aria-controls="accesses" role="tab" data-toggle="tab">'.t('Accesses project').'</a></li>
                          <li role="presentation"><a href="#information" aria-controls="information" role="tab" data-toggle="tab">'.t('Project information').'</a></li>
                        </ul>';
          $content .= ' <div class="tab-content">
                          <div role="tabpanel" class="tab-pane active" id="accesses">'.$accesses.'</div>
                          <div role="tabpanel" class="tab-pane" id="information">'.$information.'</div>
                          </div>
                      </div>';
        }
      $element['#cache'] = ['max-age' => 0,];
      $element['#markup'] = $content;

      return $element;
      }
      else{
        throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
      }
    }

}
