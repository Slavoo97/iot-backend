<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace App\Utils\Responses;

use Nette;


/**
 * JSON response used mainly for AJAX requests.
 */
final class ExtendedJsonResponse implements Nette\Application\Response
{
    use Nette\SmartObject;

    /** @var mixed */
    private $payload;

    /** @var string */
    private $contentType;

    /** @var string */
    private $code;

    public function __construct($payload, $code = Nette\Http\IResponse::S200_OK, ?string $contentType = null)
    {
        $this->payload = $payload;
        $this->contentType = $contentType ?: 'application/json';
        $this->code = $code;
    }


    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }


    /**
     * Returns the MIME content type of a downloaded file.
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }


    /**
     * Sends response to output.
     */
    public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse): void
    {
        $httpResponse->setContentType($this->contentType, 'utf-8');
        $httpResponse->setHeader('Access-Control-Allow-Origin', '*');
        $httpResponse->setHeader('Access-Control-Allow-Methods', '*');
        $httpResponse->setHeader('Access-Control-Allow-Headers', '*');
        $httpResponse->setCode($this->code);
        echo Nette\Utils\Json::encode($this->payload);
    }
}
