<?php
declare(strict_types = 1);

namespace DASPRiD\Formidable\Mapping\Formatter;

use Assert\Assertion;
use DASPRiD\Formidable\Data;
use DASPRiD\Formidable\FormError\FormError;
use DASPRiD\Formidable\FormError\FormErrorSequence;
use DASPRiD\Formidable\Mapping\BindResult;

final class TextFormatter implements FormatterInterface
{
    /**
     * {@inheritdoc}
     */
    public function bind(string $key, Data $data) : BindResult
    {
        if (!$data->hasKey($key)) {
            return BindResult::fromFormErrors(new FormErrorSequence(new FormError(
                $key,
                'error.required'
            )));
        }

        return BindResult::fromValue((string) $data->getValue($key));
    }

    /**
     * {@inheritdoc}
     */
    public function unbind(string $key, $value) : Data
    {
        Assertion::string($value);
        return Data::fromFlatArray([$key => $value]);
    }
}
