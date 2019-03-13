<?php

namespace Drupal\better_date_range;

use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Service to format date ranges.
 */
interface DateComparisonInterface {

  /**
   * Compare dates to get their comparison type.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $startDate
   *   Start date.
   * @param \Drupal\Core\Datetime\DrupalDateTime $endDate
   *   End date.
   *
   * @return string
   *  self::COMPARISON_ type to indicate the comparison match.
   */
  public function compareDates(DrupalDateTime $startDate, DrupalDateTime $endDate);

  /**
   * Get the types of comparison results available.
   *
   * @return array
   *  self::COMPARISON_
   */
  public function getComparisonTypes();

  /**
   * Get the label for a comparison type.
   *
   * @param string $comparisonType
   *  self::COMPARISON_ type to indicate the comparison match.
   *
   * @return string
   *   Label for the comparison.
   */
  public function getComparisonTypeLabel($comparisonType);
}
