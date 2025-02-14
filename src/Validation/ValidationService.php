<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Validation;

use Linio\Common\Laminas\Exception\Base\NotFoundException;
use Linio\Common\Laminas\Exception\Http\InvalidRequestException;
use Particle\Validator\Validator;

class ValidationService
{
    private ValidatorFactory $validatorFactory;
    private ValidationRulesFactory $validationRulesFactory;

    public function __construct(ValidatorFactory $validatorFactory, ValidationRulesFactory $validationRulesFactory)
    {
        $this->validatorFactory = $validatorFactory;
        $this->validationRulesFactory = $validationRulesFactory;
    }

    /**
     * @throws NotFoundException
     * @throws InvalidRequestException
     */
    public function validate(array $input, array $validationRulesClasses): void
    {
        if (empty($validationRulesClasses)) {
            return;
        }

        $validator = $this->validatorFactory->make();

        foreach ($validationRulesClasses as $validationRulesClass) {
            $validationRules = $this->validationRulesFactory->make($validationRulesClass);
            $validationRules->buildRules($validator, $input);
        }

        $result = $validator->validate($input);

        if (!$result->isValid()) {
            $this->throwExceptionWithValidatorErrors($result->getMessages());
        }
    }

    /**
     * Maps the validator's errors to DomainException's error format.
     *
     * @throws InvalidRequestException
     */
    private function throwExceptionWithValidatorErrors(array $errors): void
    {
        $compiledErrors = [];

        foreach ($errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $compiledErrors[] = [
                    'field' => $field,
                    'message' => $error,
                ];
            }
        }

        throw new InvalidRequestException($compiledErrors);
    }
}
