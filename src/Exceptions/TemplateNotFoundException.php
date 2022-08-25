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

final class TemplateNotFoundException extends \RuntimeException
{
    /**
     * @readonly
     */
    private string $template;

    public function __construct(string $template)
    {
        parent::__construct(sprintf(
            'Could not find template "%s".',
            $template
        ));
        $this->template = $template;
    }
}
