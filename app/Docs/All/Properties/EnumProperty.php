<?php

namespace App\Docs\All\Properties;

use OpenApi\Attributes as OA;
use OpenApi\Generator;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class EnumProperty extends OA\Property
{
    public function __construct(
        string $property,
        array $schemaEnumOptions,
    ) {
        $values = [];
        $example = [];
        foreach ($schemaEnumOptions as $label => $case) {
            $values[] = $case->value;
            $example[$label] = $case->value;
        }

        parent::__construct(
            property: $property,
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(
                type: 'string',
                enum: $values,
            ),
            example: $example ?: Generator::UNDEFINED,
        );
    }
}
