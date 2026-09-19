<?php
declare(strict_types=1);

namespace JfApp\Model\Dto;

/**
 * Read-only DTO projected from an article row via `projectAs`.
 *
 * Under `projectAs` the row is a raw array (the JsonFieldResultSet entity
 * hydration is bypassed), so JSON embeds arrive here as plain arrays rather than
 * `JsonEntity` instances. This fixture documents that behaviour.
 */
class ArticleDto
{
    public function __construct(
        public string $title,
        public array $data,
    ) {
    }
}
