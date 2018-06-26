<?php

namespace Drupal\better_date_range;

/**
 * Param object for formatted date strings.
 */
class FormattedDateRangeParams {

  /**
   * Get the formatted start date.
   *
   * @var string
   */
  protected $startDate;

  /**
   * Get the formatted end date.
   *
   * @var string
   */
  protected $endDate;

  /**
   * FormattedDateRangeParams constructor.
   *
   * @param string $startDate
   *   Formatted start date.
   * @param string $endDate
   *   Formatted end date.
   */
  public function __construct($startDate, $endDate) {
    $this->startDate = $startDate;
    $this->endDate = $endDate;
  }

  /**
   * @return string
   */
  public function getStartDate() {
    return $this->startDate;
  }

  /**
   * @return string
   */
  public function getEndDate() {
    return $this->endDate;
  }

}
