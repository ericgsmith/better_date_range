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
 *   label = @Translation("Custom compact date range"),
 *   field_types = {
 *     "daterange"
 *   }
 * )
 */
class CompactDateRangeCustomFormatter extends CompactDateRangeFormatterBase {

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
   */
  protected function getStartFormatField() {
    return [
      '#type' => 'textfield',
      '#title' => t('Start date format'),
      '#description' => $this->getDateFormatHelpText(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getEndFormatField() {
    return [
      '#type' => 'textfield',
      '#title' => t('End date format'),
      '#description' => $this->getDateFormatHelpText(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getFormatterType($format) {
    return 'custom';
  }

  /**
   * {@inheritdoc}
   */
  protected function getFormatterFormat($format) {
    return $format;
  }

  /**
   * Get the help text for the format fields.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   */
  private function getDateFormatHelpText() {
    return $this->t('See <a href="http://php.net/manual/function.date.php" target="_blank">the documentation for PHP date formats</a>.');
  }
}
