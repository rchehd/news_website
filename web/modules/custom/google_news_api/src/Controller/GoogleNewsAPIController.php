<?php
namespace Drupal\google_news_api\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\FileSystemInterface;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\pathauto\PathautoState;

class GoogleNewsAPIController extends ControllerBase {

  /**
   * @param $news_type
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createNews($type, $google_news_id) {
    $tempstore = \Drupal::service('tempstore.private')->get($type);
    $articles = $tempstore->get('data2');
    $article = $articles[$google_news_id];

    $node = Node::create(['type' => 'top_headline_news']);
    $node->setTitle($article['title']);
    $node->set('field_news_type', $type);
    $node->set('field_source', [
      'uri'=> $article['url'],
      'title' => $article['source']['name']
    ]);

    $data = file_get_contents($article['urlToImage']);
    $regex = preg_match('/[\w-]+\.(jpg|png|jpeg)/', $article['urlToImage'], $matches);
    if ($matches[0]) {
      $file = \Drupal::service('file.repository')->writeData($data, 'public://' . $matches[0], FileSystemInterface::EXISTS_REPLACE);
      $image_media = Media::create([
        'name' => $article['title'],
        'bundle' => 'image',
        'status' => 1,
        'field_media_image' => [
          'target_id' => $file->id(),
          'alt' => $article['title'],
          'title' => $article['title'],
        ],
      ]);
      $image_media->save();
      $node->set('field_image_banner',  [
        'target_id' => $image_media->id(),
      ]);
    }

    $content_paragraph = Paragraph::create([
      'type' => 'text_with_image',
      'field_text' => [
        'summary' => $article['description'],
        'value' => $article['content'],
        'format' => 'full_html',
      ]
    ]);
    $content_paragraph->save();

    $category_and_tag_paragraph = Paragraph::create([
      'type' => 'category_and_tag',
    ]);
    $category_and_tag_paragraph->save();



    $node->set('field_content_paragraph',  [
      [
        'target_id' => $content_paragraph->id(),
        'target_revision_id' => $content_paragraph->getRevisionId(),
      ]
    ]);

    $node->save();
    return $this->redirect('entity.node.edit_form', ['node' => $node->id()]);
  }

}
