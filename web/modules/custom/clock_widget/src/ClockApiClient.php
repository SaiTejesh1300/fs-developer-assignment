<?php

namespace Drupal\clock_widget;

use GuzzleHttp\ClientInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Service for fetching the current time from the World Clock API.
 */
class ClockApiClient {

  /**
   * The HTTP client for making requests.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The logger for recording errors.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a ClockApiClient object.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   */
  public function __construct(ClientInterface $http_client, LoggerChannelFactoryInterface $logger_factory) {
    $this->httpClient = $http_client;
    // Use the logger factory to get a logger for the "clock_widget" channel.
    $this->logger = $logger_factory->get('clock_widget');
  }

  /**
   * Fetches the current time for a given timezone.
   *
   * @param string $timezone
   *   The timezone identifier ('est' or 'utc').
   *
   * @return string|null
   *   The current time in ISO 8601 format, or NULL on failure.
   */
  public function getTime($timezone) {
    try {
      $response = $this->httpClient->get("http://worldclockapi.com/api/json/{$timezone}/now");
      $data = json_decode($response->getBody(), TRUE);

      return $data['currentDateTime'] ?? NULL;
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to fetch time for @timezone: @message', [
        '@timezone' => strtoupper($timezone),
        '@message' => $e->getMessage(),
      ]);
      return NULL;
    }
  }

}
