<?php
namespace App\Contracts;

interface RemittanceParserInterface
{
    public function parse(array $data): array;
}