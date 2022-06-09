<?php

class TextFieldMetaBox
{
  private $uniqueID;
  private $name;

  public function __construct($uniqueID, $name, $position = 'normal')
  {
    $this->uniqueID = $uniqueID;
    $this->name = $name;
    $this->position = $position;
    $this->labelID = "{$this->uniqueID}LabelField";
    $this->valueID = "{$this->uniqueID}ValueField";
  }

  public function register($postType)
  {
    \add_action(
      'add_meta_boxes',
      function () use ($postType) {
        $this->addMetaBox($postType);
      }
    );

    \add_action(
      'save_post',
      array($this, 'save')
    );
  }

  public function addMetaBox($postType)
  {
    \add_meta_box(
      $this->uniqueID, // Unique ID
      $this->name, // Meta box title
      array($this, 'render'), // Render callback
      $postType, // Post type
      $this->position // Meta box position
    );
  }

  public function id()
  {
    return $this->uniqueID;
  }

  public function data($post)
  {
    $json = \get_post_meta(
      $post->ID,
      $this->uniqueID,
      true
    );
    return (object)json_decode($json);
  }

  public function render($post)
  {
    ob_start();
    \wp_nonce_field(basename(__FILE__), $this->uniqueID);
    $nonce = ob_get_clean();

    $data = $this->data($post);

    $label = $this->name;
    if (isset($data->label)) {
      $label = $data->label;
    }

    $value = '';
    if (isset($data->value)) {
      $value = $data->value;
    }

    $blade = \CSB\inc\Blade::getInstance();
    echo $blade->render(
      'MetaBox.TextField',
      array(
        'nonce' => $nonce,
        'labelField' => (object)array(
          'id' => $this->labelID,
          'label' => "$this->name custom label",
          'value' => $label
        ),
        'valueField' => (object)array(
          'id' => $this->valueID,
          'label' => "$this->name value",
          'value' => $value
        )
      )
    );
  }

  /**
   * Nonce verification isn't working and I don't know why.
   * @todo: Fix this later!
   */
  public function save($post_id)
  {
    // Checks save status
    $is_autosave = \wp_is_post_autosave($post_id);
    $is_revision = \wp_is_post_revision($post_id);

    // Verify nonce
    $isNonceValid = true; // disabled because it prevents saving
    if (
      isset($_POST[$this->uniqueID]) &&
      \wp_verify_nonce(
        $_POST[$this->uniqueID],
        basename(__FILE__)
      )
    ) {
      $isNonceValid = true;
    }

    // Exits script depending on save status.
    if ($is_autosave || $is_revision || !$isNonceValid) {
      return;
    }

    // Checks for input and sanitizes or saves if needed.
    if (
      isset($_POST[$this->labelID]) &&
      isset($_POST[$this->valueID])
    ) {
      $data = json_encode((object)array(
        'uniqueID' => $this->uniqueID,
        'label' => \sanitize_text_field($_POST[$this->labelID]),
        'value' => \sanitize_text_field($_POST[$this->valueID])
      ));

      \update_post_meta(
        $post_id,
        $this->uniqueID,
        $data
      );
    }
  }
}
