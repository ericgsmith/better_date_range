<?php

namespace Drupal\better_date_range;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Service to format date ranges.
 */
class DateComparison implements DateComparisonInterface {

  use StringTranslationTrait;

  // Comparison types.
  const COMPARISON_SAME_TIME = 'exact';
  const COMPARISON_SAME_HOUR = 'same_hour';
  const COMPARISON_SAME_DAY = 'same_day';
  const COMPARISON_SAME_MONTH = 'same_month';
  const COMPARISON_SAME_YEAR = 'same_year';
  const COMPARISON_FALLBACK = 'fallback';

  // Date formats used when comparing dates.
  const PHP_DATE_FORMAT_Y_M = 'y-m';
  const PHP_DATE_FORMAT_Y = 'y';
  const PHP_DATE_FORMAT_Y_M_D = 'y-m-d';
  const PHP_DATE_FORMAT_Y_M_D_H = 'y-m-d-H';

  /**
   * List of comparison methods and their code.
   *
   * @var array
   */
  protected $comparisonMethodResultMap = [
    'isSameTime' => self::COMPARISON_SAME_TIME,
    'isSameHour' => self::COMPARISON_SAME_HOUR,
    'isSameDay' => self::COMPARISON_SAME_DAY,
    'isSameMonth' => self::COMPARISON_SAME_MONTH,
    'isSameYear' => self::COMPARISON_SAME_YEAR,
  ];

  /**
   * (@inheritdoc)
   */
  public function compareDates(DrupalDateTime $startDate, DrupalDateTime $endDate) {
    foreach ($this->comparisonMethodResultMap as $method => $code) {
      if ($this->$method($startDate, $endDate)) {
        return $code;
      }
    }

    return self::COMPARISON_FALLBACK;
  }

  /**
   * (@inheritdoc)
   */
  public function getComparisonTypes() {
    return  [
      self::COMPARISON_SAME_TIME,
      self::COMPARISON_SAME_HOUR,
      self::COMPARISON_SAME_DAY,
      self::COMPARISON_SAME_MONTH,
      self::COMPARISON_SAME_YEAR,
      self::COMPARISON_FALLBACK,
    ];
  }

  /**
   * (@inheritdoc)
   */
  public function getComparisonTypeLabel($comparisonType) {
    $comparisonLabels = [
      self::COMPARISON_SAME_TIME => $this->t('Dates are an exact match'),
      self::COMPARISON_SAME_HOUR => $this->t('Dates occur in the same hour'),
      self::COMPARISON_SAME_DAY => $this->t('Dates occur in the same day'),
      self::COMPARISON_SAME_MONTH => $this->t('Dates occur in the same month'),
      self::COMPARISON_SAME_YEAR => $this->t('Dates occur in the same year'),
      self::COMPARISON_FALLBACK => $this->t('No match (fallback)'),
    ];
    return $comparisonLabels[$comparisonType];
  }

  /**
   * Check if the start and end dates are the same.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $startDate
   *   Start date.
   * @param \Drupal\Core\Datetime\DrupalDateTime $endDate
   *   End date.
   *
   * @return bool
   *   TRUE if they are the same time.
   */
  protected function isSameTime(DrupalDateTime $startDate, DrupalDateTime $endDate) {
    return $startDate->getTimestamp() === $endDate->getTimestamp();
  }

  /**
   * Chick if the start and end dates are in the same hour.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $startDate
   *   Start date.
   * @param \Drupal\Core\Datetime\DrupalDateTime $endDate
   *   End date.
   *
   * @return bool
   *   TRUE if they are on the same day.
   */
  protected function isSameHour(DrupalDateTime $startDate, DrupalDateTime $endDate) {
    return $startDate->format(self::PHP_DATE_FORMAT_Y_M_D_H) === $endDate->format(self::PHP_DATE_FORMAT_Y_M_D_H);
  }

  /**
   * Chick if the start and end dates are on the same day.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $startDate
   *   Start date.
   * @param \Drupal\Core\Datetime\DrupalDateTime $endDate
   *   End date.
   *
   * @return bool
   *   TRUE if they are on the same day.
   */
  protected function isSameDay(DrupalDateTime $startDate, DrupalDateTime $endDate) {
    return $startDate->format(self::PHP_DATE_FORMAT_Y_M_D) === $endDate->format(self::PHP_DATE_FORMAT_Y_M_D);
  }

  /**
   * Check if the start and end dates are on the same month.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $startDate
   *   Start date.
   * @param \Drupal\Core\Datetime\DrupalDateTime $endDate
   *   End date.
   *
   * @return bool
   *   TRUE if they are on the same month.
   */
  protected function isSameMonth(DrupalDateTime $startDate, DrupalDateTime $endDate) {
    return $startDate->format(self::PHP_DATE_FORMAT_Y_M) === $endDate->format(self::PHP_DATE_FORMAT_Y_M);
  }

  /**
   * Check if the start and end dates are on the same year.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $startDate
   *   Start date.
   * @param \Drupal\Core\Datetime\DrupalDateTime $endDate
   *   End date.
   *
   * @return bool
   *   TRUE if they are on the same year.
   */
  protected function isSameYear(DrupalDateTime $startDate, DrupalDateTime $endDate) {
    return $startDate->format(self::PHP_DATE_FORMAT_Y) === $endDate->format(self::PHP_DATE_FORMAT_Y);
  }

}
