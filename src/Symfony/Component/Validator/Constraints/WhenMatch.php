<?php

namespace Symfony\Component\Validator\Constraints;

use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\LogicException;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class WhenMatch extends Composite
{
    public array $values = [];

    public function __construct(public string|Expression|\Closure $expression, public array $constraints = [], ?array $values = null, ?array $groups = null, $payload = null, public array|Constraint $default = [])
    {
        if (!class_exists(ExpressionLanguage::class)) {
            throw new LogicException(\sprintf('The "symfony/expression-language" component is required to use the "%s" constraint. Try running "composer require symfony/expression-language".', __CLASS__));
        }

        parent::__construct(null, $groups, $payload);

        $this->values = $values ?? $this->values;
    }

    public function getTargets(): string|array
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }

    protected function getCompositeOption(): array|string
    {
        return ['constraints', 'default'];
    }
}
