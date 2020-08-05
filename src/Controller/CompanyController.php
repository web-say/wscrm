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
class CompanyController  {

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

    public function company() {

      $element = [];

      $element['#title'] = Html::escape(t('Company'));

      $connection = Database::getConnection();

      $company_arr = wsrcm_company_list('show');
      /*
      $company = $connection->select('wscrm_company', 'cm')
            ->condition('cid',$company_arr, 'IN')
            ->fields('cm', array('cid', 'name', 'contact'));

      // Execute the statement
      $executed = $company->execute();

      // Get all the results
      $results = $executed->fetchAll(\PDO::FETCH_OBJ);
*/
      $data = [];

      $header = ['#', t('Name'), t('Contact')];

      $i = 1;

      // Iterate results
      foreach ($company_arr as $row) {

        $url = Url::fromRoute('wscrm.companyshow',['cid'=>$row->cid]);
        $link_to_company = Link::fromTextAndUrl($row->name, $url);

        $url_edit = Url::fromRoute('wscrm.companyedit',['cid'=>$row->cid]);
        $link_to_company_edit = Link::fromTextAndUrl(t('Edit'), $url_edit);

        $data[] = [$i,$link_to_company,nl2br($row->contact),$link_to_company_edit];

        $i++;

      }

      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
      $_url = Url::fromRoute('wscrm.companycreate', [], ['language' => $language]);
      $userCurrent = \Drupal::currentUser();

      if ($_url->access($userCurrent)) {

        $url_create = Link::createFromRoute(t('New company'), 'wscrm.companycreate')
          ->toString()
          ->getGeneratedLink();

        $element[] = array(
                '#markup' => $url_create
              );
      }
      $element[] = array(
        '#theme' => 'table',
        '#cache' => ['max-age' => 0,],
        '#caption' => t('List company'),
        '#header' => $header,
        '#rows' => $data,
      );

      return $element;

    }


    public function companyshow($cid) {

      $company_arr = wsrcm_company_list();
      $yes = 0;
      foreach($company_arr as $kc=>$vc){
        if($vc == $cid){ $yes = 1;}
      }

      $element = [];

      if(!empty($yes)){

        $conn = Database::getConnection();

        $query = $conn->select('wscrm_company', 'cm')
            ->condition('cid', $cid)
            ->fields('cm');

        $company = $query->execute()->fetchAssoc();

        $element['#title'] = Html::escape($company['name']);

        $content = '';

        if(!empty($company))
        {
          $data = [];

          if(!empty($company['pid']))
          {
              $project_info = wsrcm_project_id($company['pid']);
          }

        $content = '<div class="container-fluid"><div class="row"><div class="col-12"><strong>'.t('Contact company').'</strong>:<br/>'.nl2br($company['contact']).'</p></div></div></div>';
        $content .= '<div class="container-fluid"><div class="row"><div class="col-12"><strong>'.t('Description company').'</strong>:<br/>'.nl2br($company['description']).'</p></div></div></div>';

          $element[] = array(
            '#theme' => 'table',
            '#cache' => ['max-age' => 0,],
            '#header' => [],
            '#rows' => $data,
          );

        }

        $element[]['#markup'] = $content;



        $query = $conn->select('wscrm_company_users', 'ul')
            ->condition('cid', $cid)
            ->fields('ul');

        $ulist = $query->execute()->fetchAll();


        $user_list= '<div class="container-fluid"><div class="row"><div class="col-12"><strong>'.t('Users company').'</strong>:<br/>';
        //print_r($ulist);
        foreach($ulist as $key=>$val){
          //print_r($val);
          $users = \Drupal\user\Entity\User::load($val->uid);

          $user_list.= $users->getDisplayName().' ('.$val->description.') <br/>';
        }

        $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $_url = Url::fromRoute('wscrm.companyusercreate', [], ['language' => $language]);
        $userCurrent = \Drupal::currentUser();

        if ($_url->access($userCurrent)) {

          $url_add_user = Url::fromRoute('wscrm.companyusercreate',array('cid' => $cid));
          $url_add_user_options = array( 'attributes' => array( 'class' => array('btn','btn-primary' ), ),);
          $url_add_user->setOptions($url_add_user_options);
          $url_add_user_link = Link::fromTextAndUrl('+', $url_add_user)->toString();
        }else{
          $url_add_user_link = '';
        }

        $user_list.= '</div>'.$url_add_user_link.'</div></div>';

        $element[]['#markup'] = $user_list;

        return $element;

      } else {

        throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();

      }

    }

}
