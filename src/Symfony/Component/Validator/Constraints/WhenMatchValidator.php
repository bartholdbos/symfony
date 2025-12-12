<?php

namespace Symfony\Component\Validator\Constraints;

use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\LogicException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class WhenMatchValidator extends ConstraintValidator
{
    public function __construct(private ?ExpressionLanguage $expressionLanguage = null)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof WhenMatch) {
            throw new UnexpectedTypeException($constraint, WhenMatch::class);
        }

        $context = $this->context;
        $variables = $constraint->values;
        $variables['value'] = $value;
        $variables['this'] = $context->getObject();
        $variables['context'] = $context;

        $result = $this->evaluate($constraint->expression, $context, $variables);

        foreach ($constraint->constraints as $arm => $armConstraint) {
            if ($arm instanceof Expression || $arm instanceof \Closure) {
                $arm = $this->evaluate($arm, $context, $variables);
            }

            if ($result !== $arm) {
                continue;
            }

            $context->getValidator()->inContext($context)
                ->validate($value, $armConstraint);
        }

        $context->getValidator()->inContext($context)
            ->validate($value, $constraint->default);
    }

    private function evaluate(Expression|\Closure $expression, ExecutionContextInterface $context, array $variables): mixed
    {
        if ($expression instanceof \Closure) {
            return ($expression)($context->getObject());
        } else {
            return $this->getExpressionLanguage()->evaluate($expression, $variables);
        }
    }

    private function getExpressionLanguage(): ExpressionLanguage
    {
        if (!class_exists(ExpressionLanguage::class)) {
            throw new LogicException(\sprintf('The "symfony/expression-language" component is required to use the "%s" validator. Try running "composer require symfony/expression-language".', __CLASS__));
        }

        return $this->expressionLanguage ??= new ExpressionLanguage();
    }
}
