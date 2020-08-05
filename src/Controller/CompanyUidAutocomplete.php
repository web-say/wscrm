<?php
namespace Drupal\WsCrm\Controller;

// Указываем зависимости.
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Database\Database;
use Drupal\Component\Utility\Html;
use Drupal\User\Entity\User;

class CompanyUidAutocomplete {

  /**
   * Метод который будет возвращять результаты для автодополнения.
   *
   * {@inheritdoc}
   */
  public function autocomplete(Request $request) {
        // Получаем текущий запрос автокомплита. ($_GET['q'])
    $string = $request->query->get('q');
    // В данном массиве будут результаты для автодополнения, которые будут
    // выданы пользователю при вводе.
    // Каждый результат является массивом состоящим из значения и метки.
    // Значение - то что будет вставлено в поле автозаполнения, метка - то что
    // будет показано в выпадающем списке с возможными автодополнениями для
    // пользователя.
    $matches = [];

    if ($string) {
      
      $config = \Drupal::config('wscrm.settings');
      
      if($config->get('wscrm.role_company')){
        
        foreach($config->get('wscrm.role_company') as $key=>$val)
        {
          $ids = \Drupal::entityQuery('user')
          ->condition('status', 1)
          ->condition('roles', $val)
          ->condition('name', '%' . $string . '%', 'LIKE')
          ->execute();
          
          $users = User::loadMultiple($ids);
          
          foreach ($users as $row) {
      
            $value = Html::escape($row->get('uid')->value);
            $label = Html::escape($row->getDisplayName() . ' (' . $row->get('uid')->value . ')');
            $matches[] = ['value' => $value, 'label' => $label];
          }
          
        }
      }

    }

    return new JsonResponse($matches);
  }

}