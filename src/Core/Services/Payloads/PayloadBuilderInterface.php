<?php

namespace Tok\MPSubscriptions\Core\Services\Payloads;

interface PayloadBuilderInterface {
    public function build(array $fields): array;
}
