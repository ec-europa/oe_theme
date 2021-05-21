<?php

declare(strict_types = 1);

namespace Drupal\oe_theme\ValueObject;

use Drupal\file\FileInterface;
use Drupal\file_link\Plugin\Field\FieldType\FileLinkItem;
use Mimey\MimeTypes;

/**
 * Handle information about a file, such as its mime type, size, language, etc.
 */
class FileValueObject extends ValueObjectBase implements FileValueObjectInterface {

  /**
   * The name of the file.
   *
   * @var string
   */
  protected $name;

  /**
   * File URL.
   *
   * @var string
   */
  protected $url;

  /**
   * The file mime type.
   *
   * @var string
   */
  protected $mime;

  /**
   * The size of the file.
   *
   * @var string
   */
  protected $size;

  /**
   * File name extension.
   *
   * @var string
   */
  protected $extension;

  /**
   * File title.
   *
   * @var string
   */
  protected $title;

  /**
   * Language code.
   *
   * @var string
   */
  protected $languageCode = '';

  /**
   * Class constructor.
   *
   * @param string $name
   *   Name of the file, e.g. "document.pdf".
   * @param string $url
   *   File URL, including Drupal schema if internal.
   * @param string $mime
   *   File mime type.
   * @param string $size
   *   File size in bytes.
   */
  private function __construct(string $name, string $url, string $mime, string $size) {
    $this->name = $name;
    $this->url = $url;
    $this->mime = $mime;
    $this->size = $size;
  }

  /**
   * Construct object from a Drupal file entity.
   *
   * @param \Drupal\file\FileInterface $file_entity
   *   Drupal file entity object.
   *
   * @return $this
   */
  public static function fromFileEntity(FileInterface $file_entity): FileValueObjectInterface {
    $file = new static(
      $file_entity->getFilename(),
      file_create_url($file_entity->getFileUri()),
      $file_entity->getMimeType(),
      (string) $file_entity->getSize()
    );

    $file->setLanguageCode($file_entity->language()->getId());
    $file->addCacheableDependency($file_entity);

    return $file;
  }

  /**
   * {@inheritdoc}
   */
  public static function fromArray(array $values = []): ValueObjectInterface {
    $file = new static(
      $values['name'],
      $values['url'],
      $values['mime'],
      $values['size']
    );

    if (isset($values['title'])) {
      $file->setTitle($values['title']);
    }

    if (isset($values['language_code'])) {
      $file->setLanguageCode($values['language_code']);
    }

    return $file;
  }

  /**
   * Constructs an object from a file link item.
   *
   * @param \Drupal\file_link\Plugin\Field\FieldType\FileLinkItem $link
   *   The file link item.
   *
   * @return $this
   */
  public static function fromFileLink(FileLinkItem $link): FileValueObjectInterface {
    $file = new static(
      $link->get('title')->getValue() ?? '',
      $link->get('uri')->getValue(),
      $link->getFormat(),
      (string) $link->getSize()
    );

    $file->setLanguageCode($link->getEntity()->language()->getId());

    return $file;
  }

  /**
   * {@inheritdoc}
   */
  public function getUrl(): string {
    return $this->url;
  }

  /**
   * {@inheritdoc}
   */
  public function getMime(): string {
    return $this->mime;
  }

  /**
   * {@inheritdoc}
   */
  public function getSize(): string {
    return $this->size;
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return $this->title ? $this->title : $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function getLanguageCode(): string {
    return $this->languageCode;
  }

  /**
   * {@inheritdoc}
   */
  public function getExtension(): string {
    $mime_types = new MimeTypes();
    $extension = $mime_types->getExtension($this->getMime());
    return $extension ?? pathinfo($this->name, PATHINFO_EXTENSION);
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle(string $title): FileValueObjectInterface {
    $this->title = $title;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setLanguageCode(string $language_code): FileValueObjectInterface {
    $this->languageCode = $language_code;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getArray(): array {
    return [
      'title' => $this->getTitle(),
      'name' => $this->getName(),
      'url' => $this->getUrl(),
      'size' => $this->getSize(),
      'mime' => $this->getMime(),
      'extension' => $this->getExtension(),
      'language_code' => $this->getLanguageCode(),
    ];
  }

}
