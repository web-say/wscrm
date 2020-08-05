<?php
namespace Drupal\WsCrm\Controller;

// Указываем зависимости.
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Database\Database;
use Drupal\Component\Utility\Html;

class TaskUidAutocomplete {

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
      // Делаем выборку по всему содержимому типа node, где заголовок похож
      // на введенный в поле автодополнения.
      $query = \Drupal::database()->select('users_field_data', 'u')
        ->fields('u', array('uid', 'name'))
        ->condition('u.name', '%' . $string . '%', 'LIKE')
        ->range(0, 10);
      // Выполняем запрос и получаем результаты.
      $result = $query->execute();

      // Добавляем результаты в массив.
      foreach ($result as $row) {
        $value = Html::escape($row->uid);
        $label = Html::escape($row->name . ' (' . $row->uid . ')');
        $matches[] = ['value' => $value, 'label' => $label];
      }
    }

    return new JsonResponse($matches);
  }

}