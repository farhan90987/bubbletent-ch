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

namespace MarketPress\German_Market\Symfony\Component\Process\Messenger;

use MarketPress\German_Market\Symfony\Component\Process\Process;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class RunProcessContext
{
    public readonly ?int $exitCode;
    public readonly ?string $output;
    public readonly ?string $errorOutput;

    public function __construct(
        public readonly RunProcessMessage $message,
        Process $process,
    ) {
        $this->exitCode = $process->getExitCode();
        $this->output = !$process->isStarted() || $process->isOutputDisabled() ? null : $process->getOutput();
        $this->errorOutput = !$process->isStarted() || $process->isOutputDisabled() ? null : $process->getErrorOutput();
    }
}
