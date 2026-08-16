<?php

declare(strict_types=1);

namespace App\MCF\Base;

use Illuminate\Database\Eloquent\Model;
use LogicException;
use ReflectionClass;

abstract class MfcService
{
    /*
    |--------------------------------------------------------------------------
    | Data → Model
    |--------------------------------------------------------------------------
    */

    /**
     * Convert a Data object into an Eloquent model.
     *
     * The Data object's public properties must match
     * existing model attributes.
     *
     * Any mismatch is a programming error and throws
     * a LogicException instead of being silently ignored.
     */
    protected function dataToModel(
        object $data,
        Model $model,
    ): Model {
        $dataReflection = new ReflectionClass($data);

        $dataProperties = [];

        foreach ($dataReflection->getProperties() as $property) {
            if (! $property->isPublic()) {
                continue;
            }

            $dataProperties[$property->getName()] = $property->getValue(
                $data,
            );
        }

        $modelColumns = array_flip(
            $model->getConnection()
                ->getSchemaBuilder()
                ->getColumnListing(
                    $model->getTable(),
                ),
        );

        $unknownFields = array_diff_key(
            $dataProperties,
            $modelColumns,
        );

        if ($unknownFields !== []) {
            throw new LogicException(
                sprintf(
                    'Unable to convert %s to %s. Unknown model fields: %s.',
                    $data::class,
                    $model::class,
                    implode(
                        ', ',
                        array_keys($unknownFields),
                    ),
                ),
            );
        }

        $model->fill(
            $dataProperties,
        );

        return $model;
    }
}
