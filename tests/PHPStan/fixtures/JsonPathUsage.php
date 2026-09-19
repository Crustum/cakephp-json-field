<?php
declare(strict_types=1);

use JfApp\Model\Entity\JsonFieldArticle;

$article = new JsonFieldArticle();

// Declared JSON columns resolve to a virtual MixedType property (no error).
$name = $article->{'data->profile.name'};
$theme = $article->{'meta->theme'};

// An undeclared JSON column must remain an "undefined property" error.
$secret = $article->{'secret->value'};
