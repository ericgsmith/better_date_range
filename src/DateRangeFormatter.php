<?php

namespace Drupal\better_date_range;

use Drupal\better_date_range\DateRangeFormatParams;
use Drupal\better_date_range\FormattedDateRangeParams;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Service to format date ranges.
 */
class DateRangeFormatter {

  const DATE_FORMAT_CUSTOM = 'custom';
  const PHP_DATE_FORMAT_Y_M = 'y-m';
  const PHP_DATE_FORMAT_Y = 'y';
  const PHP_DATE_FORMAT_Y_M_D = 'y-m-d';

  /**
   * Drupal's date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * DateRangeFormatter constructor.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   Drupal's date formatter service.
   */
  public function __construct(DateFormatterInterface $dateFormatter) {
    $this->dateFormatter = $dateFormatter;
  }

  /**
   * Get a formatted date range.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $startDate
   *   Start date of range.
   * @param \Drupal\Core\Datetime\DrupalDateTime $endDate
   *   End date of range.
   * @param \Drupal\better_date_range\DateRangeFormatParams $dateRangeFormats
   *   Param object of the various formats to use.
   * @param string $timezone
   *   Timezone to use when displaying the date.
   *
   * @return \Drupal\better_date_range\FormattedDateRangeParams
   *   Param object of the formatted dates.
   */
  public function formatDateRange(DrupalDateTime $startDate, DrupalDateTime $endDate, DateRangeFormatParams $dateRangeFormats, $timezone) {
    if ($this->isSameTime($startDate, $endDate)) {
      return $this->getFormattedDateParams(
        $this->formatDate($startDate, $dateRangeFormats->getSingleTimeFormat(), $timezone),
        ''
      );
    }

    if ($this->isSameDay($startDate, $endDate)) {
      return $this->getFormattedDateParams(
        $this->formatDate($startDate, $dateRangeFormats->getSameDayFormat(), $timezone),
        ''
      );
    }

    if ($this->isSameMonth($startDate, $endDate)) {
      return $this->getFormattedDateParams(
        $this->formatDate($startDate, $dateRangeFormats->getSameMonthStartFormat(), $timezone),
        $this->formatDate($endDate, $dateRangeFormats->getEndDateFormat(), $timezone)
      );
    }

    if ($this->isSameYear($startDate, $endDate)) {
      return $this->getFormattedDateParams(
        $this->formatDate($startDate, $dateRangeFormats->getSameYearStartFormat(), $timezone),
        $this->formatDate($endDate, $dateRangeFormats->getEndDateFormat(), $timezone)
      );
    }

    return $this->getFormattedDateParams(
      $this->formatDate($startDate, $dateRangeFormats->getEndDateFormat(), $timezone),
      $this->formatDate($endDate, $dateRangeFormats->getEndDateFormat(), $timezone)
    );
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

  /**
   * Get a formatted date.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $date
   *   Date to format.
   * @param string $format
   *   PHP date format.
   * @param string $timezone
   *   Timezone.
   *
   * @return string
   *   Formatted date.
   */
  protected function formatDate(DrupalDateTime $date, $format, $timezone) {
    return $this->dateFormatter->format($date->getTimestamp(), self::DATE_FORMAT_CUSTOM, $format, $timezone);
  }

  /**
   * Get the formatted date param object for the formatted strings.
   *
   * @param string $startDate
   *   Formatted start date.
   * @param string $endDate
   *   Formatted end date.
   *
   * @return \Drupal\better_date_range\FormattedDateRangeParams
   *   Formatted date object.
   */
  protected function getFormattedDateParams($startDate, $endDate) {
    return new FormattedDateRangeParams($startDate, $endDate);
  }

}
