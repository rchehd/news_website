<?php

namespace Drupal\news_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;

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
    $entity_ids = $query
      ->condition('type', 'top_headline_news', '=')
      ->condition('field_news_type', 'every', '=')
      ->range(0, 8)
      ->execute();

    $entity_type_manager = \Drupal::entityTypeManager();
    $node_view_builder   = $entity_type_manager->getViewBuilder('node');
    $i = 0;
    foreach ($entity_ids as $id) {
      $node = $entity_type_manager->getStorage('node')->load($id);
      if ($i == 0) {
        $list[$i] = $node_view_builder->view($node, 'teaser');
      }
      else {
        $list[$i] = $node_view_builder->view($node, 'sub_teaser');
      }
      $i++;
    }

    return [
      '#theme'    => 'top_headlines',
      '#list'     => $list,
      '#attached' => [
        'library' => ['news_core/everything'],
      ],
    ];
  }

}
