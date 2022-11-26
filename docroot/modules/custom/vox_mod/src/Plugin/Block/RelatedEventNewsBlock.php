<?php

namespace Drupal\vox_mod\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Language\LanguageInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

/**
 * Provides a 'Event News' Block.
 *
 * @Block(
 *   id = "related_event_news_block",
 *   admin_label = @Translation("Related Event News block"),
 *   category = @Translation("Related Event News"),
 * )
 */
class RelatedEventNewsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      $nid = $node->id();
      $node_type = $node->bundle();
      $cat_name = 'field_' . $node_type . '_category';
      $categories = $node->get($cat_name)->referencedEntities();
      $tids = [];
      foreach ($categories as $category) {
        $tids [] = $category->id();
      }
    }
    //dump($tids);
    $related_nodes = [];
    
    $storage = \Drupal::service('entity_type.manager')->getStorage('node');
    $query = $storage->getQuery()
      ->condition('type', $node_type)
      ->condition('status', 1)
      ->condition($cat_name, $tids, 'IN')
      ->condition('nid', $nid, '<>')
      ->sort('created', 'DESC');

    $ids = $query->execute();
    $thenodes = \Drupal\node\Entity\Node::loadMultiple($ids);
    
    foreach ($thenodes as $event_item) {
      $nid = $event_item->ID();
      $path_alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'.$nid);
      $related_nodes [] = [
        'title' => $event_item->getTitle(),
        'image' => !empty($event_item->field_image->entity) ? $event_item->field_image->entity->getFileUri() : '',
        'start_date' => isset($event_item->field_event_date->value) ? date('d F Y', strtotime($event_item->field_event_date->value)) : '',
        'end_date' => isset($event_item->field_event_date->end_value) ? ' - ' . date('d F Y', strtotime($event_item->field_event_date->end_value)) : '',
        'publish_date' => isset($event_item->field_publish_date->value) ? date('d F Y', strtotime($event_item->field_publish_date->value)) : '',
        'link' => '<a href="' . $path_alias . '">Detail</a>'
      ];
    }
    
    $build = [];
    $build['#theme'] = 'related_event_news_block';
    $build['#thetitle'] = 'Related ' . $node_type;
    $build['#related_nodes'] = $related_nodes;

    return $build;
  }

}
