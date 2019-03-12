<?php

namespace Drupal\better_date_range\Plugin\Field\FieldFormatter;

use Drupal\better_date_range\DateComparison;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\Plugin\Field\FieldFormatter\DateTimeDefaultFormatter;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
class CompactDateRangeFormatter extends DateTimeDefaultFormatter {

  // Settings.
  const SETTING_FORMAT_TYPE = 'format_type';
  const SETTING_START_FORMAT = 'start_format';
  const SETTING_END_FORMAT = 'end_format';
  const SETTING_SEPARATOR = 'separator';
  const SETTING_TIMEZONE_OVERRIDE = 'timezone_override';

  /**
   * Date comparison service.
   *
   * @var \Drupal\better_date_range\DateComparison
   */
  protected $dateComparison;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\better_date_range\DateComparison $date_comparison
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
    DateComparison $date_comparison
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
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    unset($form[self::SETTING_FORMAT_TYPE]);

    $options = $this->getFormatOptions();

    $start_field = $this->getStartFormatField($options);
    $separator_field = $this->getSeparatorField();
    $end_field = $this->getEndFormatField($options);

    $types = $this->dateComparison->getComparisonTypes();

    foreach ($types as $comparison) {
      $form[$comparison] = [
        '#type' => 'details',
        '#title' => $this->dateComparison->getComparisonTypeLabel($comparison),
      ];

      $comparison_settings = $this->getSetting($comparison);

      $form[$comparison][self::SETTING_START_FORMAT] = $start_field;
      $form[$comparison][self::SETTING_START_FORMAT]['#default_value'] = $comparison_settings[self::SETTING_START_FORMAT];
      $form[$comparison][self::SETTING_SEPARATOR] = $separator_field;
      $form[$comparison][self::SETTING_SEPARATOR]['#default_value'] = $comparison_settings[self::SETTING_SEPARATOR];
      $form[$comparison][self::SETTING_END_FORMAT] = $end_field;
      $form[$comparison][self::SETTING_END_FORMAT]['#default_value'] = $comparison_settings[self::SETTING_END_FORMAT];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $date = new DrupalDateTime();

    foreach ($this->dateComparison->getComparisonTypes() as $key) {
      $summary[] = $this->t('@type: @format', ['@type' => $key, '@format' => $this->formatDateForSummary($date, $key)]);
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

        $comparison_code = $this->dateComparison->compareDates($start_date, $end_date);
        $comparison_settings = $this->getSetting($comparison_code);

        $start_format = $comparison_settings[self::SETTING_START_FORMAT];
        $start_date_formatted = $start_format ? $this->buildDateWithIsoAttributeForFormat($start_date, $start_format) : NULL;
        $end_format = $comparison_settings[self::SETTING_END_FORMAT];
        $end_date_formatted =$end_format ? $this->buildDateWithIsoAttributeForFormat($end_date, $end_format) : NULL;

        if ($start_date_formatted && $end_date_formatted) {
          $elements[$delta] = [
            'start_date' => $start_date_formatted,
            self::SETTING_SEPARATOR => ['#plain_text' => ' ' . $comparison_settings[self::SETTING_SEPARATOR] . ' '],
            'end_date' => $end_date_formatted,
          ];
        }
        else {
          $elements[$delta] = array_filter([$start_date_formatted, $end_date_formatted]);
        }

      }
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   *
   * @param string $format_type
   */
  protected function formatDate($date, $format_type = 'medium') {
    /** @var \Drupal\Core\Datetime\DrupalDateTime $date */
    $timezone = $this->getSetting(self::SETTING_TIMEZONE_OVERRIDE) ?: $date->getTimezone()->getName();
    return $this->dateFormatter->format($date->getTimestamp(), $format_type, '', $timezone !== '' ? $timezone : NULL);
  }

  /**
   * Creates a render array from a date object with ISO date attribute.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $date
   *   A date object.
   * @param string $format_type
   *   The format type to use for formatting the date.
   *
   * @return array
   *   A render array.
   */
  protected function buildDateWithIsoAttributeForFormat(DrupalDateTime $date, $format_type) {
    // Create the ISO date in Universal Time.
    $iso_date = $date->format("Y-m-d\TH:i:s") . 'Z';

    $this->setTimeZone($date);

    $build = [
      '#theme' => 'time',
      '#text' => $this->formatDate($date, $format_type),
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
   * @return array
   */
  protected function getFormatOptions() {
    $time = new DrupalDateTime();
    $format_types = $this->dateFormatStorage->loadMultiple();
    $options = [];
    foreach ($format_types as $type => $type_info) {
      $format = $this->dateFormatter->format($time->getTimestamp(), $type);
      $options[$type] = $type_info->label() . ' (' . $format . ')';
    }
    return $options;
  }

  /**
   * @param $options
   *
   * @return array
   */
  protected function getStartFormatField($options) {
    return [
      '#type' => 'select',
      '#title' => t('Start date format'),
      '#options' => $options,
      '#empty_option' => $this->t('Hidden'),
    ];
  }

  /**
   * @param $options
   *
   * @return array
   */
  protected function getEndFormatField($options) {
    return [
      '#type' => 'select',
      '#title' => t('End date format'),
      '#options' => $options,
      '#empty_option' => $this->t('Hidden'),
    ];
  }

  /**
   * @return array
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
   * @param string $key
   *
   * @return string
   */
  private function formatDateForSummary(DrupalDateTime $date, $key) {
    $comparison_settings = $this->getSetting($key);

    $start_date = $comparison_settings[self::SETTING_START_FORMAT] ? $this->formatDate($date, $comparison_settings[self::SETTING_START_FORMAT]) : NULL;
    $end_date = $comparison_settings[self::SETTING_END_FORMAT] ? $this->formatDate($date, $comparison_settings[self::SETTING_END_FORMAT]) : NULL;

    return implode(' ' . $comparison_settings[self::SETTING_SEPARATOR] . ' ', array_filter([$start_date, $end_date]));
  }
}
