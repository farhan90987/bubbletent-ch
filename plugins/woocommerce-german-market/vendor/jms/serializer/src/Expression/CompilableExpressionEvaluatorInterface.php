<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\Expression;

/**
 * @author Asmir Mustafic <goetas@gmail.com>
 */
interface CompilableExpressionEvaluatorInterface
{
    public function parse(string $expression, array $names = []): Expression;

    /**
     * @return mixed
     */
    public function evaluateParsed(Expression $expression, array $data = []);
}
