<?php
namespace Drupal\google_news_api\Controller;
use Drupal\Core\Controller\ControllerBase;

class GoogleNewsAPIController extends ControllerBase {

  /**
   * @param $news_type
   *
   * @return void
   */
  public function createNews($news_type) {
    $config = [];
    if ($news_type == 'top') {
      $config = $this->config('google_news_api.settings')->get('top_head_settings');
    }
    if ($news_type == 'every') {
      $config = $this->config('google_news_api.settings')->get('everything_settings');
    }
    $node = $this->entityTypeManager()->getStorage('node')->create([

    ]);
  }

}
