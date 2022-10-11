<?php

namespace Drupal\news_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\taxonomy\Entity\Term;

/**
 * Provides a Top Headlines Block.
 *
 * @Block(
 *   id = "everything_news_block",
 *   admin_label = @Translation("Everything News Block"),
 *   category = @Translation("Content block"),
 * )
 */
class EverythingNewsBlock extends BlockBase {

  public function build(): array {
    $query = \Drupal::entityQuery('node');
    $cat = [];
    $list = [];
    $terms = $this->getTerms('news_category');
    foreach ($terms as $term) {
      $entity_type_manager = \Drupal::entityTypeManager();
      $entity_ids = $query
        ->condition('type', 'top_headline_news', '=')
        ->condition('field_news_type', 'every', '=')
        ->sort('created' , 'DESC')
        ->execute();

     $term_view_builder = $entity_type_manager->getViewBuilder('taxonomy_term');
      $cat[] = [
        'id' => $term->id(),
        'taxonomy_term' => $term_view_builder->view($term),
      ];
      $node_view_builder = $entity_type_manager->getViewBuilder('node');
      $i = 0;
      foreach ($entity_ids as $id) {
        $node = $entity_type_manager->getStorage('node')->load($id);
        $category = $node->get('field_category')->referencedEntities()[0];
        if ($category->id() == $term->id()) {
          $list[$term->id()][] = $node_view_builder->view($node, 'standart');
          $i++;
          if ($i > 3) {
            break;
          }
        }
      }
    }
    return [
      '#theme'    => 'everything',
      '#list'     => $list,
      '#categories' => $cat,
      '#attached' => [
        'library' => ['news_core/everything'],
      ],
    ];
  }

  /**
   * Get term list
   *
   * @param $vocabulary
   *
   * @return array
   */
  private function getTerms($vocabulary): array {
    $query = \Drupal::entityQuery('taxonomy_term');
    $tids = $query
      ->condition('vid', $vocabulary)
      ->sort('weight')
      ->execute();
    $terms = Term::loadMultiple($tids);
    return $terms;
  }

}
