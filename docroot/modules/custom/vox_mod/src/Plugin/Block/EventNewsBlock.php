<?php

namespace Drupal\vox_mod\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Event News' Block.
 *
 * @Block(
 *   id = "event_news_block",
 *   admin_label = @Translation("Event News block"),
 *   category = @Translation("Event News"),
 * )
 */
class EventNewsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $latest_events = [];
    $latest_news = [];

    // Get events
    $nids = \Drupal::entityQuery('node')
    ->condition('status', 1)
    ->condition('type', 'event')
    ->sort('field_event_date', 'DESC')
    ->pager(2)
    ->execute();
    $events = \Drupal\node\Entity\Node::loadMultiple($nids);
    
    foreach ($events as $event_item) {
      $nid = $event_item->ID();
      $path_alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'.$nid);
      $latest_events [] = [
        'title' => $event_item->getTitle(),
        'image' => $event_item->field_image->entity->getFileUri(),
        'start_date' => date('d F Y', strtotime($event_item->field_event_date->value)),
        'end_date' => !empty($event_item->field_event_date->end_value) ? ' - ' . date('d F Y', strtotime($event_item->field_event_date->end_value)) : '',
        'body' => $event_item->body->value,
        'link' => '<a href="' . $path_alias . '">Detail</a>'
      ];
    }
    //dump($latest_events);

    // Get latest news
    $news_nids = \Drupal::entityQuery('node')
    ->condition('status', 1)
    ->condition('type', 'news')
    ->sort('field_publish_date', 'DESC')
    ->pager(1)
    ->execute();
    $latestnews = \Drupal\node\Entity\Node::loadMultiple($news_nids);
    
    foreach ($latestnews as $news_item) {
      $news_nid = $news_item->ID();
      $path_alias_news = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'.$news_nid);
      $latest_news [] = [
        'title' => $news_item->getTitle(),
        'image' => $news_item->field_image->entity->getFileUri(),
        'publish_date' => date('d F Y', strtotime($news_item->field_publish_date->value)),
        'body' => $news_item->body->value,
        'link' => '<a href="' . $path_alias_news . '">Detail</a>'
      ];
    }
    dump($latest_news);
    $build = [];
    $build['#theme'] = 'event_news_block';
    $build['#latest_events'] = $latest_events;
    $build['#latest_news'] = $latest_news;

    return $build;
  }

}
