<?php

namespace Drupal\clock_widget\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\clock_widget\ClockApiClient;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides a 'Clock Widget' block.
 *
 * @Block(
 *   id = "clock_widget_block",
 *   admin_label = @Translation("Clock Widget"),
 * )
 */
class ClockWidgetBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The Clock API Client service.
   *
   * @var \Drupal\clock_widget\ClockApiClient
   */
  protected $clockApiClient;

  /**
   * Constructs a ClockWidgetBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\clock_widget\ClockApiClient $clock_api_client
   *   The Clock API client service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ClockApiClient $clock_api_client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->clockApiClient = $clock_api_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('clock_widget.api_client')
    );
  }

  /**
   * Block form to configure time zones.
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['timezones'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Select time zones to display'),
      '#description' => $this->t('Choose the time zones to display in the clock widget.'),
      '#options' => [
        'est' => $this->t('Eastern Standard Time (EST)'),
        'utc' => $this->t('Coordinated Universal Time (UTC)'),
      ],
      '#default_value' => $this->configuration['timezones'] ?? ['est', 'utc'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // Save selected timezones, filtering out any unselected checkboxes (empty values).
    $this->configuration['timezones'] = array_filter($form_state->getValue('timezones'));
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $output = [];
    $selected_timezones = $this->configuration['timezones'] ?? ['est', 'utc'];
  
    foreach ($selected_timezones as $timezone) {
      $time = $this->clockApiClient->getTime($timezone);
      $output[] = [
        '#theme' => 'clock_widget',
        '#timezone' => strtoupper($timezone),
        '#time' => $time ?? $this->t('Failed to load time'),
        '#attached' => [
          'library' => [
            'clock_theme/clock_widget_scripts',
          ],
        ],
      ];
    }
    //var_dump($output);
    return $output;
  }
  

}
