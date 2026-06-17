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
        foreach ($schemaEnumOptions as $case) {
            $values[] = $case->value;
        }

        parent::__construct(
            property: $property,
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(
                        property: 'value',
                        type: 'string',
                        enum: $values,
                        example: $values[0] ?? Generator::UNDEFINED,
                    ),
                    new OA\Property(
                        property: 'label',
                        type: 'string',
                        example: array_key_first($schemaEnumOptions) ?? Generator::UNDEFINED,
                    ),
                ],
            ),
        );
    }
}
