<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace MarketPress\German_Market\Symfony\Component\Validator\Constraints;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use MarketPress\German_Market\Symfony\Component\Validator\Constraint;
use MarketPress\German_Market\Symfony\Component\Validator\ConstraintValidator;
use MarketPress\German_Market\Symfony\Component\Validator\Exception\UnexpectedTypeException;
use MarketPress\German_Market\Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @author Andrey Sevastianov <mrpkmail@gmail.com>
 */
class ExpressionSyntaxValidator extends ConstraintValidator
{
    private ?ExpressionLanguage $expressionLanguage;

    public function __construct(?ExpressionLanguage $expressionLanguage = null)
    {
        $this->expressionLanguage = $expressionLanguage;
    }

    public function validate(mixed $expression, Constraint $constraint): void
    {
        if (!$constraint instanceof ExpressionSyntax) {
            throw new UnexpectedTypeException($constraint, ExpressionSyntax::class);
        }

        if (null === $expression || '' === $expression) {
            return;
        }

        if (!\is_string($expression) && !$expression instanceof \Stringable) {
            throw new UnexpectedValueException($expression, 'string');
        }

        $this->expressionLanguage ??= new ExpressionLanguage();

        try {
            $this->expressionLanguage->lint($expression, $constraint->allowedVariables);
        } catch (SyntaxError $exception) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ syntax_error }}', $this->formatValue($exception->getMessage()))
                ->setInvalidValue((string) $expression)
                ->setCode(ExpressionSyntax::EXPRESSION_SYNTAX_ERROR)
                ->addViolation();
        }
    }
}
