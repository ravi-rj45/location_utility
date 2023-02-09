<?php

namespace Drupal\site_location\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\site_location\CurrentTime;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block for showing stie location.
 *
 * @Block(
 *     id = "site_location_block",
 *     admin_label = @Translation("Site Location Block")
 * )
 */
class SiteLocationBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The config factory that knows what is overwritten.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The site location service.
   *
   * @var \Drupal\site_location\CurrentTime
   */
  protected $siteLocationTime;

  /**
   * Constructs a new SiteLocationBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\site_location\CurrentTime $site_location_time
   *   The site location time service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory,
    DateFormatterInterface $date_formatter,
    CurrentTime $site_location_time
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->dateFormatter = $date_formatter;
    $this->siteLocationTime = $site_location_time;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('date.formatter'),
      $container->get('site_location.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->configFactory->get('site_location.settings');
    $current_date = $this->siteLocationTime->getCurrentDateTime();
    // Remove extra chars from date so we can convert it timestamp.
    $current_date = str_replace(['st', 'nd', 'rd', 'th', ' -'], '', $current_date);
    $current_timestamp = strtotime($current_date);
    $city = $config->get('city') ? $config->get('city') : 'New Delhi, Delhi';
    $country = $config->get('country') ? $config->get('country') : 'India';
    return [
      '#theme' => 'site_location',
      '#time' => date('h:i a', $current_timestamp),
      '#date' => date('l, d F Y', $current_timestamp),
      '#location' => $city . ', ' . $country,
      '#cache' => [
        'tags' => $config->getCacheTags(),
        'contexts' => [
          'url',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 60;
  }

}
