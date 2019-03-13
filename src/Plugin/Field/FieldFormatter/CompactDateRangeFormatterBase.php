<?php

namespace Drupal\better_date_range\Plugin\Field\FieldFormatter;

use Drupal\better_date_range\DateComparisonInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\Plugin\Field\FieldFormatter\DateTimeDefaultFormatter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin base of compact range or single formatter for 'daterange' fields.
 *
 * This formatter renders the data range using <time> elements, with
 * a date format for single and multiple dates.
 */
abstract class CompactDateRangeFormatterBase extends DateTimeDefaultFormatter {

  // Settings.
  const SETTING_FORMAT_TYPE = 'format_type';
  const SETTING_START_FORMAT = 'start_format';
  const SETTING_END_FORMAT = 'end_format';
  const SETTING_SEPARATOR = 'separator';
  const SETTING_TIMEZONE_OVERRIDE = 'timezone_override';

  /**
   * Date comparison service.
   *
   * @var \Drupal\better_date_range\DateComparisonInterface
   */
  protected $dateComparison;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\better_date_range\DateComparisonInterface $date_comparison
   *   Date comparison service.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    DateFormatterInterface $date_formatter,
    EntityStorageInterface $date_format_storage,
    DateComparisonInterface $date_comparison
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings, $date_formatter, $date_format_storage);
    $this->dateComparison = $date_comparison;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('date.formatter'),
      $container->get('entity.manager')->getStorage('date_format'),
      $container->get('better_date_range.date_comparison')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    // Remove the global date fallback.
    unset($form[self::SETTING_FORMAT_TYPE]);

    foreach ($this->dateComparison->getComparisonTypes() as $comparison_type) {
      $form[$comparison_type] = [
        '#type' => 'details',
        '#title' => $this->dateComparison->getComparisonTypeLabel($comparison_type),
      ];

      $comparison_type_settings = $this->getSetting($comparison_type);

      $form[$comparison_type][self::SETTING_START_FORMAT] = $this->getStartFormatField();
      $form[$comparison_type][self::SETTING_START_FORMAT]['#default_value'] = $comparison_type_settings[self::SETTING_START_FORMAT];
      $form[$comparison_type][self::SETTING_SEPARATOR] = $this->getSeparatorField();
      $form[$comparison_type][self::SETTING_SEPARATOR]['#default_value'] = $comparison_type_settings[self::SETTING_SEPARATOR];
      $form[$comparison_type][self::SETTING_END_FORMAT] = $this->getEndFormatField();
      $form[$comparison_type][self::SETTING_END_FORMAT]['#default_value'] = $comparison_type_settings[self::SETTING_END_FORMAT];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $date = new DrupalDateTime();

    foreach ($this->dateComparison->getComparisonTypes() as $comparison_type) {
      $summary[] = $this->t('@type: @format', ['@type' => $comparison_type, '@format' => $this->formatDateForSummary($date, $comparison_type)]);
    }

    if ($timezone = $this->getSetting(self::SETTING_TIMEZONE_OVERRIDE)) {
      $summary[] = $this->t('Time zone: @timezone', ['@timezone' => $timezone]);
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

        $comparison_type_settings = $this->getSetting($this->dateComparison->compareDates($start_date, $end_date));

        $start_date_formatted = $this->buildDateWithIsoAttributeForFormat($start_date, $comparison_type_settings[self::SETTING_START_FORMAT]);
        $end_date_formatted = $this->buildDateWithIsoAttributeForFormat($end_date, $comparison_type_settings[self::SETTING_END_FORMAT]);

        if ($start_date_formatted && $end_date_formatted) {
          $elements[$delta] = [
            'start_date' => $start_date_formatted,
            'separator' => ['#plain_text' => ' ' . $comparison_type_settings[self::SETTING_SEPARATOR] . ' '],
            'end_date' => $end_date_formatted,
          ];
        }
        else {
          // If one or more of the dates is hidden, we do not need the separator.
          $elements[$delta] = array_filter([$start_date_formatted, $end_date_formatted]);
        }

      }
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   *
   * @param string $format
   *   The format value to use. Based on the implementing class, this could be
   *   a date format id, or a custom php date format string.
   */
  protected function formatDate($date, $format = NULL) {
    // Safely override parent function with an additional config param.
    if ($format === NULL) {
      return parent::formatDate($date);
    }
    /** @var \Drupal\Core\Datetime\DrupalDateTime $date */
    $timezone = $this->getSetting(self::SETTING_TIMEZONE_OVERRIDE) ?: $date->getTimezone()->getName();
    return $this->dateFormatter->format(
      $date->getTimestamp(),
      $this->getFormatterType($format),
      $this->getFormatterFormat($format),
      $timezone !== '' ? $timezone : NULL
    );
  }

  /**
   * Get the date formatter type to use for a given configuration.
   *
   * @param string $format
   *   Config value for the comparison type.
   *
   * @return string
   *   Date format type id or 'custom'.
   */
  abstract protected function getFormatterType($format);

  /**
   * Get the date formatter format to use for a given configuration.
   *
   * @param string $format
   *   Config value for the comparison type.
   *
   * @return string
   *   Empty string if this will be used with a date format entity, or the php date format string.
   */
  abstract protected function getFormatterFormat($format);

  /**
   * Creates a render array from a date object with ISO date attribute.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $date
   *   A date object.
   * @param string $format
   *   The format type to use for formatting the date.
   *
   * @return array
   *   A render array.
   */
  protected function buildDateWithIsoAttributeForFormat(DrupalDateTime $date, $format) {
    if (empty($format)) {
      return [];
    }

    // Create the ISO date in Universal Time.
    $iso_date = $date->format("Y-m-d\TH:i:s") . 'Z';

    $this->setTimeZone($date);

    $build = [
      '#theme' => 'time',
      '#text' => $this->formatDate($date, $format),
      '#html' => FALSE,
      '#attributes' => [
        'datetime' => $iso_date,
      ],
      '#cache' => [
        'contexts' => [
          'timezone',
        ],
      ],
    ];

    return $build;
  }

  /**
   * Get the start date format field.
   *
   * @return array
   *   Drupal form render array.
   */
  abstract protected function getStartFormatField();

  /**
   * Get the end date format field.
   *
   * @return array
   *   Drupal form render array.
   */
  abstract protected function getEndFormatField();

  /**
   * Get the separator field.
   *
   * @return array
   *   Drupal form render array.
   */
  protected function getSeparatorField() {
    return [
      '#type' => 'textfield',
      '#title' => $this->t('Date separator'),
    ];
  }

  /**
   * Format a date time object with a given comparison setting.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $date
   *   Date time object to format.
   * @param string $comparison_type
   *   The comparison type we are showing.
   *
   * @return string
   *   Formatted string of the date range.
   */
  private function formatDateForSummary(DrupalDateTime $date, $comparison_type) {
    $comparison_settings = $this->getSetting($comparison_type);

    $start_date = $comparison_settings[self::SETTING_START_FORMAT] ? $this->formatDate($date, $comparison_settings[self::SETTING_START_FORMAT]) : NULL;
    $end_date = $comparison_settings[self::SETTING_END_FORMAT] ? $this->formatDate($date, $comparison_settings[self::SETTING_END_FORMAT]) : NULL;

    return implode(' ' . $comparison_settings[self::SETTING_SEPARATOR] . ' ', array_filter([$start_date, $end_date]));
  }
}
