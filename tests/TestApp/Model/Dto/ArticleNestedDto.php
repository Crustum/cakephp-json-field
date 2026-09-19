<?php
declare(strict_types=1);

namespace JfApp\Model\Dto;

/**
 * Article DTO projected via `projectAs`. The `data` embed column is mapped into
 * a nested {@see ArticlePayloadDto} (CakePHP 5.4 core DTO attribute support);
 * the plugin itself does not auto-hydrate embeds for DTO projections.
 */
class ArticleNestedDto
{
    public function __construct(
        public string $title,
        public ArticlePayloadDto $data,
    ) {
    }
}
