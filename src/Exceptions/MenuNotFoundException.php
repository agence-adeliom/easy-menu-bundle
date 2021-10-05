<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Adeliom\EasyMenuBundle\Exceptions;

final class MenuNotFoundException extends \RuntimeException
{
    public function __construct(string $code)
    {
        parent::__construct(sprintf(
            'Could not find menu with code "%s".',
            $code
        ));
    }
}
