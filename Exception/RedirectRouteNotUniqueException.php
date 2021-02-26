<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Exception;

use Sulu\Component\Rest\Exception\TranslationErrorMessageExceptionInterface;

/**
 * Redirect-route for given source already exists.
 */
class RedirectRouteNotUniqueException extends \Exception implements TranslationErrorMessageExceptionInterface
{
    /**
     * @var string
     */
    private $source;

    /**
     * @var string|null
     */
    private $sourceHost;

    /**
     * @param string $source
     * @param string|null $sourceHost
     */
    public function __construct($source, $sourceHost = null)
    {
        parent::__construct(sprintf('The source "%s" with sourceHost "%s" is already in use.', $source, $sourceHost));

        $this->source = $source;
    }

    /**
     * Returns source which is not unique.
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Returns source-host which is not unique.
     *
     * @return string|null
     */
    public function getSourceHost()
    {
        return $this->sourceHost;
    }

    public function getMessageTranslationKey(): string
    {
        return 'sulu_redirect.redirect_with_source_already_exist';
    }

    public function getMessageTranslationParameters(): array
    {
        return [
            '%source%' => $this->source,
            '%sourceHost%' => $this->sourceHost ?: '',
        ];
    }
}
