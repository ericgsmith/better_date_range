<?php

namespace Drupal\better_date_range;

/**
 * Param object for compact date range formatting.
 */
class DateRangeFormatParams {

  /**
   * Format to use for a single time.
   *
   * @var string
   */
  protected $singleTimeFormat;

  /**
   * Format to use for multiple times on a single day.
   *
   * @var string
   */
  protected $sameDayFormat;

  /**
   * Format to use for the start date when the end date is in the same month.
   *
   * @var string
   */
  protected $sameMonthStartFormat;

  /**
   * Format to use for the start date when the end date is in the same year.
   *
   * @var string
   */
  protected $sameYearStartFormat;

  /**
   * Format to use for the end date.
   *
   * @var string
   */
  protected $endDateFormat;

  /**
   * DateRangeFormatParams constructor.
   *
   * @param string $singleTimeFormat
   *   Format to use for a single time.
   * @param string $sameDayFormat
   *   Format to use for multiple times on a single day.
   * @param string $sameMonthStartFormat
   *   Format to use for the start date when the end date is in the same month.
   * @param string $sameYearEndFormat
   *   Format to use for the start date when the end date is in the same year.
   * @param string $endDateFormat
   *   Format to use for the end date.
   */
  public function __construct($singleTimeFormat, $sameDayFormat, $sameMonthStartFormat, $sameYearEndFormat, $endDateFormat) {
    $this->singleTimeFormat = $singleTimeFormat;
    $this->sameDayFormat = $sameDayFormat;
    $this->sameMonthStartFormat = $sameMonthStartFormat;
    $this->sameYearStartFormat = $sameYearEndFormat;
    $this->endDateFormat = $endDateFormat;
  }

  /**
   * @return string
   */
  public function getSingleTimeFormat() {
    return $this->singleTimeFormat;
  }

  /**
   * @return string
   */
  public function getSameDayFormat() {
    return $this->sameDayFormat;
  }

  /**
   * @return string
   */
  public function getSameMonthStartFormat() {
    return $this->sameMonthStartFormat;
  }

  /**
   * @return string
   */
  public function getSameYearStartFormat() {
    return $this->sameYearStartFormat;
  }

  /**
   * @return string
   */
  public function getEndDateFormat() {
    return $this->endDateFormat;
  }

}
