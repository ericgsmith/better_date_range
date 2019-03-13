<?php

namespace Drupal\better_date_range\Plugin\Field\FieldFormatter;

use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Plugin implementation of compact range or single formatter for 'daterange' fields.
 *
 * This formatter renders the data range using <time> elements, with
 * a date format for single and multiple dates.
 *
 * @FieldFormatter(
 *   id = "daterange_compact",
 *   label = @Translation("Compact date range"),
 *   field_types = {
 *     "daterange"
 *   }
 * )
 */
class CompactDateRangeFormatter extends AbstractCompactDateRangeFormatter {

  /**
   * List of stored date formats.
   *
   * Keyed by format id, value is the label.
   *
   * @var string[]
   */
  private $options = [];

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = parent::defaultSettings();
    unset($settings[self::SETTING_FORMAT_TYPE]);

    $types = \Drupal::service('better_date_range.date_comparison')->getComparisonTypes();

    foreach ($types as $key) {
      $settings[$key][self::SETTING_START_FORMAT] = 'medium';
      $settings[$key][self::SETTING_END_FORMAT] = 'medium';
      $settings[$key][self::SETTING_SEPARATOR] = '-';
    }

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  protected function getStartFormatField() {
    return [
      '#type' => 'select',
      '#title' => t('Start date format'),
      '#options' => $this->getFormatOptions(),
      '#empty_option' => $this->t('Hidden'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getEndFormatField() {
    return [
      '#type' => 'select',
      '#title' => t('End date format'),
      '#options' => $this->getFormatOptions(),
      '#empty_option' => $this->t('Hidden'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getFormatterType($format) {
    // This formatter type uses a date format for its config.
    return $format;
  }

  /**
   * {@inheritdoc}
   */
  protected function getFormatterFormat($format) {
    return '';
  }

  /**
   * Gets the list of format types.
   *
   * @return array
   *   List of stored date formats.
   */
  private function getFormatOptions() {
    if (empty($this->options)) {
      $time = new DrupalDateTime();
      $format_types = $this->dateFormatStorage->loadMultiple();
      foreach ($format_types as $type => $type_info) {
        $format = $this->dateFormatter->format($time->getTimestamp(), $type);
        $this->options[$type] = $type_info->label() . ' (' . $format . ')';
      }
    }

    return $this->options;
  }
}
