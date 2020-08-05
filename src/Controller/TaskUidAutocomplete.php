<?php
namespace Drupal\WsCrm\Controller;

// ��������� �����������.
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Database\Database;
use Drupal\Component\Utility\Html;

class TaskUidAutocomplete {

  /**
   * ����� ������� ����� ���������� ���������� ��� ��������������.
   *
   * {@inheritdoc}
   */
  public function autocomplete(Request $request) {
        // �������� ������� ������ ������������. ($_GET['q'])
    $string = $request->query->get('q');
    // � ������ ������� ����� ���������� ��� ��������������, ������� �����
    // ������ ������������ ��� �����.
    // ������ ��������� �������� �������� ��������� �� �������� � �����.
    // �������� - �� ��� ����� ��������� � ���� ��������������, ����� - �� ���
    // ����� �������� � ���������� ������ � ���������� ���������������� ���
    // ������������.
    $matches = [];

    if ($string) {
      // ������ ������� �� ����� ����������� ���� node, ��� ��������� �����
      // �� ��������� � ���� ��������������.
      $query = \Drupal::database()->select('users_field_data', 'u')
        ->fields('u', array('uid', 'name'))
        ->condition('u.name', '%' . $string . '%', 'LIKE')
        ->range(0, 10);
      // ��������� ������ � �������� ����������.
      $result = $query->execute();

      // ��������� ���������� � ������.
      foreach ($result as $row) {
        $value = Html::escape($row->uid);
        $label = Html::escape($row->name . ' (' . $row->uid . ')');
        $matches[] = ['value' => $value, 'label' => $label];
      }
    }

    return new JsonResponse($matches);
  }

}