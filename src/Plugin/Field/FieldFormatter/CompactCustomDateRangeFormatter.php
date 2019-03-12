<?php

namespace Drupal\better_date_range\Plugin\Field\FieldFormatter;

use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

/**
 * Plugin implementation of compact range or single formatter for 'daterange' fields.
 *
 * This formatter renders the data range using <time> elements, with
 * a date format for single and multiple dates.
 *
 * @FieldFormatter(
 *   id = "daterange_custom_compact",
 *   label = @Translation("Compact date range custom format"),
 *   field_types = {
 *     "daterange"
 *   }
 * )
 */
class CompactCustomDateRangeFormatter extends CompactDateRangeFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = parent::defaultSettings();
    unset($settings[self::SETTING_FORMAT_TYPE]);

    $types = \Drupal::service('better_date_range.date_comparison')->getComparisonTypes();

    foreach ($types as $key) {
      $settings[$key][self::SETTING_START_FORMAT] = DateTimeItemInterface::DATETIME_STORAGE_FORMAT;
      $settings[$key][self::SETTING_END_FORMAT] = DateTimeItemInterface::DATETIME_STORAGE_FORMAT;
      $settings[$key][self::SETTING_SEPARATOR] = '-';
    }

    return $settings;
  }

  /**
   * {@inheritdoc}
   *
   * @param string $custom_format
   */
  protected function formatDate($date, $custom_format = DateTimeItemInterface::DATE_STORAGE_FORMAT) {
    /** @var \Drupal\Core\Datetime\DrupalDateTime $date */
    $timezone = $this->getSetting(self::SETTING_TIMEZONE_OVERRIDE) ?: $date->getTimezone()->getName();
    return $this->dateFormatter->format($date->getTimestamp(), 'custom', $custom_format, $timezone !== '' ? $timezone : NULL);
  }


  /**
   * @param $options
   *
   * @return array
   */
  protected function getStartFormatField($options) {
    return [
      '#type' => 'textfield',
      '#title' => t('Start date format'),
      '#description' => $this->t('See <a href="http://php.net/manual/function.date.php" target="_blank">the documentation for PHP date formats</a>.'),
    ];
  }

  /**
   * @param $options
   *
   * @return array
   */
  protected function getEndFormatField($options) {
    return [
      '#type' => 'textfield',
      '#title' => t('End date format'),
      '#description' => $this->t('See <a href="http://php.net/manual/function.date.php" target="_blank">the documentation for PHP date formats</a>.'),
    ];
  }
}
