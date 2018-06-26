<?php

namespace Drupal\better_date_format\Plugin\Field\FieldFormatter;

use Drupal\better_date_format\DateRangeFormatParams;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime_range\Plugin\Field\FieldFormatter\DateRangeDefaultFormatter;

/**
 * Plugin implementation of range or single formatter for 'daterange' fields.
 *
 * This formatter renders the data range using <time> elements, with
 * a date format for single and multiple events.
 *
 * @FieldFormatter(
 *   id = "daterange_singlefallback",
 *   label = @Translation("Single date fallback"),
 *   field_types = {
 *     "daterange"
 *   }
 * )
 */
class DateRangeSingleFallbackFormatter extends DateRangeDefaultFormatter {

  // Settings.
  const DATE_FORMAT_DAY_MONTH_YEAR_TIME = 'j M o, G:i';
  const DATE_FORMAT_DAY_MONTH_YEAR = 'j M o';
  const DATE_FORMAT_DAY_MONTH = 'j M';
  const DATE_FORMAT_DAY = 'j';
  const DATE_FORMAT_ISO_TIME = "Y-m-d\TH:i:s";
  const SETTING_SEPARATOR = 'separator';
  const SETTING_FORMAT_TYPE = 'format_type';
  const SETTING_TIMEZONE_OVERRIDE = 'timezone_override';

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = parent::defaultSettings();
    unset($settings[self::SETTING_FORMAT_TYPE]);
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    unset($form[self::SETTING_FORMAT_TYPE]);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    if ($separator = $this->getSetting(self::SETTING_SEPARATOR)) {
      $summary[] = $this->t('Separator: %separator', ['%separator' => $separator]);
    }
    if ($override = $this->getSetting(self::SETTING_TIMEZONE_OVERRIDE)) {
      $summary[] = $this->t('Time zone: @timezone', ['@timezone' => $override]);
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      if (!empty($item->start_date) && !empty($item->end_date)) {
        /** @var \Drupal\Core\Datetime\DrupalDateTime $start_date */
        $start_date = $item->start_date;
        /** @var \Drupal\Core\Datetime\DrupalDateTime $end_date */
        $end_date = $item->end_date;

        $formatParams = new DateRangeFormatParams(
          self::DATE_FORMAT_DAY_MONTH_YEAR_TIME,
          self::DATE_FORMAT_DAY_MONTH_YEAR,
          self::DATE_FORMAT_DAY,
          self::DATE_FORMAT_DAY_MONTH,
          self::DATE_FORMAT_DAY_MONTH_YEAR
        );

        /** @var \Drupal\better_date_format\FormattedDateRangeParams $formattedDates */
        $formattedDates = \Drupal::service('better_date_format.formatter')->formatDateRange($start_date, $end_date, $formatParams, $this->getSetting(self::SETTING_TIMEZONE_OVERRIDE));

        if ($formattedDates->getEndDate()) {
          $elements[$delta] = [
            'start_date' => $this->getDateTimeBuild($start_date, $formattedDates->getStartDate()),
            'separator' => ['#markup' => $this->getSetting(self::SETTING_SEPARATOR)],
            'end_date' => $this->getDateTimeBuild($end_date, $formattedDates->getEndDate()),
          ];
        }
        else {
          $elements[$delta] = [$this->getDateTimeBuild($start_date, $formattedDates->getStartDate())];
        }

        if (!empty($item->_attributes)) {
          $elements[$delta]['#attributes'] += $item->_attributes;
          // Unset field item attributes since they have been included in the
          // formatter output and should not be rendered in the field template.
          unset($item->_attributes);
        }
      }
    }

    return $elements;
  }

  /**
   * Get the build array for the date time object.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $date
   *   Date object.
   * @param string $formattedDate
   *   Formatted date string.
   *
   * @return array
   *   Render array.
   */
  protected function getDateTimeBuild(DrupalDateTime $date, $formattedDate) {
    // Create the ISO date in Universal Time.
    $iso_date = $date->format(self::DATE_FORMAT_ISO_TIME) . 'Z';
    $this->setTimeZone($date);
    $build = [
      '#theme' => 'time',
      '#text' => $formattedDate,
      '#html' => FALSE,
      '#attributes' => [
        'datetime' => $iso_date,
      ],
    ];
    return $build;
  }

}
