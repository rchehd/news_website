<?php

namespace Drupal\news_search\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Displays the news_search settings form.
 */
class NewsSearchSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['news_search.settings'];
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::getFormID().
   */
  public function getFormId() {
    return 'news_search_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['text'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('News Search Text Options'),
    ];

    $placeholder_text = $this->config('news_search.settings')->get('placeholder_text');
    $form['text']['placeholder_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Placeholder Text'),
      '#description' => $this->t('Enter the text to be displayed in the search field (placeholder text)'),
      '#default_value' => $this->t($placeholder_text),
      '#size' => 30,
      '#maxlength' => 60,
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('news_search.settings')->set('placeholder_text', $form_state->getValue('placeholder_text'));
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
